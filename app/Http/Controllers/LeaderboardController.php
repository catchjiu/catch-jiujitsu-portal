<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Booking;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LeaderboardController extends Controller
{
    /**
     * Display the leaderboard.
     */
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'hours');

        // Get users with public profiles only
        $publicUsers = User::where('is_admin', false)
            ->where('public_profile', true)
            ->get();

        // Most Hours This Year = mat_hours (starting) + hours from classes this year
        $hoursLeaderboard = $publicUsers->map(function($user) {
            return [
                'user' => $user,
                'hours' => $user->total_hours_this_year,
            ];
        })->sortByDesc('hours')->values()->take(20);

        // Most Classes This Month
        $classesLeaderboard = $publicUsers->map(function($user) {
            $classCount = $user->bookings()
                ->whereHas('classSession', function($query) {
                    $query->whereMonth('start_time', now()->month)
                          ->whereYear('start_time', now()->year)
                          ->where('start_time', '<', now());
                })
                ->count();

            return [
                'user' => $user,
                'classes' => $classCount,
            ];
        })->sortByDesc('classes')->values()->take(20);

        // Current user's rank (even if not public)
        $currentUser = auth()->user();
        $myHoursRank = null;
        $myClassesRank = null;
        $myHours = 0;
        $myClasses = 0;

        if ($currentUser) {
            // Calculate current user's stats (mat_hours + hours this year)
            $myHours = $currentUser->total_hours_this_year;

            $myClasses = $currentUser->bookings()
                ->whereHas('classSession', function($query) {
                    $query->whereMonth('start_time', now()->month)
                          ->whereYear('start_time', now()->year)
                          ->where('start_time', '<', now());
                })
                ->count();

            // Find rank among all members (not just public)
            $allUsersHours = User::where('is_admin', false)->get()->map(function($user) {
                return ['id' => $user->id, 'hours' => $user->total_hours_this_year];
            })->sortByDesc('hours')->values();

            $myHoursRank = $allUsersHours->search(function($item) use ($currentUser) {
                return $item['id'] === $currentUser->id;
            });
            $myHoursRank = $myHoursRank !== false ? $myHoursRank + 1 : null;

            $allUsersClasses = User::where('is_admin', false)->get()->map(function($user) {
                return [
                    'id' => $user->id,
                    'classes' => $user->bookings()
                        ->whereHas('classSession', function($query) {
                            $query->whereMonth('start_time', now()->month)
                                  ->whereYear('start_time', now()->year)
                                  ->where('start_time', '<', now());
                        })
                        ->count()
                ];
            })->sortByDesc('classes')->values();

            $myClassesRank = $allUsersClasses->search(function($item) use ($currentUser) {
                return $item['id'] === $currentUser->id;
            });
            $myClassesRank = $myClassesRank !== false ? $myClassesRank + 1 : null;
        }

        return view('leaderboard', [
            'tab' => $tab,
            'hoursLeaderboard' => $hoursLeaderboard,
            'classesLeaderboard' => $classesLeaderboard,
            'myHoursRank' => $myHoursRank,
            'myClassesRank' => $myClassesRank,
            'myHours' => $myHours,
            'myClasses' => $myClasses,
            'currentUser' => $currentUser,
        ]);
    }
}
