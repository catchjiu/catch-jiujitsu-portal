@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.shop.products') }}" class="text-slate-400 hover:text-white transition-colors p-1">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h1 class="text-2xl font-bold text-white uppercase tracking-wide" style="font-family: 'Bebas Neue', sans-serif;">
            {{ $product ? __('app.admin.edit_product') : __('app.admin.add_product') }}
        </h1>
    </div>

    <form action="{{ $product ? route('admin.shop.products.update', $product) : route('admin.shop.products.store') }}" method="POST" class="space-y-6">
        @csrf
        @if($product) @method('PUT') @endif

        {{-- Product details --}}
        <div class="rounded-xl bg-slate-800/60 border border-slate-700/50 p-4 space-y-4">
            <h2 class="text-[#00d4ff] font-semibold text-sm uppercase tracking-wider">{{ __('app.admin.product_details') }}</h2>
            <div>
                <label for="name" class="block text-slate-400 text-sm font-medium mb-1">{{ __('app.admin.product_name') }} *</label>
                <input type="text" name="name" id="name" value="{{ old('name', $product?->name) }}" required
                       class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-600 text-white placeholder-slate-500 focus:border-[#00d4ff]/60 focus:ring-1 focus:ring-[#00d4ff]/40 outline-none"
                       placeholder="e.g. Catch BJJ Gi">
                @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="category" class="block text-slate-400 text-sm font-medium mb-1">{{ __('app.shop.category') }} *</label>
                <select name="category" id="category" required
                        class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-600 text-white focus:border-[#00d4ff]/60 focus:ring-1 focus:ring-[#00d4ff]/40 outline-none">
                    <option value="">{{ __('app.admin.select_category') }}</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ old('category', $product?->category) === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
                @error('category')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="description" class="block text-slate-400 text-sm font-medium mb-1">{{ __('app.admin.description') }}</label>
                <textarea name="description" id="description" rows="3"
                          class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-600 text-white placeholder-slate-500 focus:border-[#00d4ff]/60 focus:ring-1 focus:ring-[#00d4ff]/40 outline-none resize-none"
                          placeholder="Short product description (optional)">{{ old('description', $product?->description) }}</textarea>
                @error('description')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="price" class="block text-slate-400 text-sm font-medium mb-1">{{ __('app.shop.price') }} (NT$) *</label>
                    <input type="number" name="price" id="price" value="{{ old('price', $product?->price) }}" min="0" step="1" required
                           class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-600 text-white focus:border-[#00d4ff]/60 focus:ring-1 focus:ring-[#00d4ff]/40 outline-none"
                           placeholder="0">
                    @error('price')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="image_url" class="block text-slate-400 text-sm font-medium mb-1">{{ __('app.admin.image_url') }}</label>
                    <input type="text" name="image_url" id="image_url" value="{{ old('image_url', $product?->getRawOriginal('image_url')) }}"
                           class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-600 text-white placeholder-slate-500 focus:border-[#00d4ff]/60 focus:ring-1 focus:ring-[#00d4ff]/40 outline-none"
                           placeholder="URL or path (optional)">
                    @error('image_url')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Sizes / Variants (BJJ: size, color, stock) --}}
        <div class="rounded-xl bg-slate-800/60 border border-slate-700/50 p-4 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-[#00d4ff] font-semibold text-sm uppercase tracking-wider">{{ __('app.admin.sizes_stock') }}</h2>
                <div class="flex gap-2">
                    <button type="button" id="quickAddSizes" class="text-xs px-3 py-1.5 rounded-lg bg-slate-700 text-slate-300 hover:bg-slate-600 transition-colors">
                        {{ __('app.admin.quick_add_sizes') }}
                    </button>
                    <button type="button" id="addVariant" class="text-xs px-3 py-1.5 rounded-lg bg-[#00d4ff]/20 text-[#00d4ff] border border-[#00d4ff]/40 hover:bg-[#00d4ff]/30 transition-colors">
                        + {{ __('app.admin.add_variant') }}
                    </button>
                </div>
            </div>
            <p class="text-slate-500 text-xs">{{ __('app.admin.variants_hint') }}</p>
            <div id="variantsContainer" class="space-y-3">
                @php
                    $oldVariants = old('variants');
                    $variants = $product ? $product->variants : collect();
                    if ($oldVariants && is_array($oldVariants)) {
                        $variants = collect($oldVariants)->map(fn($v) => (object)['size' => $v['size'] ?? '', 'color' => $v['color'] ?? '', 'stock_quantity' => $v['stock_quantity'] ?? 0]);
                    } elseif ($variants->isEmpty()) {
                        $variants = collect([(object)['size' => '', 'color' => '', 'stock_quantity' => 0]]);
                    }
                @endphp
                @foreach($variants as $index => $v)
                    <div class="variant-row flex flex-wrap gap-2 items-end p-3 rounded-lg bg-slate-900/60 border border-slate-700/50">
                        <div class="flex-1 min-w-[80px]">
                            <label class="block text-slate-500 text-xs mb-0.5">{{ __('app.shop.size') }}</label>
                            <input type="text" name="variants[{{ $index }}][size]" value="{{ $v->size ?? '' }}" placeholder="A1, M, L..."
                                   class="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-600 text-white text-sm outline-none focus:border-[#00d4ff]/60 variant-size">
                        </div>
                        <div class="flex-1 min-w-[80px]">
                            <label class="block text-slate-500 text-xs mb-0.5">{{ __('app.admin.color') }}</label>
                            <input type="text" name="variants[{{ $index }}][color]" value="{{ $v->color ?? '' }}" placeholder="Optional"
                                   class="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-600 text-white text-sm outline-none focus:border-[#00d4ff]/60">
                        </div>
                        <div class="w-20">
                            <label class="block text-slate-500 text-xs mb-0.5">{{ __('app.admin.stock_quantity') }}</label>
                            <input type="number" name="variants[{{ $index }}][stock_quantity]" value="{{ $v->stock_quantity ?? 0 }}" min="0"
                                   class="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-600 text-white text-sm outline-none focus:border-[#00d4ff]/60">
                        </div>
                        <button type="button" class="remove-variant p-2 rounded-lg text-slate-500 hover:bg-red-500/20 hover:text-red-400 transition-colors" title="{{ __('app.common.delete') }}">
                            <span class="material-symbols-outlined text-lg">close</span>
                        </button>
                    </div>
                @endforeach
            </div>
            @error('variants')<p class="text-red-400 text-xs">{{ $message }}</p>@enderror
        </div>

        <div class="flex gap-3">
            <button type="submit" class="flex-1 py-3 rounded-xl bg-[#00d4ff]/20 text-[#00d4ff] border border-[#00d4ff]/40 font-semibold hover:bg-[#00d4ff]/30 transition-colors">
                {{ $product ? __('app.admin.save_changes') : __('app.admin.save_product') }}
            </button>
            <a href="{{ route('admin.shop.products') }}" class="py-3 px-4 rounded-xl bg-slate-700 text-slate-300 font-medium hover:bg-slate-600 transition-colors">
                {{ __('app.common.cancel') }}
            </a>
        </div>
    </form>
</div>

<script>
(function() {
    var container = document.getElementById('variantsContainer');
    var addBtn = document.getElementById('addVariant');
    var quickAddBtn = document.getElementById('quickAddSizes');
    var categorySelect = document.getElementById('category');

    var sizePresets = {
        'Gi': ['A0', 'A1', 'A2', 'A3', 'A4', 'M', 'L'],
        'Belt': ['1', '2', '3', '4', '5'],
        'Rash guard': ['XS', 'S', 'M', 'L', 'XL'],
        'Shorts': ['S', 'M', 'L', 'XL'],
        'T-shirt': ['XS', 'S', 'M', 'L', 'XL'],
        'Sticker': ['One size']
    };

    function nextIndex() {
        var inputs = container.querySelectorAll('input[name^="variants["]');
        var max = -1;
        inputs.forEach(function(inp) {
            var m = inp.name.match(/variants\[(\d+)\]/);
            if (m) max = Math.max(max, parseInt(m[1], 10));
        });
        return max + 1;
    }

    function addRow(size, color, stock) {
        var i = nextIndex();
        stock = stock !== undefined ? stock : 0;
        var div = document.createElement('div');
        div.className = 'variant-row flex flex-wrap gap-2 items-end p-3 rounded-lg bg-slate-900/60 border border-slate-700/50';
        div.innerHTML = '<div class="flex-1 min-w-[80px]"><label class="block text-slate-500 text-xs mb-0.5">Size</label><input type="text" name="variants[' + i + '][size]" value="' + (size || '') + '" placeholder="A1, M, L..." class="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-600 text-white text-sm outline-none focus:border-[#00d4ff]/60 variant-size"></div>' +
            '<div class="flex-1 min-w-[80px]"><label class="block text-slate-500 text-xs mb-0.5">Color</label><input type="text" name="variants[' + i + '][color]" value="' + (color || '') + '" placeholder="Optional" class="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-600 text-white text-sm outline-none focus:border-[#00d4ff]/60"></div>' +
            '<div class="w-20"><label class="block text-slate-500 text-xs mb-0.5">Stock</label><input type="number" name="variants[' + i + '][stock_quantity]" value="' + stock + '" min="0" class="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-600 text-white text-sm outline-none focus:border-[#00d4ff]/60"></div>' +
            '<button type="button" class="remove-variant p-2 rounded-lg text-slate-500 hover:bg-red-500/20 hover:text-red-400 transition-colors" title="Remove"><span class="material-symbols-outlined text-lg">close</span></button>';
        container.appendChild(div);
        div.querySelector('.remove-variant').addEventListener('click', function() {
            if (container.querySelectorAll('.variant-row').length > 1) div.remove();
        });
    }

    addBtn.addEventListener('click', function() { addRow('', '', 0); });

    quickAddBtn.addEventListener('click', function() {
        var cat = categorySelect.value;
        var sizes = sizePresets[cat] || ['S', 'M', 'L'];
        sizes.forEach(function(s) { addRow(s, '', 0); });
    });

    container.querySelectorAll('.remove-variant').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var row = btn.closest('.variant-row');
            if (container.querySelectorAll('.variant-row').length > 1) row.remove();
        });
    });
})();
</script>
@endsection
