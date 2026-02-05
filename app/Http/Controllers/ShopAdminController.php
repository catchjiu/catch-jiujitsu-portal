<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ShopAdminController extends Controller
{
    /**
     * Stock Manager: list all variants with +/- buttons; low stock highlighted.
     */
    public function stock(Request $request)
    {
        $products = Product::with('variants')
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return view('admin.shop-stock', [
            'products' => $products,
            'lowStockThreshold' => 3,
        ]);
    }

    /**
     * Update variant stock via AJAX (no page reload).
     */
    public function updateStock(Request $request): JsonResponse
    {
        $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'delta' => 'required|integer|in:-1,1',
        ]);

        $variant = ProductVariant::findOrFail($request->variant_id);
        $delta = (int) $request->delta;
        $newQty = $variant->stock_quantity + $delta;

        if ($newQty < 0) {
            return response()->json([
                'ok' => false,
                'message' => __('app.shop.stock_cannot_negative'),
                'stock_quantity' => $variant->stock_quantity,
            ], 422);
        }

        $variant->stock_quantity = $newQty;
        $variant->save();

        return response()->json([
            'ok' => true,
            'stock_quantity' => $variant->stock_quantity,
            'is_low_stock' => $variant->isLowStock(3),
        ]);
    }

    /**
     * Order Tracker: list member orders; mark as Processing or Delivered.
     */
    public function orders(Request $request)
    {
        $orders = Order::with(['user', 'items.productVariant.product'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.shop-orders', [
            'orders' => $orders,
        ]);
    }

    /**
     * Update order status (Processing / Delivered).
     */
    public function updateOrderStatus(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:Processing,Delivered',
        ]);

        $order->status = $request->status;
        $order->save();

        return response()->json([
            'ok' => true,
            'status' => $order->status,
        ]);
    }

    /**
     * Admin: list products for CRUD (optional future use). For now we only have Stock + Orders.
     */
    public function products()
    {
        $products = Product::withCount('variants')->with('variants')->orderBy('category')->orderBy('name')->get();
        return view('admin.shop-products', ['products' => $products]);
    }
}
