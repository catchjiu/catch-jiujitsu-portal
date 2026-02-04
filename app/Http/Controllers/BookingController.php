<?php

namespace App\Http\Controllers;

use App\Models\ClassSession;
use App\Models\Booking;
use App\Models\ClassTrial;
use App\Models\PrivateClassBooking;
use App\Models\User;
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
        $user = User::currentFamilyMember();
        
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
        
        $classes = $query->get()->map(function ($class) use ($user) {
            $class->is_booked_by_user = $class->isBookedByUser($user);
            return $class;
        });

        // Accepted private classes for the viewing member on the selected day
        $privateClassesForDay = PrivateClassBooking::with('coach')
            ->where('member_id', $user->id)
            ->where('status', 'accepted')
            ->whereBetween('scheduled_at', [$dayStart, $dayEnd])
            ->orderBy('scheduled_at')
            ->get();

        // Merge group classes and private classes, sorted by start time
        $scheduleItems = collect();
        foreach ($classes as $class) {
            $scheduleItems->push(['type' => 'class', 'start_time' => $class->start_time, 'payload' => $class]);
        }
        foreach ($privateClassesForDay as $booking) {
            $scheduleItems->push(['type' => 'private', 'start_time' => $booking->scheduled_at, 'payload' => $booking]);
        }
        $scheduleItems = $scheduleItems->sortBy('start_time')->values();

        return view('schedule', [
            'classes' => $classes,
            'scheduleItems' => $scheduleItems,
            'currentFilter' => $filter,
            'selectedDate' => $selectedDate,
            'weekDays' => $weekDays,
            'weekStart' => $weekStart,
            'prevWeek' => $prevWeek,
            'nextWeek' => $nextWeek,
            'viewingUser' => $user,
            'familyBar' => Auth::user()->isInFamily() ?? false,
            'familyMembers' => Auth::user()->isInFamily() ? Auth::user()->familyMembersWithSelf() : collect(),
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

        $user = User::currentFamilyMember();

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
        $user = User::currentFamilyMember();

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
        $user = User::currentFamilyMember();

        if (!$user->hasActiveMembership()) {
            return response()->json([
                'success' => false,
                'message' => $user->membership_issue ?? __('app.dashboard.membership_expired'),
            ], 422);
        }

        // Use client's date so "today" matches the member's timezone; fallback to server today
        $dateInput = $request->input('date');
        if ($dateInput) {
            $today = Carbon::parse($dateInput, config('app.timezone'));
            // Only allow today or past dates (prevent future check-ins)
            if ($today->isFuture()) {
                return response()->json([
                    'success' => false,
                    'message' => __('app.dashboard.check_in_no_classes'),
                ], 422);
            }
        } else {
            $today = Carbon::today();
        }
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
        $checkedIn = 0;

        foreach ($classes as $class) {
            $existing = Booking::where('user_id', $user->id)->where('class_id', $class->id)->first();
            if ($existing) {
                if (!$existing->checked_in) {
                    $existing->update(['checked_in' => true]);
                    $checkedIn++;
                }
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

        if ($booked === 0 && $checkedIn > 0) {
            return response()->json([
                'success' => true,
                'message' => __('app.dashboard.check_in_success'),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => __('app.dashboard.check_in_success'),
            'booked' => $booked,
        ]);
    }

    /**
     * Show class attendance for coaches (same module as admin attendance).
     */
    public function showAttendance($classId)
    {
        $user = Auth::user();
        if (!$user->isCoach()) {
            return redirect()->route('schedule')->with('error', 'Only coaches can view attendance.');
        }

        $class = ClassSession::with(['bookings.user'])->withCount('bookings')->findOrFail($classId);
        $bookedUsers = $class->bookings->map(fn ($booking) => [
            'booking' => $booking,
            'user' => $booking->user,
            'checked_in' => $booking->checked_in ?? false,
        ]);

        $bookedUserIds = $class->bookings->pluck('user_id');
        $classAgeGroup = $class->age_group ?? 'Adults';
        $availableMembers = User::where('is_admin', false)
            ->whereNotIn('id', $bookedUserIds)
            ->when($classAgeGroup === 'Kids', fn ($q) => $q->whereIn('age_group', ['Kids', 'All']))
            ->when($classAgeGroup === 'Adults', fn ($q) => $q->whereIn('age_group', ['Adults', 'All']))
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $class->load('trials');
        $waitlistCount = max(0, $class->bookings_count - $class->capacity);
        $checkedInCount = $class->bookings->where('checked_in', true)->count();

        return view('coach.attendance', [
            'class' => $class,
            'bookedUsers' => $bookedUsers,
            'trials' => $class->trials,
            'availableMembers' => $availableMembers,
            'checkedInCount' => $checkedInCount,
            'waitlistCount' => $waitlistCount,
        ]);
    }

    /**
     * Toggle check-in (coach).
     */
    public function toggleCheckInCoach(Request $request, $classId, $bookingId)
    {
        if (!Auth::user()->isCoach()) {
            return redirect()->route('schedule')->with('error', 'Only coaches can update attendance.');
        }
        $booking = Booking::where('class_id', $classId)->findOrFail($bookingId);
        $booking->checked_in = !$booking->checked_in;
        $booking->save();
        return back()->with('success', 'Check-in status updated.');
    }

    /**
     * Remove booking from class (coach).
     */
    public function removeBookingCoach($classId, $bookingId)
    {
        if (!Auth::user()->isCoach()) {
            return redirect()->route('schedule')->with('error', 'Only coaches can update attendance.');
        }
        $booking = Booking::where('class_id', $classId)->findOrFail($bookingId);
        $user = $booking->user;
        $booking->delete();
        if ($user && $user->classes_remaining !== null) {
            $user->incrementClassesRemaining();
        }
        return back()->with('success', 'Removed from class.');
    }

    /**
     * Remove trial from class (coach).
     */
    public function removeTrialCoach($classId, $trialId)
    {
        if (!Auth::user()->isCoach()) {
            return redirect()->route('schedule')->with('error', 'Only coaches can update attendance.');
        }
        $trial = ClassTrial::where('class_id', $classId)->findOrFail($trialId);
        $trial->delete();
        return back()->with('success', 'Trial removed.');
    }

    /**
     * Add walk-in (coach).
     */
    public function addWalkInCoach(Request $request, $classId)
    {
        if (!Auth::user()->isCoach()) {
            return redirect()->route('schedule')->with('error', 'Only coaches can add walk-ins.');
        }
        $class = ClassSession::withCount('bookings')->with('trials')->findOrFail($classId);
        $validated = $request->validate(['user_id' => 'required|exists:users,id']);
        $userId = (int) $validated['user_id'];
        if (Booking::where('class_id', $class->id)->where('user_id', $userId)->exists()) {
            return back()->with('error', 'Member is already booked for this class.');
        }
        $totalAttendance = $class->bookings_count + $class->trials->count();
        if ($totalAttendance >= $class->capacity) {
            return back()->with('error', 'Class is full.');
        }
        $user = User::findOrFail($userId);
        if ($user->is_admin) {
            return back()->with('error', 'Cannot add admin as walk-in.');
        }
        Booking::create([
            'user_id' => $userId,
            'class_id' => $class->id,
            'checked_in' => true,
        ]);
        if ($user->classes_remaining !== null && $user->classes_remaining > 0) {
            $user->decrementClassesRemaining();
        }
        return back()->with('success', $user->name . ' added as walk-in.');
    }
}
