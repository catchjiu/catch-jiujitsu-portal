<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\User;
use App\Models\ClassSession;
use App\Models\Booking;
use App\Models\MembershipPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        
        // Total notification count
        $notificationCount = $expiringMemberships->count() + $newSignupsToday->count();

        return view('admin.overview', [
            'user' => $user,
            'totalMembers' => $totalMembers,
            'activeBookings' => $activeBookings,
            'todayCheckIns' => $todayCheckIns,
            'hourlyData' => $hourlyData,
            'recentActivity' => $recentActivity,
            'recentSignups' => $recentSignups,
            'expiringMemberships' => $expiringMemberships,
            'newSignupsToday' => $newSignupsToday,
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
                $basePrice = $package->price - ($user->discount_amount ?? 0);
                if ($basePrice < 0) $basePrice = 0;
                
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
        
        // Monthly member growth (last 6 months)
        $memberGrowth = [];
        $monthLabels = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthLabels[] = $date->format('M');
            $memberGrowth[] = User::where('is_admin', false)
                ->where('created_at', '<=', $date->endOfMonth())
                ->count();
        }
        
        // Memberships expiring soon (next 7 days)
        $expiringSoon = User::where('is_admin', false)
            ->where('membership_status', 'active')
            ->where('membership_expires_at', '>=', now())
            ->where('membership_expires_at', '<=', now()->addDays(7))
            ->count();
        
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
            'estimatedRevenue' => round($estimatedRevenue),
            'monthLabels' => json_encode($monthLabels),
            'memberGrowth' => json_encode($memberGrowth),
            'expiringSoon' => $expiringSoon,
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
     * Update member's avatar.
     */
    public function updateMemberAvatar(Request $request, $id)
    {
        $member = User::where('is_admin', false)->findOrFail($id);

        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:10240',
        ]);

        // Delete old avatar if exists
        if ($member->avatar_url && !str_starts_with($member->avatar_url, 'http')) {
            Storage::disk('public')->delete($member->avatar_url);
        }

        $file = $request->file('avatar');
        $extension = strtolower($file->getClientOriginalExtension());
        
        // Read and resize image using GD
        $maxWidth = 400;
        $maxHeight = 400;
        $quality = 85;
        
        // Get image info
        $imageInfo = getimagesize($file->getPathname());
        $originalWidth = $imageInfo[0];
        $originalHeight = $imageInfo[1];
        $mimeType = $imageInfo['mime'];
        
        // Create image resource based on type
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
                return back()->with('error', 'Unsupported image format.');
        }
        
        if (!$sourceImage) {
            return back()->with('error', 'Failed to process image.');
        }
        
        // Calculate new dimensions maintaining aspect ratio
        $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
        if ($ratio < 1) {
            $newWidth = (int) ($originalWidth * $ratio);
            $newHeight = (int) ($originalHeight * $ratio);
        } else {
            $newWidth = $originalWidth;
            $newHeight = $originalHeight;
        }
        
        // Create new image
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserve transparency for PNG
        if ($mimeType === 'image/png') {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
        }
        
        // Resize
        imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
        
        // Generate filename and path
        $filename = 'avatars/' . $member->id . '_' . time() . '.jpg';
        $tempPath = sys_get_temp_dir() . '/' . uniqid() . '.jpg';
        
        // Save as JPEG for smaller file size
        imagejpeg($newImage, $tempPath, $quality);
        
        // Clean up
        imagedestroy($sourceImage);
        imagedestroy($newImage);
        
        // Store in public disk
        Storage::disk('public')->put($filename, file_get_contents($tempPath));
        unlink($tempPath);
        
        // Update user
        $member->update(['avatar_url' => $filename]);

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
