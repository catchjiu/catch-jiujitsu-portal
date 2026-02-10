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
        $flex = LineMessagingService::flexClassReminder($titleEn, $titleZh, $timeStr);
        $altText = "Reminder: {$titleEn} at {$timeStr}. See you on the mat!";

        foreach ($bookings as $booking) {
            $user = $booking->user;
            if (! $user || ! $user->reminders_enabled || ! $user->line_id) {
                continue;
            }
            if ($lineMessaging->sendPushFlex($user->line_id, $flex, $altText)) {
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
    // Use app timezone so "today" is correct for the gym (e.g. Asia/Taipei)
    $today = Carbon::now(config('app.timezone'))->startOfDay();
    $expiryWindowStart = $today->copy()->addDay()->toDateString();   // 1 day from now (Y-m-d)
    $expiryWindowEnd = $today->copy()->addDays(3)->toDateString();   // 3 days from now (Y-m-d)

    // 1) Membership expiring in 1–3 days: expiry date in that window, and we haven't sent for this expiry yet (sends once per expiry)
    $expiryUsers = User::whereNotNull('line_id')
        ->whereNotNull('membership_expires_at')
        ->whereRaw('DATE(membership_expires_at) >= ? AND DATE(membership_expires_at) <= ?', [$expiryWindowStart, $expiryWindowEnd])
        ->where(function ($q) {
            $q->whereNull('membership_expiry_reminder_sent_at')
                ->orWhereRaw('DATE(membership_expiry_reminder_sent_at) != DATE(membership_expires_at)');
        })
        ->get();

    $this->info("Expiry window: {$expiryWindowStart} to {$expiryWindowEnd} (today: {$today->toDateString()}). Found " . $expiryUsers->count() . " user(s) for expiry reminder.");

    $paymentsUrl = LineMessagingService::getLinkUrl('payments');
    foreach ($expiryUsers as $user) {
        $dateStr = $user->membership_expires_at->format('M j, Y');
        $flex = LineMessagingService::flexMembershipExpiring($dateStr, $paymentsUrl);
        $altText = "Reminder: Your membership expires in 3 days ({$dateStr}). Contact us to renew.";
        if ($lineMessaging->sendPushFlex($user->line_id, $flex, $altText)) {
            $user->update(['membership_expiry_reminder_sent_at' => $user->membership_expires_at]);
            $sentExpiry++;
        } else {
            $this->warn("Failed to send expiry reminder to user {$user->id} ({$user->email}): " . (LineMessagingService::getLastPushError() ?? 'unknown'));
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

    $paymentsUrl = LineMessagingService::getLinkUrl('payments');
    foreach ($zeroClassUsers as $user) {
        $flex = LineMessagingService::flexClassPassZero($paymentsUrl);
        $altText = 'Reminder: Your class pass has no classes left. Contact us to top up.';
        if ($lineMessaging->sendPushFlex($user->line_id, $flex, $altText)) {
            $user->update(['classes_zero_reminder_sent_at' => now()]);
            $sentZero++;
        } else {
            $this->warn("Failed to send zero-class reminder to user {$user->id} ({$user->email}): " . (LineMessagingService::getLastPushError() ?? 'unknown'));
        }
    }

    $this->info("Sent {$sentExpiry} expiry reminder(s), {$sentZero} zero-class reminder(s).");
    return 0;
})->purpose('Send LINE reminders for membership expiring in 3 days and class pass at zero');

Schedule::command('reminders:send-membership')->daily();

/**
 * Send LINE day-before reminders: "You have [Class] tomorrow at [time]" with link to schedule.
 * Run daily (e.g. evening); finds classes tomorrow and notifies users who have reminders_enabled and line_id.
 */
Artisan::command('reminders:send-day-before', function () {
    $lineMessaging = app(LineMessagingService::class);
    if (! $lineMessaging->isConfigured()) {
        $this->warn('LINE Messaging API not configured. Skip.');
        return 0;
    }

    $tz = config('app.timezone');
    $tomorrowStart = Carbon::now($tz)->addDay()->startOfDay();
    $tomorrowEnd = Carbon::now($tz)->addDay()->endOfDay();
    $scheduleUrl = LineMessagingService::getLinkUrl('schedule');

    $classes = ClassSession::whereBetween('start_time', [$tomorrowStart, $tomorrowEnd])
        ->where('is_cancelled', false)
        ->get();

    $sent = 0;
    foreach ($classes as $class) {
        $bookings = Booking::where('class_id', $class->id)->with('user')->get();
        $timeStr = $class->start_time->format('H:i');
        $titleEn = $class->title;
        $titleZh = $class->title_zh ?: $class->title;
        $flex = LineMessagingService::flexDayBeforeReminder($titleEn, $titleZh, $timeStr, $scheduleUrl);
        $altText = "Reminder: You have {$titleEn} tomorrow at {$timeStr}. View schedule to cancel.";

        foreach ($bookings as $booking) {
            $user = $booking->user;
            if (! $user || ! $user->reminders_enabled || ! $user->line_id) {
                continue;
            }
            if ($lineMessaging->sendPushFlex($user->line_id, $flex, $altText)) {
                $sent++;
            }
        }
    }

    $this->info("Sent {$sent} day-before reminder(s).");
    return 0;
})->purpose('Send LINE day-before class reminders');

Schedule::command('reminders:send-day-before')->dailyAt('20:00');

/**
 * Send LINE post-class messages: "Thanks for attending [Class]. Book your next session!"
 * Run every hour; finds classes that ended 1–2 hours ago and sends to users who had a booking.
 */
Artisan::command('reminders:send-post-class', function () {
    $lineMessaging = app(LineMessagingService::class);
    if (! $lineMessaging->isConfigured()) {
        $this->warn('LINE Messaging API not configured. Skip.');
        return 0;
    }

    $tz = config('app.timezone');
    $windowEnd = Carbon::now($tz)->subHour();
    $windowStart = Carbon::now($tz)->subHours(2);
    $scheduleUrl = LineMessagingService::getLinkUrl('schedule');

    $classes = ClassSession::where('is_cancelled', false)->get()->filter(function ($class) use ($windowStart, $windowEnd) {
        $endTime = $class->start_time->copy()->addMinutes((int) $class->duration_minutes);
        return $endTime->between($windowStart, $windowEnd);
    });

    $sent = 0;
    foreach ($classes as $class) {
        $bookings = Booking::where('class_id', $class->id)->with('user')->get();
        $titleEn = $class->title;
        $titleZh = $class->title_zh ?: $class->title;
        $flex = LineMessagingService::flexPostClass($titleEn, $titleZh, $scheduleUrl);
        $altText = "Thanks for attending {$titleEn}! Book your next session.";

        foreach ($bookings as $booking) {
            $user = $booking->user;
            if (! $user || ! $user->line_id) {
                continue;
            }
            if ($lineMessaging->sendPushFlex($user->line_id, $flex, $altText)) {
                $sent++;
            }
        }
    }

    $this->info("Sent {$sent} post-class message(s).");
    return 0;
})->purpose('Send LINE post-class thank-you messages');

Schedule::command('reminders:send-post-class')->hourly();

/**
 * Send LINE re-engagement: "We miss you! Here's this week's schedule" to users with no booking in 7 days.
 * Run daily; at most once per 7 days per user (tracked by last_reengagement_line_sent_at).
 */
Artisan::command('reminders:send-reengagement', function () {
    $lineMessaging = app(LineMessagingService::class);
    if (! $lineMessaging->isConfigured()) {
        $this->warn('LINE Messaging API not configured. Skip.');
        return 0;
    }

    $tz = config('app.timezone');
    $sevenDaysAgo = Carbon::now($tz)->subDays(7);
    $reengagementCooldown = Carbon::now($tz)->subDays(7);
    $scheduleUrl = LineMessagingService::getLinkUrl('schedule');

    $candidates = User::whereNotNull('line_id')
        ->where(function ($q) use ($reengagementCooldown) {
            $q->whereNull('last_reengagement_line_sent_at')
                ->orWhere('last_reengagement_line_sent_at', '<', $reengagementCooldown);
        })
        ->get();

    $sent = 0;
    foreach ($candidates as $user) {
        $hasRecentBooking = Booking::where('user_id', $user->id)
            ->whereHas('classSession', function ($q) use ($sevenDaysAgo) {
                $q->where('start_time', '>=', $sevenDaysAgo);
            })
            ->exists();

        if ($hasRecentBooking) {
            continue;
        }

        $flex = LineMessagingService::flexReengagement($scheduleUrl);
        $altText = "We miss you! Here's this week's schedule — see you on the mat soon!";
        if ($lineMessaging->sendPushFlex($user->line_id, $flex, $altText)) {
            $user->update(['last_reengagement_line_sent_at' => now()]);
            $sent++;
        } else {
            $this->warn("Failed to send re-engagement to user {$user->id}: " . (LineMessagingService::getLastPushError() ?? 'unknown'));
        }
    }

    $this->info("Sent {$sent} re-engagement message(s).");
    return 0;
})->purpose('Send LINE re-engagement to users with no booking in 7 days');

Schedule::command('reminders:send-reengagement')->dailyAt('10:00');
