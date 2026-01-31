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
        
        // Get classes booked this month
        $classesThisMonth = $user->bookings()
            ->whereHas('classSession', function($query) {
                $query->whereMonth('start_time', now()->month)
                      ->whereYear('start_time', now()->year);
            })
            ->count();

        return view('dashboard', [
            'user' => $user,
            'nextClass' => $nextClass,
            'classesThisMonth' => $classesThisMonth,
        ]);
    }
}
