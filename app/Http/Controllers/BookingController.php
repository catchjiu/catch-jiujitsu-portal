<?php

namespace App\Http\Controllers;

use App\Models\ClassSession;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * Display the schedule with all upcoming classes.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Default filter based on user's age_group
        $defaultFilter = $user->age_group ?? 'Adults';
        $filter = $request->get('filter', $defaultFilter);
        
        // Get selected date (default to today)
        $selectedDate = $request->get('date') ? Carbon::parse($request->get('date')) : Carbon::today();
        
        // Get week days (Mon-Sun)
        $weekStart = $selectedDate->copy()->startOfWeek(Carbon::MONDAY);
        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $weekDays[] = $weekStart->copy()->addDays($i);
        }
        
        // Calculate previous and next week dates
        $prevWeek = $weekStart->copy()->subWeek();
        $nextWeek = $weekStart->copy()->addWeek();
        
        // Get classes for selected date
        $dayStart = $selectedDate->copy()->startOfDay();
        $dayEnd = $selectedDate->copy()->endOfDay();
        
        $query = ClassSession::withCount('bookings')
            ->whereBetween('start_time', [$dayStart, $dayEnd])
            ->orderBy('start_time');
        
        // Apply age group filter
        if ($filter === 'Kids') {
            $query->whereIn('age_group', ['Kids', 'All']);
        } elseif ($filter === 'Adults') {
            $query->whereIn('age_group', ['Adults', 'All']);
        }
        // 'All' filter shows all classes
        
        $classes = $query->get()->map(function ($class) {
            $class->is_booked_by_user = $class->isBookedByUser(Auth::user());
            return $class;
        });

        return view('schedule', [
            'classes' => $classes,
            'currentFilter' => $filter,
            'selectedDate' => $selectedDate,
            'weekDays' => $weekDays,
            'weekStart' => $weekStart,
            'prevWeek' => $prevWeek,
            'nextWeek' => $nextWeek,
        ]);
    }

    /**
     * Book a class for the authenticated user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id'
        ]);

        $user = Auth::user();

        // Check if user has active membership
        if (!$user->hasActiveMembership()) {
            return back()->with('error', $user->membership_issue ?? 'You need an active membership to book classes.');
        }

        $class = ClassSession::withCount('bookings')->findOrFail($request->class_id);

        // Check if class is full
        if ($class->bookings_count >= $class->capacity) {
            return back()->with('error', 'Sorry, this class is full.');
        }

        // Check if already booked
        $existingBooking = Booking::where('user_id', $user->id)
            ->where('class_id', $class->id)
            ->first();

        if ($existingBooking) {
            return back()->with('error', 'You have already booked this class.');
        }

        // Create booking
        Booking::create([
            'user_id' => $user->id,
            'class_id' => $class->id,
        ]);

        // Decrement classes remaining for class-based packages
        $user->decrementClassesRemaining();

        return back()->with('success', 'Class booked successfully! See you on the mats.');
    }

    /**
     * Cancel a booking.
     */
    public function destroy($classId)
    {
        $user = Auth::user();
        
        $booking = Booking::where('user_id', $user->id)
            ->where('class_id', $classId)
            ->first();

        if (!$booking) {
            return back()->with('error', 'Booking not found.');
        }

        $booking->delete();

        // Restore class credit for class-based packages
        $user->incrementClassesRemaining();

        return back()->with('success', 'Booking cancelled successfully.');
    }
}
