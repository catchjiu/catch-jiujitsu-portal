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
        
        $query = ClassSession::with('instructor')
            ->withCount('bookings')
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

    /**
     * Check in for today's classes - book user for all of today's classes.
     */
    public function checkInToday(Request $request)
    {
        $user = Auth::user();

        if (!$user->hasActiveMembership()) {
            return response()->json([
                'success' => false,
                'message' => $user->membership_issue ?? __('app.dashboard.membership_expired'),
            ], 422);
        }

        $today = Carbon::today();
        $dayStart = $today->copy()->startOfDay();
        $dayEnd = $today->copy()->endOfDay();

        $classes = ClassSession::withCount('bookings')
            ->whereBetween('start_time', [$dayStart, $dayEnd])
            ->where('is_cancelled', false)
            ->whereIn('age_group', in_array($user->age_group ?? 'Adults', ['Kids']) ? ['Kids', 'All'] : ['Adults', 'All'])
            ->orderBy('start_time')
            ->get();

        if ($classes->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => __('app.dashboard.check_in_no_classes'),
            ], 422);
        }

        $booked = 0;
        $skipped = 0;

        foreach ($classes as $class) {
            $existing = Booking::where('user_id', $user->id)->where('class_id', $class->id)->exists();
            if ($existing) {
                $skipped++;
                continue;
            }
            if ($class->bookings_count >= $class->capacity) {
                continue;
            }
            Booking::create([
                'user_id' => $user->id,
                'class_id' => $class->id,
                'checked_in' => true,
            ]);
            $user->decrementClassesRemaining();
            $booked++;
        }

        if ($booked === 0 && $skipped > 0) {
            return response()->json([
                'success' => true,
                'message' => __('app.dashboard.already_booked'),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => __('app.dashboard.check_in_success'),
            'booked' => $booked,
        ]);
    }

    /**
     * Show class attendance for coaches.
     */
    public function showAttendance($classId)
    {
        $user = Auth::user();
        
        // Only coaches can view attendance
        if (!$user->isCoach()) {
            return redirect()->route('schedule')->with('error', 'Only coaches can view attendance.');
        }

        $class = ClassSession::with(['instructor', 'bookings.user'])
            ->withCount('bookings')
            ->findOrFail($classId);

        // Get all bookings with user details
        $bookings = $class->bookings()
            ->with('user')
            ->orderBy('booked_at')
            ->get();

        return view('class-attendance', [
            'class' => $class,
            'bookings' => $bookings,
        ]);
    }
}
