<?php

namespace App\Http\Controllers;

use App\Models\ClassSession;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    /**
     * Display the schedule with all upcoming classes.
     */
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'All');
        
        $query = ClassSession::withCount('bookings')
            ->where('start_time', '>', now())
            ->orderBy('start_time');
        
        // Apply filter
        if ($filter === 'Gi') {
            $query->whereIn('type', ['Gi', 'Fundamentals']);
        } elseif ($filter === 'No-Gi') {
            $query->where('type', 'No-Gi');
        }
        
        $classes = $query->get()->map(function ($class) {
            $class->is_booked_by_user = $class->isBookedByUser(Auth::user());
            return $class;
        });

        return view('schedule', [
            'classes' => $classes,
            'currentFilter' => $filter,
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

        $class = ClassSession::withCount('bookings')->findOrFail($request->class_id);

        // Check if class is full
        if ($class->bookings_count >= $class->capacity) {
            return back()->with('error', 'Sorry, this class is full.');
        }

        // Check if already booked
        $existingBooking = Booking::where('user_id', Auth::id())
            ->where('class_id', $class->id)
            ->first();

        if ($existingBooking) {
            return back()->with('error', 'You have already booked this class.');
        }

        // Create booking
        Booking::create([
            'user_id' => Auth::id(),
            'class_id' => $class->id,
        ]);

        return back()->with('success', 'Class booked successfully! See you on the mats.');
    }

    /**
     * Cancel a booking.
     */
    public function destroy($classId)
    {
        $booking = Booking::where('user_id', Auth::id())
            ->where('class_id', $classId)
            ->first();

        if (!$booking) {
            return back()->with('error', 'Booking not found.');
        }

        $booking->delete();

        return back()->with('success', 'Booking cancelled successfully.');
    }
}
