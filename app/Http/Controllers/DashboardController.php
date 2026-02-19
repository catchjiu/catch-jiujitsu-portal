<?php

namespace App\Http\Controllers;

use App\Models\ClassSession;
use App\Models\PrivateClassBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Support\SchemaCache;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if ($user->isInFamily()) {
            return redirect()->route('family.dashboard');
        }
        $user->load('membershipPackage'); // Load membership package relationship
        $hasClassesTables = SchemaCache::hasTable('classes') && SchemaCache::hasTable('bookings');
        $hasPrivateClasses = SchemaCache::hasTable('private_class_bookings');
        $hasShopTables = SchemaCache::hasTable('orders')
            && SchemaCache::hasTable('order_items')
            && SchemaCache::hasTable('product_variants')
            && SchemaCache::hasTable('products');

        $nextClass = null;
        $nextBooking = null;
        $classesThisMonth = 0;
        $previousClasses = collect();

        if ($hasClassesTables) {
            $nextClass = $user->nextBookedClass();
            if ($nextClass) {
                $nextBooking = $user->bookings()
                    ->where('class_id', $nextClass->id)
                    ->first();
            }

            $classesThisMonth = $user->bookings()
                ->whereHas('classSession', function ($query) {
                    $query->whereMonth('start_time', now()->month)
                        ->whereYear('start_time', now()->year);
                })
                ->count();

            $previousClasses = $user->bookedClasses()
                ->with('instructor')
                ->where('start_time', '<', now())
                ->orderBy('start_time', 'desc')
                ->take(5)
                ->get();
        }

        $pendingPrivateRequests = ($user->is_coach && $hasPrivateClasses)
            ? PrivateClassBooking::where('coach_id', $user->id)->where('status', 'pending')->count()
            : 0;

        $shopOrders = $hasShopTables
            ? $user->orders()
                ->with(['items.productVariant.product'])
                ->orderByDesc('created_at')
                ->take(10)
                ->get()
            : collect();

        return view('dashboard', [
            'user' => $user,
            'nextClass' => $nextClass,
            'nextBooking' => $nextBooking,
            'classesThisMonth' => $classesThisMonth,
            'previousClasses' => $previousClasses,
            'pendingPrivateRequests' => $pendingPrivateRequests,
            'shopOrders' => $shopOrders,
        ]);
    }
}
