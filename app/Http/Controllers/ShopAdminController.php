<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Intervention\Image\Laravel\Facades\Image;

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
     * Admin: list products with Add product button.
     */
    public function products()
    {
        $products = Product::withCount('variants')->with('variants')
            ->orderBy('category')
            ->orderBy('name')
            ->get();
        return view('admin.shop-products', ['products' => $products]);
    }

    /**
     * Show form to add a new product (with variants).
     */
    public function createProduct()
    {
        return view('admin.shop-product-form', [
            'product' => null,
            'categories' => Product::categories(),
        ]);
    }

    /**
     * Store a new product and its variants.
     */
    public function storeProduct(Request $request)
    {
        $validated = $this->validateProduct($request);
        $imagePath = $this->handleProductImage($request, null);
        $product = Product::create([
            'name' => $validated['name'],
            'product_name_zh' => $validated['product_name_zh'] ?? null,
            'category' => $validated['category'],
            'description' => $validated['description'] ?? null,
            'product_desc_zh' => $validated['product_desc_zh'] ?? null,
            'price' => $validated['price'],
            'image_url' => $imagePath ?? $validated['image_url'] ?? null,
        ]);
        $this->syncVariants($product, $validated['variants'] ?? []);
        return redirect()->route('admin.shop.products')->with('success', __('app.admin.product_added'));
    }

    /**
     * Show form to edit a product.
     */
    public function editProduct(Product $product)
    {
        $product->load('variants');
        return view('admin.shop-product-form', [
            'product' => $product,
            'categories' => Product::categories(),
        ]);
    }

    /**
     * Update a product and its variants.
     */
    public function updateProduct(Request $request, Product $product)
    {
        $validated = $this->validateProduct($request, $product);
        $imagePath = $this->handleProductImage($request, $product);
        $product->update([
            'name' => $validated['name'],
            'product_name_zh' => $validated['product_name_zh'] ?? null,
            'category' => $validated['category'],
            'description' => $validated['description'] ?? null,
            'product_desc_zh' => $validated['product_desc_zh'] ?? null,
            'price' => $validated['price'],
            'image_url' => $imagePath ?? $validated['image_url'] ?? $product->getRawOriginal('image_url'),
        ]);
        $this->syncVariants($product, $validated['variants'] ?? []);
        return redirect()->route('admin.shop.products')->with('success', __('app.admin.product_updated'));
    }

    /**
     * Delete a product (and its variants via cascade).
     */
    public function destroyProduct(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.shop.products')->with('success', __('app.admin.product_deleted'));
    }

    private function validateProduct(Request $request, ?Product $product = null): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'product_name_zh' => 'nullable|string|max:255',
            'category' => ['required', 'string', Rule::in(Product::categories())],
            'description' => 'nullable|string|max:2000',
            'product_desc_zh' => 'nullable|string|max:2000',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,webp|max:5120',
            'image_url' => 'nullable|string|max:500',
            'variants' => 'required|array|min:1',
            'variants.*.size' => 'required|string|max:30',
            'variants.*.color' => 'nullable|string|max:60',
            'variants.*.stock_quantity' => 'required|integer|min:0',
        ];
        return $request->validate($rules);
    }

    /**
     * Handle product image upload: store and resize to 3:2 (e.g. 900×600).
     * Returns relative path for storage (e.g. products/xxx.jpg) or null if no new file.
     */
    private function handleProductImage(Request $request, ?Product $product): ?string
    {
        if (! $request->hasFile('image') || ! $request->file('image')->isValid()) {
            return null;
        }
        $file = $request->file('image');
        $targetW = 900;
        $targetH = 600; // 3:2
        $dir = 'products';
        $filename = uniqid('product_', true) . '.' . $file->getClientOriginalExtension();
        $path = $dir . '/' . $filename;
        $fullPath = Storage::disk('public')->path($path);
        Storage::disk('public')->makeDirectory($dir);
        try {
            $image = Image::read($file->getRealPath());
            $image->cover($targetW, $targetH);
            $image->save($fullPath);
        } catch (\Throwable $e) {
            $file->storeAs($dir, $filename, 'public');
        }
        if ($product && $product->getRawOriginal('image_url')) {
            $old = $product->getRawOriginal('image_url');
            if ($old && ! str_starts_with($old, 'http')) {
                Storage::disk('public')->delete($old);
            }
        }
        return $path;
    }

    private function syncVariants(Product $product, array $variants): void
    {
        $product->variants()->delete();
        foreach ($variants as $v) {
            if (trim((string) ($v['size'] ?? '')) === '') {
                continue;
            }
            $product->variants()->create([
                'size' => trim($v['size']),
                'color' => isset($v['color']) && trim((string) $v['color']) !== '' ? trim($v['color']) : null,
                'stock_quantity' => (int) ($v['stock_quantity'] ?? 0),
            ]);
        }
    }
}
