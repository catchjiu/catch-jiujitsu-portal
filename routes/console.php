<?php

use App\Models\Booking;
use App\Models\ClassSession;
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
        $title = app()->getLocale() === 'zh-TW' && $class->title_zh ? $class->title_zh : $class->title;
        $message = (app()->getLocale() === 'zh-TW')
            ? "課程提醒：{$title} 將在 {$timeStr} 開始。See you on the mat!"
            : "Reminder: {$title} at {$timeStr}. See you on the mat!";

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
