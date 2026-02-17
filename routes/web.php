<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FamilyDashboardController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\GoalsController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MembershipPackageController;
use App\Http\Controllers\CheckInController;
use App\Http\Controllers\PrivateClassController;
use App\Http\Controllers\LineWebhookController;
use App\Http\Controllers\LiffAuthController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\ShopAdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

// Public routes

Route::get('/debug/runtime', function (Request $request) {
    $token = (string) env('DEBUG_TOKEN', '');
    if ($token !== '' && !hash_equals($token, (string) $request->query('token', ''))) {
        abort(403, 'Forbidden');
    }

    $tableChecks = [];
    foreach ([
        'users',
        'sessions',
        'classes',
        'bookings',
        'membership_packages',
        'orders',
        'order_items',
        'products',
        'product_variants',
        'private_class_bookings',
        'family_members',
    ] as $table) {
        try {
            $tableChecks[$table] = Schema::hasTable($table);
        } catch (\Throwable $e) {
            $tableChecks[$table] = 'error: '.$e->getMessage();
        }
    }

    try {
        DB::connection()->getPdo();
        $dbStatus = [
            'ok' => true,
            'driver' => DB::getDriverName(),
            'default_connection' => config('database.default'),
        ];
    } catch (\Throwable $e) {
        $dbStatus = [
            'ok' => false,
            'driver' => config('database.default'),
            'error' => $e->getMessage(),
        ];
    }

    $lastException = null;
    $lastExceptionFile = storage_path('app/runtime-last-exception.json');
    if (is_file($lastExceptionFile)) {
        $raw = @file_get_contents($lastExceptionFile);
        $decoded = is_string($raw) ? json_decode($raw, true) : null;
        $lastException = is_array($decoded) ? $decoded : ['raw' => $raw];
    }

    return response()->json([
        'app_runtime_debug' => (bool) env('APP_RUNTIME_DEBUG', false),
        'app_env' => config('app.env'),
        'app_url' => config('app.url'),
        'php_version' => PHP_VERSION,
        'db' => $dbStatus,
        'tables' => $tableChecks,
        'writable' => [
            'storage' => is_writable(storage_path()),
            'storage_sessions' => is_writable(storage_path('framework/sessions')),
            'storage_cache' => is_writable(storage_path('framework/cache')),
            'bootstrap_cache' => is_writable(base_path('bootstrap/cache')),
        ],
        'last_exception' => $lastException,
        'now' => now()->toIso8601String(),
    ]);
})->name('debug.runtime');

Route::get('/debug/throw', function (Request $request) {
    $token = (string) env('DEBUG_TOKEN', '');
    if ($token !== '' && !hash_equals($token, (string) $request->query('token', ''))) {
        abort(403, 'Forbidden');
    }

    throw new \RuntimeException('Debug throw endpoint triggered intentionally.');
})->name('debug.throw');

// LINE Messaging API webhook (no auth; CSRF excluded in bootstrap/app.php)
Route::post('/webhook/line', LineWebhookController::class)->name('webhook.line');

// LIFF – open from LINE in-app browser to log in by line_id and redirect (e.g. /liff/payments, /liff/schedule)
Route::get('/liff/ping', fn () => response('<html><body>OK</body></html>', 200, ['Content-Type' => 'text/html; charset=UTF-8']))->name('liff.ping');
Route::get('/liff/{path?}', [LiffAuthController::class, 'show'])->where('path', '.*')->name('liff.auth');
Route::post('/liff/session', [LiffAuthController::class, 'session'])->name('liff.session');

// Check-in kiosk (no auth – open in new tab for monitor)
Route::get('/checkin', [CheckInController::class, 'show'])->name('checkin');
Route::get('/api/checkin', [CheckInController::class, 'lookup'])->name('checkin.lookup');
Route::get('/', function () {
    if (auth()->check()) {
        if (auth()->user()->isAdmin()) {
            return redirect('/admin');
        }
        if (auth()->user()->isInFamily()) {
            return redirect()->route('family.dashboard');
        }
        return redirect('/dashboard');
    }
    return redirect('/login');
});

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Locale switch (public: works for guests, members, admins; uses browser language when no preference set)
Route::post('/locale', [SettingsController::class, 'updateLocale'])->name('locale.switch');

// Member routes (redirect admins to admin dashboard)
Route::middleware(['auth', 'member'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Family Dashboard (for users in a family)
    Route::get('/family/dashboard', [FamilyDashboardController::class, 'index'])->name('family.dashboard');
    Route::get('/family/settings', [FamilyDashboardController::class, 'settings'])->name('family.settings');
    Route::post('/family/switch', [FamilyDashboardController::class, 'switchMember'])->name('family.switch');

    // Schedule & Bookings
    Route::get('/schedule', [BookingController::class, 'index'])->name('schedule');
    Route::get('/check-in', [BookingController::class, 'checkInPage'])->name('checkin.page');
    Route::post('/book', [BookingController::class, 'store'])->name('book.store');
    Route::post('/check-in/today', [BookingController::class, 'checkInToday'])->name('checkin.today');
    Route::delete('/book/{classId}', [BookingController::class, 'destroy'])->name('book.destroy');
    Route::get('/class/{classId}/attendance', [BookingController::class, 'showAttendance'])->name('class.attendance');
    Route::post('/class/{classId}/attendance/toggle/{bookingId}', [BookingController::class, 'toggleCheckInCoach'])->name('class.attendance.toggle');
    Route::delete('/class/{classId}/attendance/booking/{bookingId}', [BookingController::class, 'removeBookingCoach'])->name('class.attendance.booking.remove');
    Route::delete('/class/{classId}/attendance/trial/{trialId}', [BookingController::class, 'removeTrialCoach'])->name('class.attendance.trial.remove');
    Route::post('/class/{classId}/attendance/walk-in', [BookingController::class, 'addWalkInCoach'])->name('class.attendance.walkin');

    // Payments
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments');
    Route::post('/payments/submit', [PaymentController::class, 'submitPayment'])->name('payments.submit');
    Route::post('/payments/{payment}/upload', [PaymentController::class, 'uploadProof'])->name('payments.upload');

    // Goals
    Route::get('/goals', [GoalsController::class, 'index'])->name('goals');
    Route::post('/goals', [GoalsController::class, 'update'])->name('goals.update');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile');
    Route::post('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
    Route::post('/settings/avatar', [SettingsController::class, 'updateAvatar'])->name('settings.avatar');
    Route::delete('/settings/avatar', [SettingsController::class, 'removeAvatar'])->name('settings.avatar.remove');
    Route::post('/settings/locale', [SettingsController::class, 'updateLocale'])->name('settings.locale');
    Route::post('/settings/private-class', [SettingsController::class, 'updatePrivateClass'])->name('settings.private-class');
    Route::get('/settings/line/connect', [SettingsController::class, 'lineConnect'])->name('settings.line.connect');
    Route::post('/settings/line/disconnect', [SettingsController::class, 'lineDisconnect'])->name('settings.line.disconnect');

    // Leaderboard
    Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard');

    // Gym Shop (member storefront)
    Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
    Route::get('/shop/my-orders', [ShopController::class, 'myOrders'])->name('shop.my-orders');
    Route::post('/shop/orders/submit-payment', [ShopController::class, 'submitOrderPayment'])->name('shop.orders.submit-payment');
    Route::post('/shop/orders/{order}/cancel', [ShopController::class, 'cancelOrder'])->name('shop.orders.cancel');
    Route::post('/shop/quick-buy', [ShopController::class, 'quickBuy'])->name('shop.quick-buy');
    Route::get('/shop/confirmation/{order}', [ShopController::class, 'confirmation'])->name('shop.confirmation');

    // Private classes (member: book; coach: availability + requests)
    Route::get('/private-class/coaches', [PrivateClassController::class, 'coaches'])->name('private-class.coaches');
    Route::get('/private-class/days', [PrivateClassController::class, 'days'])->name('private-class.days');
    Route::get('/private-class/slots', [PrivateClassController::class, 'slotsByDate'])->name('private-class.slots');
    Route::get('/private-class/coach/{coachId}/availability', [PrivateClassController::class, 'availability'])->name('private-class.availability');
    Route::post('/private-class/request', [PrivateClassController::class, 'request'])->name('private-class.request');
    Route::get('/coach/private-availability', [PrivateClassController::class, 'availabilityPage'])->name('coach.private-availability');
    Route::post('/coach/private-availability', [PrivateClassController::class, 'saveAvailability'])->name('coach.private-availability.save');
    Route::get('/coach/private-requests', [PrivateClassController::class, 'requests'])->name('coach.private-requests');
    Route::post('/coach/private-requests/{id}/accept', [PrivateClassController::class, 'acceptRequest'])->name('coach.private-request.accept');
    Route::post('/coach/private-requests/{id}/decline', [PrivateClassController::class, 'declineRequest'])->name('coach.private-request.decline');
});

// Auth protected routes (shared)
Route::middleware('auth')->group(function () {
    // Admin routes
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        // Overview (Home)
        Route::get('/', [AdminController::class, 'index'])->name('index');
        
        // Members
        Route::get('/members', [AdminController::class, 'members'])->name('members');
        Route::get('/members/create', [AdminController::class, 'createMember'])->name('members.create');
        Route::post('/members', [AdminController::class, 'storeMember'])->name('members.store');
        Route::get('/members/kids-mat-hours', [AdminController::class, 'kidsMatHours'])->name('members.kids-mat-hours');
        Route::put('/members/kids-mat-hours', [AdminController::class, 'updateKidsMatHours'])->name('members.kids-mat-hours.update');
        Route::get('/members/{id}', [AdminController::class, 'showMember'])->name('members.show');
        Route::put('/members/{id}', [AdminController::class, 'updateMember'])->name('members.update');
        Route::get('/members/{id}/family/search', [AdminController::class, 'searchFamilyMembers'])->name('members.family.search');
        Route::post('/members/{id}/family', [AdminController::class, 'addFamilyMember'])->name('members.family.add');
        Route::delete('/members/{id}/family/{userId}', [AdminController::class, 'removeFamilyMember'])->name('members.family.remove');
        Route::post('/members/{id}/membership', [AdminController::class, 'updateMembership'])->name('members.membership');
        Route::post('/members/{id}/avatar', [AdminController::class, 'updateMemberAvatar'])->name('members.avatar');
        Route::delete('/members/{id}', [AdminController::class, 'deleteMember'])->name('members.delete');
        
        // Classes Management
        Route::get('/classes', [AdminController::class, 'classes'])->name('classes');
        Route::get('/classes/create', [AdminController::class, 'createClass'])->name('classes.create');
        Route::post('/classes', [AdminController::class, 'storeClass'])->name('classes.store');
        Route::get('/classes/{id}/edit', [AdminController::class, 'editClass'])->name('classes.edit');
        Route::put('/classes/{id}', [AdminController::class, 'updateClass'])->name('classes.update');
        Route::delete('/classes/{id}', [AdminController::class, 'deleteClass'])->name('classes.delete');
        
        // Attendance
        Route::get('/attendance/{classId}', [AdminController::class, 'attendance'])->name('attendance');
        Route::post('/attendance/{classId}/toggle/{bookingId}', [AdminController::class, 'toggleCheckIn'])->name('attendance.toggle');
        Route::delete('/attendance/{classId}/booking/{bookingId}', [AdminController::class, 'removeBooking'])->name('attendance.booking.remove');
        Route::delete('/attendance/{classId}/trial/{trialId}', [AdminController::class, 'removeTrial'])->name('attendance.trial.remove');
        Route::post('/attendance/{classId}/walk-in', [AdminController::class, 'addWalkIn'])->name('attendance.walkin');
        Route::post('/classes/{classId}/trials', [AdminController::class, 'storeTrial'])->name('classes.trials.store');

        // Finance
        Route::get('/finance', [AdminController::class, 'finance'])->name('finance');
        
        // Payments (Settings)
        Route::get('/payments', [AdminController::class, 'payments'])->name('payments');
        Route::post('/payments/{id}/approve', [AdminController::class, 'approvePayment'])->name('payments.approve');
        Route::post('/payments/{id}/approve-with-membership', [AdminController::class, 'approvePaymentWithMembership'])->name('payments.approve.membership');
        Route::post('/payments/{id}/reject', [AdminController::class, 'rejectPayment'])->name('payments.reject');

        // Membership Packages
        Route::get('/packages', [MembershipPackageController::class, 'index'])->name('packages.index');
        Route::get('/packages/create', [MembershipPackageController::class, 'create'])->name('packages.create');
        Route::post('/packages', [MembershipPackageController::class, 'store'])->name('packages.store');
        Route::get('/packages/{id}/edit', [MembershipPackageController::class, 'edit'])->name('packages.edit');
        Route::put('/packages/{id}', [MembershipPackageController::class, 'update'])->name('packages.update');
        Route::delete('/packages/{id}', [MembershipPackageController::class, 'destroy'])->name('packages.destroy');
        Route::post('/packages/{id}/toggle', [MembershipPackageController::class, 'toggleStatus'])->name('packages.toggle');

        // Gym Shop (admin)
        Route::get('/shop/products', [ShopAdminController::class, 'products'])->name('shop.products');
        Route::get('/shop/products/create', [ShopAdminController::class, 'createProduct'])->name('shop.products.create');
        Route::post('/shop/products', [ShopAdminController::class, 'storeProduct'])->name('shop.products.store');
        Route::get('/shop/products/{product}/edit', [ShopAdminController::class, 'editProduct'])->name('shop.products.edit');
        Route::put('/shop/products/{product}', [ShopAdminController::class, 'updateProduct'])->name('shop.products.update');
        Route::post('/shop/products/{product}/copy', [ShopAdminController::class, 'copyProduct'])->name('shop.products.copy');
        Route::delete('/shop/products/{product}', [ShopAdminController::class, 'destroyProduct'])->name('shop.products.destroy');
        Route::get('/shop/stock', [ShopAdminController::class, 'stock'])->name('shop.stock');
        Route::post('/shop/stock/update', [ShopAdminController::class, 'updateStock'])->name('shop.stock.update');
        Route::get('/shop/orders', [ShopAdminController::class, 'orders'])->name('shop.orders');
        Route::post('/shop/orders/{order}/status', [ShopAdminController::class, 'updateOrderStatus'])->name('shop.orders.status');
        Route::delete('/shop/orders/{order}', [ShopAdminController::class, 'destroyOrder'])->name('shop.orders.destroy');
        Route::get('/shop/preorder', [ShopAdminController::class, 'preorderIndex'])->name('shop.preorder');
        Route::get('/shop/preorder/{product}', [ShopAdminController::class, 'preorderProduct'])->name('shop.preorder.product');
    });
});
