<?php

use App\Models\ClassSession;
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
