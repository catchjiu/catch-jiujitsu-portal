<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\User;
use App\Models\ClassSession;
use App\Models\Booking;
use App\Models\ClassTrial;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\MembershipPackage;
use App\Models\PrivateClassBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Display admin overview dashboard.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Get filter parameters (day = sign-ups per class; week = per day; year = per week). All support past periods.
        $dateRange = $request->get('date_range', 'day');
        $ageGroup = $request->get('age_group', 'all');
        if (!in_array($dateRange, ['day', 'week', 'year'], true)) {
            $dateRange = 'day';
        }

        // Resolve period (day = single date, week = that week Mon–Sun, year = that calendar year)
        switch ($dateRange) {
            case 'day':
                $dateParam = $request->get('date');
                if ($dateParam && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateParam)) {
                    $startDate = Carbon::parse($dateParam)->startOfDay();
                    $endDate = $startDate->copy()->endOfDay();
                } else {
                    $startDate = Carbon::today();
                    $endDate = Carbon::today()->endOfDay();
                }
                $dateLabel = $startDate->isToday() ? 'Today, ' . $startDate->format('M d') : $startDate->format('l, M d, Y');
                break;
            case 'week':
                $weekParam = $request->get('week');
                if ($weekParam && preg_match('/^\d{4}-W\d{2}$/', $weekParam)) {
                    // ISO week e.g. 2025-W07
                    $startDate = Carbon::now()->setISODate((int) substr($weekParam, 0, 4), (int) substr($weekParam, 6, 2))->startOfWeek(Carbon::MONDAY);
                    $endDate = $startDate->copy()->endOfWeek(Carbon::SUNDAY);
                } elseif ($weekParam && preg_match('/^\d{4}-\d{2}-\d{2}$/', $weekParam)) {
                    $startDate = Carbon::parse($weekParam)->startOfWeek(Carbon::MONDAY);
                    $endDate = $startDate->copy()->endOfWeek(Carbon::SUNDAY);
                } else {
                    $startDate = Carbon::now()->copy()->startOfWeek(Carbon::MONDAY);
                    $endDate = Carbon::now()->copy()->endOfWeek(Carbon::SUNDAY);
                }
                $dateLabel = $startDate->format('M d') . ' – ' . $endDate->format('M d, Y');
                break;
            case 'year':
                $yearParam = $request->get('year');
                $y = is_numeric($yearParam) && (int) $yearParam >= 2020 && (int) $yearParam <= 2030 ? (int) $yearParam : (int) now()->format('Y');
                $startDate = Carbon::createFromDate($y, 1, 1)->startOfDay();
                $endDate = Carbon::createFromDate($y, 12, 31)->endOfDay();
                $dateLabel = (string) $y;
                break;
            default:
                $startDate = Carbon::today();
                $endDate = Carbon::today()->endOfDay();
                $dateLabel = 'Today, ' . now()->format('M d');
                break;
        }
        
        // Stats
        $totalMembers = User::where('is_admin', false)->count();
        $activeBookings = Booking::whereHas('classSession', function($q) {
            $q->where('start_time', '>', now());
        })->count();
        
        // Base class filter for attendance (not cancelled, optional age group)
        $classFilter = function ($q) use ($ageGroup) {
            $q->where(function ($q) {
                $q->where('is_cancelled', false)->orWhereNull('is_cancelled');
            });
            if ($ageGroup !== 'all') {
                $q->where(function ($q) use ($ageGroup) {
                    $q->where('age_group', $ageGroup)->orWhere('age_group', 'All');
                });
            }
        };
        
        $todayCheckIns = Booking::whereHas('classSession', function ($q) use ($startDate, $endDate, $classFilter) {
            $q->whereBetween('start_time', [$startDate, $endDate]);
            $classFilter($q);
        })->count();
        
        $attendanceChartMode = 'classes'; // classes | days | weeks | months
        $classAttendanceData = [];
        $aggregatedChartData = [];
        $peakHoursText = 'No classes in range';
        $yAxisMax = 30;
        
        if ($dateRange === 'day') {
            // Sign-ups per class for the day (for line chart)
            $classesQuery = ClassSession::whereBetween('start_time', [$startDate, $endDate])
                ->where($classFilter);
            $filteredClasses = $classesQuery->withCount('bookings')->orderBy('start_time')->get();
            foreach ($filteredClasses as $class) {
                $count = $class->bookings_count;
                $heightPercent = $yAxisMax > 0 ? min(100, ($count / $yAxisMax) * 100) : 0;
                $classAttendanceData[] = [
                    'label' => $class->start_time->format('g:i A'),
                    'time' => $class->start_time->format('g:i A'),
                    'title' => $class->title,
                    'count' => $count,
                    'height' => $heightPercent,
                    'class_id' => $class->id,
                ];
            }
            $peakClass = collect($classAttendanceData)->sortByDesc('count')->first();
            if ($peakClass && $peakClass['count'] > 0) {
                $peakHoursText = $peakClass['time'] . ' – ' . $peakClass['title'] . ' (' . $peakClass['count'] . ')';
            }
        } elseif ($dateRange === 'week') {
            // One bar per day (Mon–Sun)
            $attendanceChartMode = 'days';
            for ($d = 0; $d < 7; $d++) {
                $dayStart = $startDate->copy()->addDays($d)->startOfDay();
                $dayEnd = $dayStart->copy()->endOfDay();
                $count = Booking::whereHas('classSession', function ($q) use ($dayStart, $dayEnd, $classFilter) {
                    $q->whereBetween('start_time', [$dayStart, $dayEnd]);
                    $classFilter($q);
                })->count();
                $aggregatedChartData[] = [
                    'label' => $dayStart->format('D d'),
                    'count' => $count,
                ];
            }
            $maxCount = max(1, collect($aggregatedChartData)->max('count'));
            foreach ($aggregatedChartData as &$row) {
                $row['height'] = min(100, ($row['count'] / $maxCount) * 100);
            }
            unset($row);
            $peak = collect($aggregatedChartData)->sortByDesc('count')->first();
            if ($peak && $peak['count'] > 0) {
                $peakHoursText = $peak['label'] . ': ' . $peak['count'] . ' bookings';
            }
        } elseif ($dateRange === 'year') {
            // Sign-ups per week (for line chart)
            $attendanceChartMode = 'weeks';
            $cursor = $startDate->copy()->startOfWeek(Carbon::MONDAY);
            $weekNum = 1;
            while ($cursor->lte($endDate)) {
                $weekEnd = $cursor->copy()->endOfWeek(Carbon::SUNDAY);
                $count = Booking::whereHas('classSession', function ($q) use ($cursor, $weekEnd, $classFilter) {
                    $q->whereBetween('start_time', [$cursor, $weekEnd]);
                    $classFilter($q);
                })->count();
                $aggregatedChartData[] = [
                    'label' => 'Wk' . $weekNum,
                    'count' => $count,
                ];
                $weekNum++;
                $cursor->addWeek();
            }
            $maxCount = max(1, collect($aggregatedChartData)->max('count'));
            foreach ($aggregatedChartData as &$row) {
                $row['height'] = min(100, ($row['count'] / $maxCount) * 100);
            }
            unset($row);
            $peak = collect($aggregatedChartData)->sortByDesc('count')->first();
            if ($peak && $peak['count'] > 0) {
                $peakHoursText = $peak['label'] . ': ' . $peak['count'] . ' bookings';
            }
        }
        
        // Recent activity (recent bookings)
        $recentActivity = Booking::with(['user', 'classSession'])
            ->orderBy('booked_at', 'desc')
            ->take(5)
            ->get();
        
        // Recent signups (last 7 days)
        $recentSignups = User::where('is_admin', false)
            ->where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        // Memberships expiring in the next 3 days (for notifications)
        $expiringMemberships = User::where('is_admin', false)
            ->where('membership_status', 'active')
            ->where('membership_expires_at', '>=', now())
            ->where('membership_expires_at', '<=', now()->addDays(3))
            ->with('membershipPackage')
            ->orderBy('membership_expires_at')
            ->get();
        
        // New signups in the last 24 hours (for notifications)
        $newSignupsToday = User::where('is_admin', false)
            ->where('created_at', '>=', now()->subDay())
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Pending payment verifications (for notifications and live feed)
        $pendingPayments = Payment::with('user')
            ->where('status', 'Pending Verification')
            ->orderBy('submitted_at', 'desc')
            ->get();
        
        // Total notification count
        $notificationCount = $expiringMemberships->count() + $newSignupsToday->count() + $pendingPayments->count();

        // Current filter values for form pre-fill (past bookings)
        $filterDate = $dateRange === 'day' ? $startDate->format('Y-m-d') : now()->format('Y-m-d');
        $filterWeek = $dateRange === 'week' ? $startDate->format('o-\WW') : now()->format('o-\WW');
        $filterYear = $dateRange === 'year' ? (int) $startDate->format('Y') : (int) now()->format('Y');

        return view('admin.overview', [
            'user' => $user,
            'totalMembers' => $totalMembers,
            'activeBookings' => $activeBookings,
            'todayCheckIns' => $todayCheckIns,
            'attendanceChartMode' => $attendanceChartMode,
            'classAttendanceData' => $classAttendanceData,
            'aggregatedChartData' => $aggregatedChartData,
            'peakHoursText' => $peakHoursText,
            'dateLabel' => $dateLabel,
            'filterDate' => $filterDate,
            'filterWeek' => $filterWeek,
            'filterYear' => $filterYear,
            'recentActivity' => $recentActivity,
            'recentSignups' => $recentSignups,
            'expiringMemberships' => $expiringMemberships,
            'newSignupsToday' => $newSignupsToday,
            'pendingPayments' => $pendingPayments,
            'notificationCount' => $notificationCount,
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
        
        $allClasses = ClassSession::withCount(['bookings', 'trials'])
            ->with(['bookings.user'])
            ->whereBetween('start_time', [$dayStart, $dayEnd])
            ->orderBy('start_time')
            ->get();

        // Add paid/unpaid counts for capacity bar (paid = active membership, unpaid = no active membership)
        foreach ($allClasses as $class) {
            $paid = $class->bookings->filter(fn ($b) => $b->user && $b->user->hasActiveMembership())->count();
            $class->paid_bookings_count = $paid;
            $class->unpaid_bookings_count = $class->bookings_count - $paid;
        }

        $classes = $allClasses->groupBy(function($class) {
                $hour = $class->start_time->hour;
                if ($hour < 12) return 'morning';
                if ($hour < 17) return 'afternoon';
                return 'evening';
            });

        $privateClasses = PrivateClassBooking::with(['coach', 'member'])
            ->whereIn('status', ['pending', 'accepted'])
            ->whereBetween('scheduled_at', [$dayStart, $dayEnd])
            ->orderBy('scheduled_at')
            ->get();

        return view('admin.classes', [
            'selectedDate' => $selectedDate,
            'weekDays' => $weekDays,
            'weekStart' => $weekStart,
            'prevWeek' => $prevWeek,
            'nextWeek' => $nextWeek,
            'classes' => $classes,
            'privateClasses' => $privateClasses,
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
            'title_zh' => 'nullable|string|max:255',
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

        $payload = [
            'title' => $validated['title'],
            'title_zh' => $validated['title_zh'] ?? null,
            'type' => $validated['type'],
            'age_group' => $validated['age_group'],
            'start_time' => $startTime,
            'duration_minutes' => $validated['duration_minutes'],
            'instructor_id' => $validated['instructor_id'],
            'instructor_name' => $instructor->name,
            'capacity' => $validated['capacity'],
        ];

        $first = ClassSession::create($payload);
        $recurring = $request->boolean('recurring');

        if ($recurring) {
            $first->update(['recurrence_id' => $first->id]);
            for ($week = 1; $week <= 4; $week++) {
                ClassSession::create(array_merge($payload, [
                    'start_time' => $startTime->copy()->addWeeks($week),
                    'recurrence_id' => $first->id,
                ]));
            }
        }

        return redirect()->route('admin.classes')->with('success', 'Class created successfully.');
    }

    /**
     * Find sibling classes (same series): by recurrence_id, or by matching title + day of week + time.
     */
    private function getSiblingClasses(ClassSession $class): \Illuminate\Database\Eloquent\Collection
    {
        if ($class->recurrence_id) {
            return ClassSession::where('recurrence_id', $class->recurrence_id)
                ->where('id', '!=', $class->id)
                ->get();
        }
        $windowStart = Carbon::now()->subWeeks(2)->startOfDay();
        $windowEnd = Carbon::now()->addWeeks(12)->endOfDay();
        $dayOfWeek = $class->start_time->dayOfWeek;
        $timeHi = $class->start_time->format('H:i');
        return ClassSession::where('title', $class->title)
            ->where('type', $class->type)
            ->whereBetween('start_time', [$windowStart, $windowEnd])
            ->where('id', '!=', $class->id)
            ->get()
            ->filter(function ($c) use ($dayOfWeek, $timeHi) {
                return $c->start_time->dayOfWeek === $dayOfWeek
                    && $c->start_time->format('H:i') === $timeHi;
            });
    }

    /**
     * Edit class.
     */
    public function editClass($id)
    {
        $class = ClassSession::findOrFail($id);
        $coaches = User::where('is_coach', true)->orderBy('first_name')->get();
        $siblings = $this->getSiblingClasses($class);
        $recurrenceSiblingsCount = $siblings->count();
        
        return view('admin.class-edit', [
            'class' => $class,
            'coaches' => $coaches,
            'recurrenceSiblingsCount' => $recurrenceSiblingsCount,
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
            'title_zh' => 'nullable|string|max:255',
            'type' => 'required|in:Gi,No-Gi,Open Mat,Fundamentals',
            'age_group' => 'required|in:Kids,Adults,All',
            'instructor_id' => 'required|exists:users,id',
            'capacity' => 'required|integer|min:1|max:100',
            'is_cancelled' => 'nullable|boolean',
            'start_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'apply_to' => 'nullable|in:this,all',
        ]);
        
        $instructor = User::find($validated['instructor_id']);
        $isCancelled = $request->has('is_cancelled');
        $newStartTime = Carbon::parse($validated['start_date'] . ' ' . $validated['start_time']);

        $data = [
            'title' => $validated['title'],
            'title_zh' => $validated['title_zh'] ?? null,
            'type' => $validated['type'],
            'age_group' => $validated['age_group'],
            'instructor_id' => $validated['instructor_id'],
            'instructor_name' => $instructor->name,
            'capacity' => $validated['capacity'],
            'is_cancelled' => $isCancelled,
            'start_time' => $newStartTime,
        ];

        $siblings = $this->getSiblingClasses($class);
        $applyToAll = ($validated['apply_to'] ?? 'this') === 'all' && $siblings->isNotEmpty();

        if ($applyToAll) {
            $timeOnly = $validated['start_time'];
            $toUpdate = $class->recurrence_id
                ? ClassSession::where('recurrence_id', $class->recurrence_id)->get()
                : $siblings->push($class);
            foreach ($toUpdate as $c) {
                $newStart = Carbon::parse($c->start_time->format('Y-m-d') . ' ' . $timeOnly);
                $c->update(array_merge($data, ['start_time' => $newStart]));
            }
            $message = 'All classes in this series have been updated.';
        } else {
            $class->update($data);
            $message = 'Class updated successfully.';
        }

        if ($isCancelled && $class->bookings()->count() > 0) {
            $message .= ' ' . $class->bookings()->count() . ' member(s) will see this class as cancelled.';
        }

        return redirect()->route('admin.classes')->with('success', $message);
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

        // Get non-booked members for potential walk-ins (filter by class age_group: Kids class → kids/all, Adults → adults/all)
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

        return view('admin.attendance', [
            'class' => $class,
            'bookedUsers' => $bookedUsers,
            'trials' => $class->trials,
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
     * Remove a booking from the class (admin remove from attendance).
     */
    public function removeBooking($classId, $bookingId)
    {
        $booking = Booking::where('class_id', $classId)->findOrFail($bookingId);
        $user = $booking->user;
        $booking->delete();
        if ($user && $user->classes_remaining !== null) {
            $user->incrementClassesRemaining();
        }
        return back()->with('success', 'Removed from class.');
    }

    /**
     * Remove a trial from the class.
     */
    public function removeTrial($classId, $trialId)
    {
        $trial = ClassTrial::where('class_id', $classId)->findOrFail($trialId);
        $trial->delete();
        return back()->with('success', 'Trial removed.');
    }

    /**
     * Add a member as walk-in to the class (create booking and mark checked-in).
     */
    public function addWalkIn(Request $request, $classId)
    {
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

    /**
     * Store a trial member for a class.
     */
    public function storeTrial(Request $request, $classId)
    {
        $class = ClassSession::findOrFail($classId);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'age'  => 'nullable|integer|min:1|max:120',
        ]);
        ClassTrial::create([
            'class_id' => $class->id,
            'name'     => $validated['name'],
            'age'      => $validated['age'] ?? null,
        ]);
        return back()->with('success', 'Trial member added.');
    }

    /**
     * Financial management page.
     */
    public function finance()
    {
        // Total members
        $totalMembers = User::where('is_admin', false)->count();
        
        // Membership status breakdown
        $activeMemberships = User::where('is_admin', false)
            ->where(function($q) {
                $q->where('membership_status', 'active')
                  ->orWhere('discount_type', 'gratis');
            })->count();
        $pendingMemberships = User::where('is_admin', false)->where('membership_status', 'pending')->count();
        $expiredMemberships = User::where('is_admin', false)->where('membership_status', 'expired')->count();
        $noMembership = User::where('is_admin', false)->where('membership_status', 'none')->where('discount_type', 'none')->count();
        
        // New members this month
        $newMembersThisMonth = User::where('is_admin', false)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        
        // Age group breakdown
        $adultMembers = User::where('is_admin', false)->where('age_group', 'Adults')->count();
        $kidsMembers = User::where('is_admin', false)->where('age_group', 'Kids')->count();
        
        // Membership package breakdown (real data from database)
        $packageBreakdown = User::where('is_admin', false)
            ->whereNotNull('membership_package_id')
            ->selectRaw('membership_package_id, COUNT(*) as count')
            ->groupBy('membership_package_id')
            ->with('membershipPackage')
            ->get();
        
        $membershipPlans = [];
        $colors = ['bg-blue-500', 'bg-emerald-500', 'bg-amber-500', 'bg-purple-500', 'bg-pink-500', 'bg-cyan-500'];
        $chartColors = ['#3B82F6', '#10B981', '#F59E0B', '#8B5CF6', '#EC4899', '#06B6D4'];
        $packageLabels = [];
        $packageData = [];
        
        $totalWithPackage = $packageBreakdown->sum('count');
        foreach ($packageBreakdown as $index => $item) {
            if ($item->membershipPackage) {
                $percentage = $totalWithPackage > 0 ? round(($item->count / $totalWithPackage) * 100) : 0;
                $membershipPlans[] = [
                    'name' => $item->membershipPackage->name,
                    'count' => $item->count,
                    'percentage' => $percentage,
                    'color' => $colors[$index % count($colors)],
                ];
                $packageLabels[] = $item->membershipPackage->name;
                $packageData[] = $item->count;
            }
        }
        
        // Gratis members
        $gratisMembers = User::where('is_admin', false)->where('discount_type', 'gratis')->count();
        $discountedMembers = User::where('is_admin', false)->where('discount_type', 'fixed')->where('discount_amount', '>', 0)->count();
        
        // Calculate value of gratis memberships (potential revenue if they paid)
        $gratisValue = 0;
        $gratisUsers = User::where('is_admin', false)
            ->where('discount_type', 'gratis')
            ->whereNotNull('membership_package_id')
            ->with('membershipPackage')
            ->get();
        foreach ($gratisUsers as $user) {
            if ($user->membershipPackage) {
                $gratisValue += $user->membershipPackage->price;
            }
        }
        
        // Calculate total discounts given (monthly value of all discounts)
        $totalDiscountsGiven = 0;
        
        // Calculate estimated monthly revenue from active memberships
        $estimatedRevenue = 0;
        $activePackageUsers = User::where('is_admin', false)
            ->where('membership_status', 'active')
            ->where('discount_type', 'none')
            ->whereNotNull('membership_package_id')
            ->with('membershipPackage')
            ->get();
        
        foreach ($activePackageUsers as $user) {
            if ($user->membershipPackage) {
                // Normalize to monthly revenue
                $package = $user->membershipPackage;
                $monthlyValue = $package->price;
                if ($package->duration_type === 'months' && $package->duration_value > 1) {
                    $monthlyValue = $package->price / $package->duration_value;
                } elseif ($package->duration_type === 'years') {
                    $monthlyValue = $package->price / ($package->duration_value * 12);
                } elseif ($package->duration_type === 'weeks') {
                    $monthlyValue = ($package->price / $package->duration_value) * 4.33;
                }
                $estimatedRevenue += $monthlyValue;
            }
        }
        
        // Discounted members (fixed discount)
        $discountedUsers = User::where('is_admin', false)
            ->where('membership_status', 'active')
            ->where('discount_type', 'fixed')
            ->where('discount_amount', '>', 0)
            ->whereNotNull('membership_package_id')
            ->with('membershipPackage')
            ->get();
        
        foreach ($discountedUsers as $user) {
            if ($user->membershipPackage) {
                $package = $user->membershipPackage;
                $discountAmount = $user->discount_amount ?? 0;
                $basePrice = $package->price - $discountAmount;
                if ($basePrice < 0) $basePrice = 0;
                
                // Track total discounts given
                $totalDiscountsGiven += $discountAmount;
                
                $monthlyValue = $basePrice;
                if ($package->duration_type === 'months' && $package->duration_value > 1) {
                    $monthlyValue = $basePrice / $package->duration_value;
                } elseif ($package->duration_type === 'years') {
                    $monthlyValue = $basePrice / ($package->duration_value * 12);
                } elseif ($package->duration_type === 'weeks') {
                    $monthlyValue = ($basePrice / $package->duration_value) * 4.33;
                }
                $estimatedRevenue += $monthlyValue;
            }
        }
        
        // Monthly active member growth (last 6 months) – active only (active status or gratis)
        $memberGrowth = [];
        $monthLabels = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthLabels[] = $date->format('M');
            $memberGrowth[] = User::where('is_admin', false)
                ->where('created_at', '<=', $date->copy()->endOfMonth())
                ->where(function ($q) {
                    $q->where('membership_status', 'active')
                      ->orWhere('discount_type', 'gratis');
                })
                ->count();
        }
        
        // Memberships expiring soon (next 7 days)
        $expiringMembersList = User::where('is_admin', false)
            ->where('membership_status', 'active')
            ->where('membership_expires_at', '>=', now())
            ->where('membership_expires_at', '<=', now()->addDays(7))
            ->with('membershipPackage')
            ->orderBy('membership_expires_at')
            ->get();
        $expiringSoon = $expiringMembersList->count();
        
        // Classes data for this month
        $classesThisMonth = ClassSession::whereMonth('start_time', now()->month)
            ->whereYear('start_time', now()->year)
            ->count();
        
        $totalBookingsThisMonth = Booking::whereHas('classSession', function($q) {
            $q->whereMonth('start_time', now()->month)
              ->whereYear('start_time', now()->year);
        })->count();
        
        // Recent transactions (from payments table)
        $recentPayments = Payment::with('user')
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();
        
        // Payment status counts
        $pendingCount = Payment::where('status', 'Pending Verification')->count();
        $failedCount = Payment::where('status', 'Rejected')->count();
        $paidCount = Payment::where('status', 'Paid')->count();
        $overdueCount = Payment::where('status', 'Overdue')->count();

        return view('admin.finance', [
            'totalMembers' => $totalMembers,
            'activeMemberships' => $activeMemberships,
            'pendingMemberships' => $pendingMemberships,
            'expiredMemberships' => $expiredMemberships,
            'noMembership' => $noMembership,
            'newMembersThisMonth' => $newMembersThisMonth,
            'adultMembers' => $adultMembers,
            'kidsMembers' => $kidsMembers,
            'membershipPlans' => $membershipPlans,
            'packageLabels' => json_encode($packageLabels),
            'packageData' => json_encode($packageData),
            'chartColors' => json_encode($chartColors),
            'gratisMembers' => $gratisMembers,
            'discountedMembers' => $discountedMembers,
            'gratisValue' => round($gratisValue),
            'totalDiscountsGiven' => round($totalDiscountsGiven),
            'estimatedRevenue' => round($estimatedRevenue),
            'monthLabels' => json_encode($monthLabels),
            'memberGrowth' => json_encode($memberGrowth),
            'expiringSoon' => $expiringSoon,
            'expiringMembersList' => $expiringMembersList,
            'classesThisMonth' => $classesThisMonth,
            'totalBookingsThisMonth' => $totalBookingsThisMonth,
            'recentPayments' => $recentPayments,
            'pendingCount' => $pendingCount,
            'failedCount' => $failedCount,
            'paidCount' => $paidCount,
            'overdueCount' => $overdueCount,
        ]);
    }

    /**
     * Display member directory.
     */
    public function members(Request $request)
    {
        $search = $request->get('search');
        $ageFilter = $request->get('age', '');
        $statusFilter = $request->get('status', '');

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
        if ($ageFilter === 'Adults') {
            $query->where('age_group', 'Adults');
        } elseif ($ageFilter === 'Kids') {
            $query->where('age_group', 'Kids');
        }
        
        // Status filter (active = has active membership or gratis, inactive = expired/none/pending)
        if ($statusFilter === 'active') {
            $query->where(function($q) {
                $q->where('membership_status', 'active')
                  ->orWhere('discount_type', 'gratis');
            });
        } elseif ($statusFilter === 'inactive') {
            $query->where('discount_type', '!=', 'gratis')
                  ->where(function($q) {
                      $q->where('membership_status', 'expired')
                        ->orWhere('membership_status', 'none')
                        ->orWhere('membership_status', 'pending')
                        ->orWhereNull('membership_status');
                  });
        }

        $members = $query->orderBy('first_name')->orderBy('last_name')->get();

        // User IDs with at least one class (group or private) in the last 7 days (for profile dot)
        $recentGroup = Booking::whereHas('classSession', fn ($q) => $q->where('start_time', '>=', now()->subDays(7)))
            ->distinct()->pluck('user_id');
        $recentPrivate = PrivateClassBooking::where('scheduled_at', '>=', now()->subDays(7))
            ->distinct()->pluck('member_id');
        $userIdsWithClassInLast7Days = $recentGroup->merge($recentPrivate)->unique()->flip();

        $stats = [
            'total_members' => User::where('is_admin', false)->count(),
            'pending_payments' => Payment::where('status', 'Pending Verification')->count(),
        ];

        return view('admin.members', [
            'members' => $members,
            'userIdsWithClassInLast7Days' => $userIdsWithClassInLast7Days,
            'stats' => $stats,
            'search' => $search,
        ]);
    }

    /**
     * Show member details/edit form.
     */
    public function showMember($id)
    {
        $member = User::where('is_admin', false)->with(['membershipPackage', 'familyMember.family.members.user'])->findOrFail($id);
        $payments = $member->payments()->orderBy('created_at', 'desc')->get();
        $bookings = $member->bookings()->with('classSession')->orderBy('booked_at', 'desc')->take(10)->get();
        $packages = MembershipPackage::active()->ordered()->get();
        $memberFamily = $member->familyMember?->family;
        $memberFamilyUsers = $memberFamily ? $memberFamily->members()->with('user')->get() : collect();

        return view('admin.member-detail', [
            'member' => $member,
            'payments' => $payments,
            'bookings' => $bookings,
            'packages' => $packages,
            'memberFamily' => $memberFamily,
            'memberFamilyUsers' => $memberFamilyUsers,
        ]);
    }

    /**
     * Search members that can be added to this member's family (for admin family search).
     */
    public function searchFamilyMembers(Request $request, $id)
    {
        $member = User::where('is_admin', false)->findOrFail($id);
        $q = $request->get('q', '');
        $query = User::where('is_admin', false)
            ->where('id', '!=', $id)
            ->whereDoesntHave('familyMember');
        if (strlen($q) >= 1) {
            $query->where(function ($qry) use ($q) {
                $qry->where('first_name', 'like', '%' . $q . '%')
                    ->orWhere('last_name', 'like', '%' . $q . '%')
                    ->orWhere('email', 'like', '%' . $q . '%');
            });
        }
        $users = $query->orderBy('first_name')->orderBy('last_name')->limit(20)->get(['id', 'first_name', 'last_name', 'email', 'avatar_url']);
        return response()->json($users->map(function ($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'avatar' => $u->avatar,
            ];
        }));
    }

    /**
     * Add a family member to this member's family (create family if needed).
     */
    public function addFamilyMember(Request $request, $id)
    {
        $member = User::where('is_admin', false)->findOrFail($id);
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:parent,child',
        ]);
        $userId = (int) $validated['user_id'];
        if ($userId === (int) $id) {
            return back()->with('error', 'Cannot add the same member to their own family.');
        }
        $other = User::findOrFail($userId);
        if ($other->is_admin) {
            return back()->with('error', 'Cannot add an admin to a family.');
        }
        if ($other->familyMember) {
            return back()->with('error', 'That member already belongs to another family.');
        }

        $family = $member->familyMember?->family;
        if (!$family) {
            $family = Family::create(['primary_user_id' => $member->id]);
            FamilyMember::create(['family_id' => $family->id, 'user_id' => $member->id, 'role' => 'parent']);
        }
        if (!$family->canAddMember($validated['role'])) {
            return back()->with('error', $validated['role'] === 'child' ? 'Family already has 3 children.' : 'Family already has 2 parents.');
        }
        FamilyMember::create(['family_id' => $family->id, 'user_id' => $userId, 'role' => $validated['role']]);
        return back()->with('success', $other->name . ' added to family.');
    }

    /**
     * Remove a user from this member's family.
     */
    public function removeFamilyMember($id, $userId)
    {
        $member = User::where('is_admin', false)->findOrFail($id);
        $family = $member->familyMember?->family;
        if (!$family) {
            return back()->with('error', 'No family found.');
        }
        $fm = FamilyMember::where('family_id', $family->id)->where('user_id', $userId)->firstOrFail();
        $fm->delete();
        if ($family->members()->count() === 0) {
            $family->delete();
        }
        return back()->with('success', 'Removed from family.');
    }

    /**
     * Kids Mat Hours: list Kids members with editable mat_hours.
     */
    public function kidsMatHours()
    {
        $kids = User::where('is_admin', false)
            ->where('age_group', 'Kids')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'mat_hours']);

        return view('admin.kids-mat-hours', ['kids' => $kids]);
    }

    /**
     * Update Kids mat_hours in bulk.
     */
    public function updateKidsMatHours(Request $request)
    {
        $validated = $request->validate([
            'mat_hours' => 'required|array',
            'mat_hours.*' => 'nullable|integer|min:0',
        ]);

        $kids = User::where('is_admin', false)
            ->where('age_group', 'Kids')
            ->whereIn('id', array_keys($validated['mat_hours']))
            ->get();

        foreach ($kids as $user) {
            $value = $validated['mat_hours'][$user->id] ?? 0;
            $user->update(['mat_hours' => (int) $value]);
        }

        return back()->with('success', 'Mat hours updated.');
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
            'dob' => 'nullable|date',
            'age_group' => 'required|in:Kids,Adults',
            'rank' => 'required|in:White,Grey,Yellow,Orange,Green,Blue,Purple,Brown,Black',
            'belt_variation' => 'nullable|in:white,solid,black',
            'stripes' => 'required|integer|min:0|max:4',
            'mat_hours' => 'nullable|integer|min:0',
            'is_coach' => 'boolean',
            'discount_type' => 'required|in:none,gratis,fixed,percentage,half_price',
            'discount_amount' => 'nullable|integer|min:0|max:100000',
        ]);

        $validated['is_coach'] = $request->has('is_coach');
        
        // Handle backward compatibility - convert old types to 'fixed'
        if (in_array($validated['discount_type'], ['half_price', 'percentage'])) {
            $validated['discount_type'] = 'fixed';
        }
        
        // Set discount_amount to 0 if not using fixed discount
        if ($validated['discount_type'] !== 'fixed') {
            $validated['discount_amount'] = 0;
        }
        
        // Clear belt_variation for non-kids belts
        $kidsBelts = ['Grey', 'Yellow', 'Orange', 'Green'];
        if (!in_array($validated['rank'], $kidsBelts)) {
            $validated['belt_variation'] = null;
        }

        $validated['dob'] = !empty($validated['dob']) ? $validated['dob'] : null;
        $validated['mat_hours'] = $validated['mat_hours'] ?? 0;
        $member->update($validated);

        return redirect()->route('admin.members.show', $member->id)->with('success', 'Member updated successfully.');
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
            'chinese_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:50',
            'dob' => 'nullable|date',
            'line_id' => 'nullable|string|max:255',
            'gender' => 'nullable|in:male,female',
            'age_group' => 'required|in:Kids,Adults',
            'rank' => 'required|in:White,Grey,Yellow,Orange,Green,Blue,Purple,Brown,Black',
            'belt_variation' => 'nullable|in:white,solid,black',
            'stripes' => 'required|integer|min:0|max:4',
            'avatar' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:10240',
        ]);

        $kidsBelts = ['Grey', 'Yellow', 'Orange', 'Green'];
        $beltVariation = in_array($validated['rank'], $kidsBelts) ? ($validated['belt_variation'] ?? null) : null;

        $member = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'chinese_name' => $validated['chinese_name'] ?? null,
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'dob' => $validated['dob'] ?? null,
            'line_id' => $validated['line_id'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'age_group' => $validated['age_group'],
            'rank' => $validated['rank'],
            'belt_variation' => $beltVariation,
            'stripes' => $validated['stripes'],
            'mat_hours' => 0,
            'is_admin' => false,
        ]);

        if ($request->hasFile('avatar')) {
            $filename = $this->processMemberAvatarUpload($request->file('avatar'), $member);
            $member->update(['avatar_url' => $filename]);
        }

        return redirect()->route('admin.members')->with('success', 'Member added successfully.');
    }

    /**
     * Process avatar upload: resize and save. Returns storage path.
     */
    private function processMemberAvatarUpload($file, User $member): string
    {
        $imageInfo = getimagesize($file->getPathname());
        $originalWidth = $imageInfo[0];
        $originalHeight = $imageInfo[1];
        $mimeType = $imageInfo['mime'];

        switch ($mimeType) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($file->getPathname());
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($file->getPathname());
                break;
            case 'image/gif':
                $sourceImage = imagecreatefromgif($file->getPathname());
                break;
            case 'image/webp':
                $sourceImage = imagecreatefromwebp($file->getPathname());
                break;
            default:
                throw new \InvalidArgumentException('Unsupported image format.');
        }

        if (!$sourceImage) {
            throw new \InvalidArgumentException('Failed to process image.');
        }

        $maxWidth = 400;
        $maxHeight = 400;
        $quality = 85;
        $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
        if ($ratio < 1) {
            $newWidth = (int) ($originalWidth * $ratio);
            $newHeight = (int) ($originalHeight * $ratio);
        } else {
            $newWidth = $originalWidth;
            $newHeight = $originalHeight;
        }

        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        if ($mimeType === 'image/png') {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
        }
        imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

        $filename = 'avatars/' . $member->id . '_' . time() . '.jpg';
        $tempPath = sys_get_temp_dir() . '/' . uniqid() . '.jpg';
        imagejpeg($newImage, $tempPath, $quality);
        imagedestroy($sourceImage);
        imagedestroy($newImage);
        Storage::disk('public')->put($filename, file_get_contents($tempPath));
        unlink($tempPath);

        return $filename;
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
     * Update member's avatar.
     */
    public function updateMemberAvatar(Request $request, $id)
    {
        $member = User::where('is_admin', false)->findOrFail($id);

        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:10240',
        ]);

        if ($member->avatar_url && !str_starts_with($member->avatar_url, 'http')) {
            Storage::disk('public')->delete($member->avatar_url);
        }

        try {
            $filename = $this->processMemberAvatarUpload($request->file('avatar'), $member);
            $member->update(['avatar_url' => $filename]);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.members.show', $member->id)->with('success', 'Profile picture updated.');
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
        $pendingPayments = Payment::with(['user', 'user.membershipPackage'])
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
        
        $packages = MembershipPackage::active()->ordered()->get();

        return view('admin.payments', [
            'pendingPayments' => $pendingPayments,
            'allPayments' => $allPayments,
            'stats' => $stats,
            'packages' => $packages,
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
     * Approve a payment and update membership.
     */
    public function approvePaymentWithMembership(Request $request, $id)
    {
        $payment = Payment::with('user')->findOrFail($id);
        
        // Approve the payment
        $payment->update(['status' => 'Paid']);
        
        // Update membership if data provided
        $member = $payment->user;
        
        $validated = $request->validate([
            'membership_package_id' => 'nullable|exists:membership_packages,id',
            'membership_status' => 'required|in:none,pending,active,expired',
            'membership_expires_at' => 'nullable|date',
            'classes_remaining' => 'nullable|integer|min:0',
        ]);
        
        // Calculate expiration if package selected and not provided
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
            if ($package && $package->duration_type === 'classes' && empty($validated['classes_remaining'])) {
                $validated['classes_remaining'] = $package->duration_value;
            }
        }
        
        $member->update([
            'membership_package_id' => $validated['membership_package_id'],
            'membership_status' => $validated['membership_status'],
            'membership_expires_at' => $validated['membership_expires_at'],
            'classes_remaining' => $validated['classes_remaining'],
        ]);

        return back()->with('success', 'Payment approved and membership updated successfully.');
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
