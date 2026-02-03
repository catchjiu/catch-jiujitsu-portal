<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\GoalsController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MembershipPackageController;
use App\Http\Controllers\CheckInController;
use Illuminate\Support\Facades\Route;

// Public routes

// Check-in kiosk (no auth – open in new tab for monitor)
Route::get('/checkin', [CheckInController::class, 'show'])->name('checkin');
Route::get('/api/checkin', [CheckInController::class, 'lookup'])->name('checkin.lookup');
Route::get('/', function () {
    if (auth()->check()) {
        if (auth()->user()->isAdmin()) {
            return redirect('/admin');
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

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Member routes (redirect admins to admin dashboard)
Route::middleware(['auth', 'member'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Schedule & Bookings
    Route::get('/schedule', [BookingController::class, 'index'])->name('schedule');
    Route::post('/book', [BookingController::class, 'store'])->name('book.store');
    Route::post('/check-in/today', [BookingController::class, 'checkInToday'])->name('checkin.today');
    Route::delete('/book/{classId}', [BookingController::class, 'destroy'])->name('book.destroy');
    Route::get('/class/{classId}/attendance', [BookingController::class, 'showAttendance'])->name('class.attendance');

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

    // Leaderboard
    Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard');
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
        Route::get('/members/{id}', [AdminController::class, 'showMember'])->name('members.show');
        Route::put('/members/{id}', [AdminController::class, 'updateMember'])->name('members.update');
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
    });
});
