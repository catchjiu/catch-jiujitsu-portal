<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ClassSession;
use App\Models\Payment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin User
        $admin = User::create([
            'name' => 'Coach Marco',
            'email' => 'admin@catchjiujitsu.com',
            'password' => Hash::make('password'),
            'rank' => 'Black',
            'stripes' => 2,
            'mat_hours' => 5000,
            'is_admin' => true,
        ]);

        // Create Demo Member
        $member = User::create([
            'name' => "Alex 'The Shark'",
            'email' => 'member@catchjiujitsu.com',
            'password' => Hash::make('password'),
            'rank' => 'Blue',
            'stripes' => 2,
            'mat_hours' => 142,
            'is_admin' => false,
        ]);

        // Create Additional Members
        $members = [
            ['name' => 'Sarah Connor', 'email' => 'sarah@example.com', 'rank' => 'Purple', 'stripes' => 1, 'mat_hours' => 320],
            ['name' => 'John Smith', 'email' => 'john@example.com', 'rank' => 'White', 'stripes' => 3, 'mat_hours' => 45],
            ['name' => 'Mike Johnson', 'email' => 'mike@example.com', 'rank' => 'Blue', 'stripes' => 4, 'mat_hours' => 200],
        ];

        foreach ($members as $memberData) {
            User::create([
                'name' => $memberData['name'],
                'email' => $memberData['email'],
                'password' => Hash::make('password'),
                'rank' => $memberData['rank'],
                'stripes' => $memberData['stripes'],
                'mat_hours' => $memberData['mat_hours'],
                'is_admin' => false,
            ]);
        }

        // Create Weekly Class Schedule
        $today = Carbon::now()->startOfWeek();
        
        $classTemplates = [
            // Monday
            ['title' => 'Morning Fundamentals', 'type' => 'Fundamentals', 'day' => 0, 'hour' => 7, 'duration' => 60, 'instructor' => 'Prof. Marco', 'capacity' => 20],
            ['title' => 'Evening Gi', 'type' => 'Gi', 'day' => 0, 'hour' => 18, 'duration' => 90, 'instructor' => 'Prof. Marco', 'capacity' => 25],
            
            // Tuesday
            ['title' => 'No-Gi Basics', 'type' => 'No-Gi', 'day' => 1, 'hour' => 7, 'duration' => 60, 'instructor' => 'Coach Sarah', 'capacity' => 20],
            ['title' => 'Advanced No-Gi', 'type' => 'No-Gi', 'day' => 1, 'hour' => 18, 'duration' => 90, 'instructor' => 'Coach Sarah', 'capacity' => 25],
            
            // Wednesday
            ['title' => 'Morning Fundamentals', 'type' => 'Fundamentals', 'day' => 2, 'hour' => 7, 'duration' => 60, 'instructor' => 'Prof. Marco', 'capacity' => 20],
            ['title' => 'Competition Gi', 'type' => 'Gi', 'day' => 2, 'hour' => 18, 'duration' => 90, 'instructor' => 'Prof. Marco', 'capacity' => 20],
            ['title' => 'Open Mat', 'type' => 'Open Mat', 'day' => 2, 'hour' => 19, 'duration' => 120, 'instructor' => 'Various', 'capacity' => 40],
            
            // Thursday
            ['title' => 'No-Gi Basics', 'type' => 'No-Gi', 'day' => 3, 'hour' => 7, 'duration' => 60, 'instructor' => 'Coach Sarah', 'capacity' => 20],
            ['title' => 'Advanced No-Gi', 'type' => 'No-Gi', 'day' => 3, 'hour' => 18, 'duration' => 90, 'instructor' => 'Coach Sarah', 'capacity' => 25],
            
            // Friday
            ['title' => 'Morning Gi', 'type' => 'Gi', 'day' => 4, 'hour' => 7, 'duration' => 60, 'instructor' => 'Prof. Marco', 'capacity' => 20],
            ['title' => 'All Levels Gi', 'type' => 'Gi', 'day' => 4, 'hour' => 18, 'duration' => 90, 'instructor' => 'Prof. Marco', 'capacity' => 30],
            
            // Saturday
            ['title' => 'Weekend Warrior', 'type' => 'Gi', 'day' => 5, 'hour' => 10, 'duration' => 120, 'instructor' => 'Prof. Marco', 'capacity' => 35],
            ['title' => 'Open Mat', 'type' => 'Open Mat', 'day' => 5, 'hour' => 12, 'duration' => 180, 'instructor' => 'Various', 'capacity' => 50],
        ];

        // Create classes for this week and next week
        foreach ([0, 7] as $weekOffset) {
            foreach ($classTemplates as $template) {
                $startTime = $today->copy()
                    ->addDays($template['day'] + $weekOffset)
                    ->setHour($template['hour'])
                    ->setMinute(0)
                    ->setSecond(0);

                // Only create future classes
                if ($startTime->isFuture()) {
                    ClassSession::create([
                        'title' => $template['title'],
                        'type' => $template['type'],
                        'start_time' => $startTime,
                        'duration_minutes' => $template['duration'],
                        'instructor_name' => $template['instructor'],
                        'capacity' => $template['capacity'],
                    ]);
                }
            }
        }

        // Create Payment Records for Demo Member
        $currentMonth = Carbon::now()->format('F Y');
        $lastMonth = Carbon::now()->subMonth()->format('F Y');
        $twoMonthsAgo = Carbon::now()->subMonths(2)->format('F Y');

        Payment::create([
            'user_id' => $member->id,
            'amount' => 1500,
            'month' => $twoMonthsAgo,
            'status' => 'Paid',
            'submitted_at' => Carbon::now()->subMonths(2)->addDays(2),
        ]);

        Payment::create([
            'user_id' => $member->id,
            'amount' => 1500,
            'month' => $lastMonth,
            'status' => 'Paid',
            'submitted_at' => Carbon::now()->subMonth()->addDays(1),
        ]);

        Payment::create([
            'user_id' => $member->id,
            'amount' => 1500,
            'month' => $currentMonth,
            'status' => 'Overdue',
        ]);

        // Create some payments for other members
        $allMembers = User::where('is_admin', false)->get();
        foreach ($allMembers as $m) {
            if ($m->id !== $member->id) {
                Payment::create([
                    'user_id' => $m->id,
                    'amount' => 1500,
                    'month' => $currentMonth,
                    'status' => collect(['Overdue', 'Pending Verification', 'Paid'])->random(),
                    'submitted_at' => collect([null, Carbon::now()->subDays(rand(1, 5))])->random(),
                ]);
            }
        }
    }
}
