<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\User;
use App\Models\ClassSession;
use App\Models\Booking;
use App\Models\MembershipPackage;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Display admin overview dashboard.
     */
    public function index()
    {
        $user = auth()->user();
        
        // Stats
        $totalMembers = User::where('is_admin', false)->count();
        $activeBookings = Booking::whereHas('classSession', function($q) {
            $q->where('start_time', '>', now());
        })->count();
        
        // Today's attendance (bookings for today's classes)
        $todayStart = Carbon::today();
        $todayEnd = Carbon::today()->endOfDay();
        $todayClasses = ClassSession::whereBetween('start_time', [$todayStart, $todayEnd])->get();
        $todayCheckIns = Booking::whereIn('class_id', $todayClasses->pluck('id'))->count();
        
        // Hourly attendance data for chart (mock for now)
        $hourlyData = [];
        for ($h = 6; $h <= 21; $h++) {
            $hourlyData[] = [
                'hour' => $h,
                'count' => rand(5, 45)
            ];
        }
        
        // Recent activity (recent bookings)
        $recentActivity = Booking::with(['user', 'classSession'])
            ->orderBy('booked_at', 'desc')
            ->take(5)
            ->get();
        
        // Recent signups
        $recentSignups = User::where('is_admin', false)
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        return view('admin.overview', [
            'user' => $user,
            'totalMembers' => $totalMembers,
            'activeBookings' => $activeBookings,
            'todayCheckIns' => $todayCheckIns,
            'hourlyData' => $hourlyData,
            'recentActivity' => $recentActivity,
            'recentSignups' => $recentSignups,
        ]);
    }

    /**
     * Manage classes page.
     */
    public function classes(Request $request)
    {
        $selectedDate = $request->get('date') ? Carbon::parse($request->get('date')) : Carbon::today();
        
        // Get week days (full 7-day week, Mon-Sun)
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
        
        $classes = ClassSession::withCount('bookings')
            ->whereBetween('start_time', [$dayStart, $dayEnd])
            ->orderBy('start_time')
            ->get()
            ->groupBy(function($class) {
                $hour = $class->start_time->hour;
                if ($hour < 12) return 'morning';
                if ($hour < 17) return 'afternoon';
                return 'evening';
            });

        return view('admin.classes', [
            'selectedDate' => $selectedDate,
            'weekDays' => $weekDays,
            'weekStart' => $weekStart,
            'prevWeek' => $prevWeek,
            'nextWeek' => $nextWeek,
            'classes' => $classes,
        ]);
    }

    /**
     * Show add class form.
     */
    public function createClass()
    {
        $coaches = User::where('is_coach', true)->orderBy('first_name')->get();
        
        return view('admin.class-create', [
            'coaches' => $coaches,
        ]);
    }

    /**
     * Store new class.
     */
    public function storeClass(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:Gi,No-Gi,Open Mat,Fundamentals',
            'age_group' => 'required|in:Kids,Adults,All',
            'instructor_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'time' => 'required',
            'duration_minutes' => 'required|integer|min:30|max:180',
            'capacity' => 'required|integer|min:1|max:100',
            'recurring' => 'boolean',
        ]);

        $startTime = Carbon::parse($validated['date'] . ' ' . $validated['time']);
        $instructor = User::find($validated['instructor_id']);

        // Create the class
        ClassSession::create([
            'title' => $validated['title'],
            'type' => $validated['type'],
            'age_group' => $validated['age_group'],
            'start_time' => $startTime,
            'duration_minutes' => $validated['duration_minutes'],
            'instructor_id' => $validated['instructor_id'],
            'instructor_name' => $instructor->name, // Keep for backward compatibility
            'capacity' => $validated['capacity'],
        ]);

        // If recurring, create for next 4 weeks
        if ($request->boolean('recurring')) {
            for ($week = 1; $week <= 4; $week++) {
                ClassSession::create([
                    'title' => $validated['title'],
                    'type' => $validated['type'],
                    'age_group' => $validated['age_group'],
                    'start_time' => $startTime->copy()->addWeeks($week),
                    'duration_minutes' => $validated['duration_minutes'],
                    'instructor_id' => $validated['instructor_id'],
                    'instructor_name' => $instructor->name,
                    'capacity' => $validated['capacity'],
                ]);
            }
        }

        return redirect()->route('admin.classes')->with('success', 'Class created successfully.');
    }

    /**
     * Edit class.
     */
    public function editClass($id)
    {
        $class = ClassSession::findOrFail($id);
        $coaches = User::where('is_coach', true)->orderBy('first_name')->get();
        
        return view('admin.class-edit', [
            'class' => $class,
            'coaches' => $coaches,
        ]);
    }

    /**
     * Update class.
     */
    public function updateClass(Request $request, $id)
    {
        $class = ClassSession::findOrFail($id);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:Gi,No-Gi,Open Mat,Fundamentals',
            'age_group' => 'required|in:Kids,Adults,All',
            'instructor_id' => 'required|exists:users,id',
            'capacity' => 'required|integer|min:1|max:100',
        ]);
        
        $instructor = User::find($validated['instructor_id']);

        $class->update([
            'title' => $validated['title'],
            'type' => $validated['type'],
            'age_group' => $validated['age_group'],
            'instructor_id' => $validated['instructor_id'],
            'instructor_name' => $instructor->name,
            'capacity' => $validated['capacity'],
        ]);

        return redirect()->route('admin.classes')->with('success', 'Class updated successfully.');
    }

    /**
     * Delete class.
     */
    public function deleteClass($id)
    {
        $class = ClassSession::findOrFail($id);
        $class->delete();
        
        return redirect()->route('admin.classes')->with('success', 'Class deleted.');
    }

    /**
     * Attendance tracker for a specific class.
     */
    public function attendance($classId)
    {
        $class = ClassSession::with(['bookings.user'])->withCount('bookings')->findOrFail($classId);
        
        $bookedUsers = $class->bookings->map(function($booking) {
            return [
                'booking' => $booking,
                'user' => $booking->user,
                'checked_in' => $booking->checked_in ?? false,
            ];
        });

        // Get non-booked members for potential walk-ins
        $bookedUserIds = $class->bookings->pluck('user_id');
        $availableMembers = User::where('is_admin', false)
            ->whereNotIn('id', $bookedUserIds)
            ->get();

        $waitlistCount = max(0, $class->bookings_count - $class->capacity);
        $checkedInCount = $class->bookings->where('checked_in', true)->count();

        return view('admin.attendance', [
            'class' => $class,
            'bookedUsers' => $bookedUsers,
            'availableMembers' => $availableMembers,
            'checkedInCount' => $checkedInCount,
            'waitlistCount' => $waitlistCount,
        ]);
    }

    /**
     * Toggle check-in status.
     */
    public function toggleCheckIn(Request $request, $classId, $bookingId)
    {
        $booking = Booking::where('class_id', $classId)->findOrFail($bookingId);
        $booking->checked_in = !$booking->checked_in;
        $booking->save();

        return back()->with('success', 'Check-in status updated.');
    }

    /**
     * Financial management page.
     */
    public function finance()
    {
        // Calculate MRR (Monthly Recurring Revenue)
        $currentMonth = now()->format('F Y');
        $paidThisMonth = Payment::where('status', 'Paid')
            ->where('month', $currentMonth)
            ->sum('amount');
        
        $totalMembers = User::where('is_admin', false)->count();
        $activeMembers = Payment::where('status', 'Paid')
            ->where('month', $currentMonth)
            ->distinct('user_id')
            ->count('user_id');

        // Membership breakdown (mock data for now)
        $membershipPlans = [
            ['name' => 'Unlimited (Adults)', 'percentage' => 65, 'color' => 'bg-blue-500'],
            ['name' => 'Kids Program', 'percentage' => 25, 'color' => 'bg-emerald-500'],
            ['name' => '3x / Week', 'percentage' => 10, 'color' => 'bg-amber-500'],
        ];

        // Recent transactions
        $recentPayments = Payment::with('user')
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();

        // Pending payments count
        $pendingCount = Payment::where('status', 'Pending Verification')->count();
        $failedCount = Payment::where('status', 'Rejected')->count();

        return view('admin.finance', [
            'mrr' => $paidThisMonth,
            'activeMembers' => $activeMembers,
            'totalMembers' => $totalMembers,
            'membershipPlans' => $membershipPlans,
            'recentPayments' => $recentPayments,
            'pendingCount' => $pendingCount,
            'failedCount' => $failedCount,
        ]);
    }

    /**
     * Display member directory.
     */
    public function members(Request $request)
    {
        $search = $request->get('search');
        $filter = $request->get('filter', 'All');

        $query = User::where('is_admin', false);

        // Search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('rank', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Age group filter
        if ($filter === 'Adults') {
            $query->where('age_group', 'Adults');
        } elseif ($filter === 'Kids') {
            $query->where('age_group', 'Kids');
        }
        // 'All' and 'Competitors' show all for now

        $members = $query->orderBy('first_name')->orderBy('last_name')->get();

        $stats = [
            'total_members' => User::where('is_admin', false)->count(),
            'pending_payments' => Payment::where('status', 'Pending Verification')->count(),
        ];

        return view('admin.members', [
            'members' => $members,
            'stats' => $stats,
            'currentFilter' => $filter,
            'search' => $search,
        ]);
    }

    /**
     * Show member details/edit form.
     */
    public function showMember($id)
    {
        $member = User::where('is_admin', false)->with('membershipPackage')->findOrFail($id);
        $payments = $member->payments()->orderBy('created_at', 'desc')->get();
        $bookings = $member->bookings()->with('classSession')->orderBy('booked_at', 'desc')->take(10)->get();
        $packages = MembershipPackage::active()->ordered()->get();

        return view('admin.member-detail', [
            'member' => $member,
            'payments' => $payments,
            'bookings' => $bookings,
            'packages' => $packages,
        ]);
    }

    /**
     * Update member details.
     */
    public function updateMember(Request $request, $id)
    {
        $member = User::where('is_admin', false)->findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'age_group' => 'required|in:Kids,Adults',
            'rank' => 'required|in:White,Grey,Yellow,Orange,Green,Blue,Purple,Brown,Black',
            'stripes' => 'required|integer|min:0|max:4',
            'mat_hours' => 'required|integer|min:0',
            'is_coach' => 'boolean',
        ]);

        $validated['is_coach'] = $request->has('is_coach');

        $member->update($validated);

        return back()->with('success', 'Member updated successfully.');
    }

    /**
     * Show create member form.
     */
    public function createMember()
    {
        return view('admin.member-create');
    }

    /**
     * Store new member.
     */
    public function storeMember(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'age_group' => 'required|in:Kids,Adults',
            'rank' => 'required|in:White,Grey,Yellow,Orange,Green,Blue,Purple,Brown,Black',
            'stripes' => 'required|integer|min:0|max:4',
        ]);

        $member = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'age_group' => $validated['age_group'],
            'rank' => $validated['rank'],
            'stripes' => $validated['stripes'],
            'mat_hours' => 0,
            'is_admin' => false,
        ]);

        // Create initial payment record for current month
        Payment::create([
            'user_id' => $member->id,
            'amount' => 1500,
            'month' => now()->format('F Y'),
            'status' => 'Overdue',
        ]);

        return redirect()->route('admin.members')->with('success', 'Member added successfully.');
    }

    /**
     * Delete a member.
     */
    public function deleteMember($id)
    {
        $member = User::where('is_admin', false)->findOrFail($id);
        
        // Delete related records
        $member->bookings()->delete();
        $member->payments()->delete();
        
        // Delete the member
        $member->delete();
        
        return redirect()->route('admin.members')->with('success', 'Member deleted successfully.');
    }

    /**
     * Update member's membership.
     */
    public function updateMembership(Request $request, $id)
    {
        $member = User::where('is_admin', false)->findOrFail($id);

        $validated = $request->validate([
            'membership_package_id' => 'nullable|exists:membership_packages,id',
            'membership_status' => 'required|in:none,pending,active,expired',
            'membership_expires_at' => 'nullable|date',
            'classes_remaining' => 'nullable|integer|min:0',
        ]);

        // If a package is selected and status is being set to active, calculate expiration
        if ($validated['membership_package_id'] && $validated['membership_status'] === 'active' && !$validated['membership_expires_at']) {
            $package = MembershipPackage::find($validated['membership_package_id']);
            if ($package && $package->duration_type !== 'classes') {
                $expiresAt = now();
                switch ($package->duration_type) {
                    case 'days':
                        $expiresAt = $expiresAt->addDays($package->duration_value);
                        break;
                    case 'weeks':
                        $expiresAt = $expiresAt->addWeeks($package->duration_value);
                        break;
                    case 'months':
                        $expiresAt = $expiresAt->addMonths($package->duration_value);
                        break;
                    case 'years':
                        $expiresAt = $expiresAt->addYears($package->duration_value);
                        break;
                }
                $validated['membership_expires_at'] = $expiresAt;
            }

            // Set classes remaining for class-based packages
            if ($package && $package->duration_type === 'classes' && $validated['classes_remaining'] === null) {
                $validated['classes_remaining'] = $package->duration_value;
            }
        }

        $member->update([
            'membership_package_id' => $validated['membership_package_id'],
            'membership_status' => $validated['membership_status'],
            'membership_expires_at' => $validated['membership_expires_at'],
            'classes_remaining' => $validated['classes_remaining'],
        ]);

        return redirect()->route('admin.members.show', $member->id)->with('success', 'Membership updated successfully.');
    }

    /**
     * Display payments management.
     */
    public function payments()
    {
        $pendingPayments = Payment::with('user')
            ->where('status', 'Pending Verification')
            ->orderBy('submitted_at')
            ->get();

        $allPayments = Payment::with('user')
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();

        $stats = [
            'total_members' => User::where('is_admin', false)->count(),
            'pending_payments' => $pendingPayments->count(),
            'paid_this_month' => Payment::where('status', 'Paid')
                ->whereMonth('updated_at', now()->month)
                ->count(),
        ];

        return view('admin.payments', [
            'pendingPayments' => $pendingPayments,
            'allPayments' => $allPayments,
            'stats' => $stats,
        ]);
    }

    /**
     * Approve a payment.
     */
    public function approvePayment($id)
    {
        $payment = Payment::findOrFail($id);
        $payment->update(['status' => 'Paid']);

        return back()->with('success', 'Payment approved successfully.');
    }

    /**
     * Reject a payment.
     */
    public function rejectPayment($id)
    {
        $payment = Payment::findOrFail($id);
        $payment->update(['status' => 'Rejected']);

        return back()->with('success', 'Payment rejected.');
    }
}
