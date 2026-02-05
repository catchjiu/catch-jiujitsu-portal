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
     */
    public function quickBuy(Request $request)
    {
        $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'nullable|integer|min:1|max:99',
        ]);

        $variant = ProductVariant::with('product')->findOrFail($request->product_variant_id);
        $quantity = (int) ($request->quantity ?? 1);

        if ($variant->stock_quantity < $quantity) {
            return back()->with('error', __('app.shop.out_of_stock'));
        }

        $user = auth()->user();
        $unitPrice = $variant->product->price;
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
        ]);

        $variant->decrement('stock_quantity', $quantity);

        return redirect()->route('shop.confirmation', $order)
            ->with('success', __('app.shop.order_placed'));
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
