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

        // Most Hours This Year
        $hoursLeaderboard = $publicUsers->map(function($user) {
            $totalMinutes = $user->bookings()
                ->whereHas('classSession', function($query) {
                    $query->whereYear('start_time', now()->year)
                          ->where('start_time', '<', now());
                })
                ->with('classSession')
                ->get()
                ->sum(function($booking) {
                    return $booking->classSession->duration_minutes ?? 0;
                });

            return [
                'user' => $user,
                'hours' => round($totalMinutes / 60, 1),
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
            // Calculate current user's stats
            $myHoursMinutes = $currentUser->bookings()
                ->whereHas('classSession', function($query) {
                    $query->whereYear('start_time', now()->year)
                          ->where('start_time', '<', now());
                })
                ->with('classSession')
                ->get()
                ->sum(function($booking) {
                    return $booking->classSession->duration_minutes ?? 0;
                });
            $myHours = round($myHoursMinutes / 60, 1);

            $myClasses = $currentUser->bookings()
                ->whereHas('classSession', function($query) {
                    $query->whereMonth('start_time', now()->month)
                          ->whereYear('start_time', now()->year)
                          ->where('start_time', '<', now());
                })
                ->count();

            // Find rank among all members (not just public)
            $allUsersHours = User::where('is_admin', false)->get()->map(function($user) {
                $totalMinutes = $user->bookings()
                    ->whereHas('classSession', function($query) {
                        $query->whereYear('start_time', now()->year)
                              ->where('start_time', '<', now());
                    })
                    ->with('classSession')
                    ->get()
                    ->sum(function($booking) {
                        return $booking->classSession->duration_minutes ?? 0;
                    });
                return ['id' => $user->id, 'hours' => $totalMinutes / 60];
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
