@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-3">
        <button type="button" onclick="openMenu()" class="text-slate-400 hover:text-white transition-colors p-1">
            <span class="material-symbols-outlined">menu</span>
        </button>
        <h1 class="text-2xl font-bold text-white uppercase tracking-wide" style="font-family: 'Bebas Neue', sans-serif;">
            {{ __('app.admin.stock_manager') }}
        </h1>
    </div>

    <p class="text-slate-400 text-sm">
        {{ __('app.admin.low_stock') }}: &lt; {{ $lowStockThreshold }} → <span class="text-red-400 font-medium">highlighted in red</span>. Use +/- to update without reload.
    </p>

    <div class="space-y-6">
        @foreach($products as $product)
            <div class="rounded-xl bg-slate-800/60 border border-slate-700/50 overflow-hidden">
                <div class="p-4 border-b border-slate-700/50 flex items-center gap-3">
                    @if($product->image_url)
                        <img src="{{ $product->image_url }}" alt="" class="w-12 h-12 rounded-lg object-cover">
                    @else
                        <div class="w-12 h-12 rounded-lg bg-slate-700 flex items-center justify-center text-slate-500">
                            <span class="material-symbols-outlined">inventory_2</span>
                        </div>
                    @endif
                    <div>
                        <p class="text-white font-semibold">{{ $product->name }}</p>
                        <p class="text-slate-500 text-sm">{{ $product->category }} · NT$ {{ number_format($product->price) }}</p>
                    </div>
                </div>
                <ul class="divide-y divide-slate-700/50">
                    @foreach($product->variants as $variant)
                        @php $isLow = $variant->isLowStock($lowStockThreshold); @endphp
                        <li class="flex items-center justify-between gap-4 p-4 {{ $isLow ? 'bg-red-500/10 border-l-4 border-red-500' : '' }}" data-variant-row data-variant-id="{{ $variant->id }}">
                            <div class="min-w-0">
                                <p class="text-white font-medium">{{ $variant->size }}{{ $variant->color ? ' · ' . $variant->color : '' }}</p>
                                @if($isLow)
                                    <p class="text-red-400 text-xs font-medium">{{ __('app.admin.low_stock') }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <button type="button" data-stock-minus class="w-10 h-10 rounded-lg bg-slate-700 text-slate-300 hover:bg-slate-600 hover:text-white flex items-center justify-center transition-colors disabled:opacity-50 disabled:pointer-events-none" aria-label="Decrease stock">
                                    <span class="material-symbols-outlined text-xl">remove</span>
                                </button>
                                <span data-stock-value class="min-w-[2.5rem] text-center font-bold text-lg {{ $isLow ? 'text-red-400' : 'text-[#00d4ff]' }}">{{ $variant->stock_quantity }}</span>
                                <button type="button" data-stock-plus class="w-10 h-10 rounded-lg bg-slate-700 text-slate-300 hover:bg-slate-600 hover:text-white flex items-center justify-center transition-colors" aria-label="Increase stock">
                                    <span class="material-symbols-outlined text-xl">add</span>
                                </button>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>

    @if($products->isEmpty())
        <div class="py-12 text-center text-slate-500">
            <span class="material-symbols-outlined text-4xl mb-2 block">inventory_2</span>
            <p>No products yet. Add products and variants via the database or seeder.</p>
        </div>
    @endif
</div>

<script>
(function() {
    var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value;
    var url = '{{ route("admin.shop.stock.update") }}';

    document.querySelectorAll('[data-variant-row]').forEach(function(row) {
        var variantId = row.getAttribute('data-variant-id');
        var valueEl = row.querySelector('[data-stock-value]');
        var minusBtn = row.querySelector('[data-stock-minus]');
        var plusBtn = row.querySelector('[data-stock-plus]');

        function updateUi(qty, isLow) {
            valueEl.textContent = qty;
            valueEl.classList.toggle('text-red-400', isLow);
            valueEl.classList.toggle('text-[#00d4ff]', !isLow);
            row.classList.toggle('bg-red-500/10', isLow);
            row.classList.toggle('border-l-4', isLow);
            row.classList.toggle('border-red-500', isLow);
            minusBtn.disabled = qty <= 0;
        }

        function sendDelta(delta) {
            var body = new FormData();
            body.append('_token', csrf);
            body.append('variant_id', variantId);
            body.append('delta', delta);

            fetch(url, { method: 'POST', body: body, headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.ok) {
                        updateUi(data.stock_quantity, data.is_low_stock);
                    } else {
                        alert(data.message || 'Error');
                    }
                })
                .catch(function() { alert('Network error'); });
        }

        minusBtn.addEventListener('click', function() { sendDelta(-1); });
        plusBtn.addEventListener('click', function() { sendDelta(1); });
        updateUi(parseInt(valueEl.textContent, 10), row.classList.contains('bg-red-500/10'));
    });
})();
</script>
@endsection
