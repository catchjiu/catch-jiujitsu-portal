<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ClassSession;
use App\Models\Booking;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        // Get past classes
        $classes = ClassSession::where('start_time', '<', now())->get();
        
        if ($classes->isEmpty()) {
            // Create some past classes if none exist
            $this->createPastClasses();
            $classes = ClassSession::where('start_time', '<', now())->get();
        }
        
        $users = User::where('is_admin', false)->get();
        
        foreach ($users as $user) {
            // Random number of classes for each user
            $numClasses = rand(3, min(8, $classes->count()));
            $selectedClasses = $classes->random($numClasses);
            
            foreach ($selectedClasses as $class) {
                Booking::firstOrCreate([
                    'user_id' => $user->id,
                    'class_id' => $class->id,
                ]);
            }
            
            // Enable public profile for most users
            if (rand(1, 10) <= 7) {
                $user->update(['public_profile' => true]);
            }
        }
        
        $this->command->info('Bookings created for leaderboard!');
    }
    
    private function createPastClasses(): void
    {
        $templates = [
            ['title' => 'Morning Fundamentals', 'type' => 'Fundamentals', 'instructor' => 'Prof. Marco', 'duration' => 60],
            ['title' => 'No-Gi Basics', 'type' => 'No-Gi', 'instructor' => 'Coach Sarah', 'duration' => 60],
            ['title' => 'Advanced Gi', 'type' => 'Gi', 'instructor' => 'Prof. Marco', 'duration' => 90],
            ['title' => 'Open Mat', 'type' => 'Open Mat', 'instructor' => 'Various', 'duration' => 120],
        ];
        
        // Create classes for the past 4 weeks
        for ($week = 1; $week <= 4; $week++) {
            foreach ($templates as $template) {
                ClassSession::create([
                    'title' => $template['title'],
                    'type' => $template['type'],
                    'start_time' => now()->subWeeks($week)->setHour(rand(7, 19))->setMinute(0),
                    'duration_minutes' => $template['duration'],
                    'instructor_name' => $template['instructor'],
                    'capacity' => 25,
                ]);
            }
        }
    }
}
