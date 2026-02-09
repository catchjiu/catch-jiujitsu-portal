<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    /**
     * Member storefront: grid of products with search by category.
     */
    public function index(Request $request)
    {
        $query = Product::with('variants')->orderBy('name');

        $category = $request->get('category');
        if ($category && in_array($category, Product::categories(), true)) {
            $query->where('category', $category);
        }

        $search = $request->get('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('category', 'like', '%' . $search . '%');
            });
        }

        $products = $query->get();

        return view('shop.index', [
            'products' => $products,
            'categories' => Product::categories(),
            'selectedCategory' => $category,
            'search' => $search,
        ]);
    }

    /**
     * Quick Buy: place order for one variant, then show confirmation.
     * For pre-order products, confirm_preorder must be present; stock is not decremented.
     */
    public function quickBuy(Request $request)
    {
        $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'nullable|integer|min:1|max:99',
            'confirm_preorder' => 'nullable|in:1',
        ]);

        $variant = ProductVariant::with('product')->findOrFail($request->product_variant_id);
        $product = $variant->product;
        $quantity = (int) ($request->quantity ?? 1);
        $isPreorder = (bool) $product->is_preorder;

        if ($isPreorder && !$request->filled('confirm_preorder')) {
            return back()->with('error', __('app.shop.preorder_confirm_required'));
        }

        if (!$isPreorder && $variant->stock_quantity < $quantity) {
            return back()->with('error', __('app.shop.out_of_stock'));
        }

        $user = auth()->user();
        $unitPrice = $product->price;
        $totalPrice = $unitPrice * $quantity;

        $order = Order::create([
            'user_id' => $user->id,
            'total_price' => $totalPrice,
            'status' => Order::STATUS_PENDING,
            'notes' => null,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_variant_id' => $variant->id,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'is_preorder' => $isPreorder,
        ]);

        if (!$isPreorder) {
            $variant->decrement('stock_quantity', $quantity);
        }

        return redirect()->route('shop.confirmation', $order)
            ->with('success', $isPreorder ? __('app.shop.preorder_placed') : __('app.shop.order_placed'));
    }

    /**
     * Member: list my orders with items and expected delivery (for pre-orders).
     */
    public function myOrders()
    {
        $orders = Order::with(['items.productVariant.product'])
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->get();

        foreach ($orders as $order) {
            $maxWeeks = 0;
            foreach ($order->items as $item) {
                if ($item->is_preorder && $item->productVariant && $item->productVariant->product && $item->productVariant->product->preorder_weeks) {
                    $maxWeeks = max($maxWeeks, (int) $item->productVariant->product->preorder_weeks);
                }
            }
            $order->expected_delivery = $maxWeeks > 0
                ? $order->created_at->copy()->addWeeks($maxWeeks)
                : null;
            $order->has_preorder = $order->items->contains('is_preorder', true);
        }

        $pendingOrders = $orders->where('status', Order::STATUS_PENDING);
        $grandTotal = $pendingOrders->sum('total_price');

        return view('shop.my-orders', [
            'orders' => $orders,
            'grandTotal' => $grandTotal,
            'pendingOrders' => $pendingOrders,
        ]);
    }

    /**
     * Member: submit bank transfer payment for pending orders (from My Orders page).
     */
    public function submitOrderPayment(Request $request)
    {
        $request->validate([
            'account_last_5' => ['required', 'string', 'size:5', 'regex:/^[0-9]+$/'],
            'payment_date' => ['required', 'date'],
        ]);

        $pending = Order::where('user_id', auth()->id())
            ->where('status', Order::STATUS_PENDING)
            ->get();

        if ($pending->isEmpty()) {
            return redirect()->route('shop.my-orders')
                ->with('error', __('app.shop.no_pending_orders'));
        }

        foreach ($pending as $order) {
            $order->update([
                'payment_method' => 'bank',
                'account_last_5' => $request->account_last_5,
                'payment_submitted_at' => now(),
            ]);
        }

        return redirect()->route('shop.my-orders')
            ->with('success', __('app.shop.payment_submitted'));
    }

    /**
     * Member: cancel own order only when it is Pending.
     */
    public function cancelOrder(Request $request, Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }
        if ($order->status !== Order::STATUS_PENDING) {
            return redirect()->route('shop.my-orders')
                ->with('error', __('app.shop.cancel_not_allowed'));
        }
        $order->update(['status' => Order::STATUS_CANCELLED]);
        return redirect()->route('shop.my-orders')
            ->with('success', __('app.shop.order_cancelled'));
    }

    /**
     * Checkout confirmation: show order and member's chinese_name.
     */
    public function confirmation(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load(['items.productVariant.product', 'user']);

        return view('shop.confirmation', [
            'order' => $order,
        ]);
    }
}
