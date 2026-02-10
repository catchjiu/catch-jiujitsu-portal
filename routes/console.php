<?php

use App\Models\Booking;
use App\Models\ClassSession;
use App\Models\User;
use App\Services\LineMessagingService;
use Carbon\Carbon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Ensure classes exist for the next 4 weeks by copying from a reference week.
 * Run daily so the schedule is always visible 4 weeks ahead.
 */
Artisan::command('classes:ensure-four-weeks', function () {
    $today = Carbon::today();
    $weekStart = $today->copy()->startOfWeek(Carbon::MONDAY);

    // Find a reference week that has at least one class (this week, or go back)
    $referenceStart = $weekStart->copy();
    $referenceClasses = collect();
    for ($back = 0; $back < 8; $back++) {
        $start = $weekStart->copy()->subWeeks($back);
        $end = $start->copy()->endOfWeek(Carbon::SUNDAY);
        $referenceClasses = ClassSession::whereBetween('start_time', [$start, $end])->get();
        if ($referenceClasses->isNotEmpty()) {
            $referenceStart = $start;
            break;
        }
    }

    if ($referenceClasses->isEmpty()) {
        $this->warn('No classes found in the last 8 weeks. Create at least one class to use as a template.');
        return 0;
    }

    $created = 0;
    for ($weekOffset = 1; $weekOffset <= 4; $weekOffset++) {
        foreach ($referenceClasses as $ref) {
            $newStart = $ref->start_time->copy()->addWeeks($weekOffset);
            if ($newStart->isPast()) {
                continue;
            }
            // Avoid duplicate: same datetime (within same minute)
            $minStart = $newStart->copy()->startOfMinute();
            $minEnd = $newStart->copy()->endOfMinute();
            $exists = ClassSession::whereBetween('start_time', [$minStart, $minEnd])->exists();
            if ($exists) {
                continue;
            }
            ClassSession::create([
                'title' => $ref->title,
                'title_zh' => $ref->title_zh,
                'type' => $ref->type,
                'age_group' => $ref->age_group ?? 'Adults',
                'start_time' => $newStart,
                'duration_minutes' => $ref->duration_minutes,
                'instructor_id' => $ref->instructor_id,
                'instructor_name' => $ref->instructor_name,
                'capacity' => $ref->capacity,
                'is_cancelled' => false,
            ]);
            $created++;
        }
    }

    $this->info("Ensured 4 weeks ahead. Created {$created} class(es).");
    return 0;
})->purpose('Ensure classes are visible 4 weeks ahead (copy from reference week)');

Schedule::command('classes:ensure-four-weeks')->daily();

/**
 * Send LINE Messaging API reminders for classes starting in ~1 hour.
 * Run every 15 minutes; sends to users who have reminders_enabled and line_id (linked LINE).
 */
Artisan::command('reminders:send-class', function () {
    $remindMinutesBefore = 60;
    $windowStart = Carbon::now()->addMinutes($remindMinutesBefore - 5);
    $windowEnd = Carbon::now()->addMinutes($remindMinutesBefore + 5);

    $lineMessaging = app(LineMessagingService::class);
    if (! $lineMessaging->isConfigured()) {
        $this->warn('LINE Messaging API not configured. Skip.');
        return 0;
    }

    $classes = ClassSession::whereBetween('start_time', [$windowStart, $windowEnd])
        ->where('is_cancelled', false)
        ->get();

    $sent = 0;
    foreach ($classes as $class) {
        $bookings = Booking::where('class_id', $class->id)
            ->with('user')
            ->get();

        $timeStr = $class->start_time->format('H:i');
        $titleEn = $class->title;
        $titleZh = $class->title_zh ?: $class->title;
        $message = "Reminder: {$titleEn} at {$timeStr}. See you on the mat!\n\n課程提醒：{$titleZh} 將在 {$timeStr} 開始。See you on the mat!";

        foreach ($bookings as $booking) {
            $user = $booking->user;
            if (! $user || ! $user->reminders_enabled || ! $user->line_id) {
                continue;
            }
            if ($lineMessaging->sendPushMessage($user->line_id, $message)) {
                $sent++;
            }
        }
    }

    $this->info("Sent {$sent} class reminder(s).");
    return 0;
})->purpose('Send LINE reminders for classes starting in 1 hour');

Schedule::command('reminders:send-class')->everyFifteenMinutes();

/**
 * Send LINE messages: membership expiring in 3 days, and class pass at zero.
 * Run daily. Only sends to users with line_id; tracks sent state to avoid duplicates.
 */
Artisan::command('reminders:send-membership', function () {
    $lineMessaging = app(LineMessagingService::class);
    if (! $lineMessaging->isConfigured()) {
        $this->warn('LINE Messaging API not configured. Skip.');
        return 0;
    }

    $sentExpiry = 0;
    $sentZero = 0;
    $today = Carbon::today();
    $threeDaysFromNow = $today->copy()->addDays(3);

    // 1) Membership expiring in 3 days: expiry date is exactly 3 days from today, and we haven't sent for this expiry yet
    $expiryUsers = User::whereNotNull('line_id')
        ->whereNotNull('membership_expires_at')
        ->whereDate('membership_expires_at', $threeDaysFromNow)
        ->where(function ($q) {
            $q->whereNull('membership_expiry_reminder_sent_at')
                ->orWhereRaw('DATE(membership_expiry_reminder_sent_at) != DATE(membership_expires_at)');
        })
        ->get();

    foreach ($expiryUsers as $user) {
        $dateStr = $user->membership_expires_at->format('M j, Y');
        $message = "Reminder: Your membership expires in 3 days ({$dateStr}). Contact us to renew.\n\n提醒：您的會籍將在 3 天後（{$dateStr}）到期。如需續期請聯絡我們。";
        if ($lineMessaging->sendPushMessage($user->line_id, $message)) {
            $user->update(['membership_expiry_reminder_sent_at' => $user->membership_expires_at]);
            $sentExpiry++;
        }
    }

    // 2) Class pass at zero: classes_remaining is 0, and we haven't sent the zero reminder yet (class-based package)
    $zeroClassUsers = User::whereNotNull('line_id')
        ->where('classes_remaining', 0)
        ->whereNull('classes_zero_reminder_sent_at')
        ->whereHas('membershipPackage', function ($q) {
            $q->where('duration_type', 'classes');
        })
        ->get();

    foreach ($zeroClassUsers as $user) {
        $message = "Reminder: Your class pass has no classes left. Contact us to top up.\n\n提醒：您的堂數已用完。如需再購買請聯絡我們。";
        if ($lineMessaging->sendPushMessage($user->line_id, $message)) {
            $user->update(['classes_zero_reminder_sent_at' => now()]);
            $sentZero++;
        }
    }

    $this->info("Sent {$sentExpiry} expiry reminder(s), {$sentZero} zero-class reminder(s).");
    return 0;
})->purpose('Send LINE reminders for membership expiring in 3 days and class pass at zero');

Schedule::command('reminders:send-membership')->daily();
