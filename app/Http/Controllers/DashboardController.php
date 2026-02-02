<?php

namespace App\Http\Controllers;

use App\Models\ClassSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $user->load('membershipPackage'); // Load membership package relationship
        $nextClass = $user->nextBookedClass();
        
        // Get the booking for the next class (for cancel functionality)
        $nextBooking = null;
        if ($nextClass) {
            $nextBooking = $user->bookings()
                ->where('class_id', $nextClass->id)
                ->first();
        }
        
        // Get classes booked this month
        $classesThisMonth = $user->bookings()
            ->whereHas('classSession', function($query) {
                $query->whereMonth('start_time', now()->month)
                      ->whereYear('start_time', now()->year);
            })
            ->count();
        
        // Get previous classes (past classes the user attended)
        $previousClasses = $user->bookedClasses()
            ->with('instructor')
            ->where('start_time', '<', now())
            ->orderBy('start_time', 'desc')
            ->take(5)
            ->get();

        return view('dashboard', [
            'user' => $user,
            'nextClass' => $nextClass,
            'nextBooking' => $nextBooking,
            'classesThisMonth' => $classesThisMonth,
            'previousClasses' => $previousClasses,
        ]);
    }
}
