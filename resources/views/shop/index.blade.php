@extends('layouts.app')

@section('content')
<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-2xl font-bold text-white uppercase tracking-wide" style="font-family: 'Bebas Neue', sans-serif;">
            {{ __('app.shop.title') }}
        </h2>
        <a href="{{ route('shop.my-orders') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-800/80 border border-slate-600 text-slate-300 hover:border-[#00d4ff]/40 hover:text-[#00d4ff] transition-colors text-sm font-medium">
            <span class="material-symbols-outlined text-lg">shopping_bag</span>
            {{ __('app.shop.view_your_orders') }}
        </a>
    </div>

    {{-- Search & category filter (mobile-first) --}}
    <form action="{{ route('shop.index') }}" method="GET" class="space-y-3">
        <div class="relative">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-xl">search</span>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="{{ __('app.shop.search_placeholder') }}"
                   class="w-full pl-10 pr-4 py-3 rounded-xl bg-slate-800/80 border border-slate-700 text-white placeholder-slate-500 focus:border-[#00d4ff]/60 focus:ring-1 focus:ring-[#00d4ff]/40 outline-none transition-colors">
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('shop.index', ['search' => request('search')]) }}"
               class="px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ !request('category') ? 'bg-[#00d4ff]/20 text-[#00d4ff] border border-[#00d4ff]/40' : 'bg-slate-800 text-slate-400 border border-slate-700 hover:border-slate-600' }}">
                {{ __('app.shop.all_categories') }}
            </a>
            @foreach($categories as $cat)
                <a href="{{ route('shop.index', ['category' => $cat, 'search' => request('search')]) }}"
                   class="px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request('category') === $cat ? 'bg-[#00d4ff]/20 text-[#00d4ff] border border-[#00d4ff]/40' : 'bg-slate-800 text-slate-400 border border-slate-700 hover:border-slate-600' }}">
                    {{ $cat }}
                </a>
            @endforeach
        </div>
        @if(request('search') || request('category'))
            <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-1 text-sm text-[#00d4ff] hover:underline">
                <span class="material-symbols-outlined text-lg">clear</span>
                {{ __('app.common.reset') }}
            </a>
        @endif
    </form>

    {{-- Product grid --}}
    <div class="grid grid-cols-2 gap-4 sm:gap-5">
        @forelse($products as $product)
            <div class="rounded-2xl bg-slate-800/60 border border-slate-700/50 overflow-hidden flex flex-col">
                <a href="#" class="block aspect-square bg-slate-800 relative overflow-hidden">
                    @if($product->image_url)
                        <img src="{{ $product->image_url }}" alt="{{ $product->localized_name }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-slate-600">
                            <span class="material-symbols-outlined text-5xl">inventory_2</span>
                        </div>
                    @endif
                </a>
                <div class="p-3 flex flex-col flex-1">
                    <p class="text-[10px] uppercase tracking-wider text-[#00d4ff]/90 font-medium">{{ $product->category }}</p>
                    <h3 class="text-white font-semibold text-sm mt-0.5 line-clamp-2">{{ $product->localized_name }}</h3>
                    <p class="text-[#00d4ff] font-bold mt-1">NT$ {{ number_format($product->price) }}</p>
                    @if($product->is_preorder)
                        <p class="text-amber-400/90 text-[10px] uppercase tracking-wider font-medium mt-0.5">{{ __('app.shop.preorder_badge') }}</p>
                    @endif
                    @php
                        $variants = $product->is_preorder ? $product->variants : $product->variants->where('stock_quantity', '>', 0);
                    @endphp
                    @if($variants->isEmpty())
                        <p class="text-slate-500 text-xs mt-2">{{ __('app.shop.out_of_stock') }}</p>
                    @else
                        @php
                            $weeks = $product->is_preorder ? (int) $product->preorder_weeks : null;
                            $preorderMessage = $weeks ? ($weeks === 1 ? __('app.shop.preorder_notice_week') : __('app.shop.preorder_notice_weeks', ['weeks' => $weeks])) : __('app.shop.preorder_notice');
                            $preorderCheckbox = $weeks ? ($weeks === 1 ? __('app.shop.preorder_confirm_checkbox_week') : __('app.shop.preorder_confirm_checkbox_weeks', ['weeks' => $weeks])) : __('app.shop.preorder_confirm_checkbox');
                        @endphp
                        <form action="{{ route('shop.quick-buy') }}" method="POST" class="mt-2 space-y-2 shop-quick-buy-form"
                              data-is-preorder="{{ $product->is_preorder ? '1' : '0' }}"
                              data-preorder-message="{{ $preorderMessage }}"
                              data-preorder-checkbox="{{ $preorderCheckbox }}"
                              data-preorder-submit="{{ __('app.shop.preorder_confirm_button') }}"
                              data-preorder-cancel="{{ __('app.common.cancel') }}">
                            @csrf
                            <select name="product_variant_id" required class="w-full rounded-lg bg-slate-900 border border-slate-600 text-white text-sm py-2 px-3 focus:border-[#00d4ff]/60 outline-none">
                                @foreach($variants as $v)
                                    <option value="{{ $v->id }}">{{ $v->size }}{{ $v->color ? ' · ' . $v->color : '' }}@if(!$product->is_preorder) ({{ $v->stock_quantity }})@endif</option>
                                @endforeach
                            </select>
                            <button type="submit" class="w-full py-2.5 rounded-xl bg-[#00d4ff]/20 text-[#00d4ff] border border-[#00d4ff]/40 font-semibold text-sm hover:bg-[#00d4ff]/30 transition-colors flex items-center justify-center gap-1">
                                <span class="material-symbols-outlined text-lg">shopping_cart</span>
                                {{ $product->is_preorder ? __('app.shop.preorder_button') : __('app.shop.order') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-2 py-12 text-center text-slate-500">
                <span class="material-symbols-outlined text-4xl mb-2 block">inventory_2</span>
                <p>{{ __('app.shop.all_categories') }} – {{ __('app.common.search') }} or change filter.</p>
            </div>
        @endforelse
    </div>
</div>

{{-- Pre-order confirmation modal --}}
<div id="preorderModal" class="fixed inset-0 z-50 hidden" aria-modal="true" role="dialog" aria-labelledby="preorder-modal-title">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" id="preorderModalBackdrop"></div>
    <div class="fixed left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md max-h-[90vh] overflow-y-auto mx-4 rounded-2xl bg-slate-800 border border-slate-600 shadow-2xl p-5">
        <h3 id="preorder-modal-title" class="text-lg font-semibold text-amber-400 mb-3">{{ __('app.shop.preorder_title') }}</h3>
        <p id="preorderModalMessage" class="text-slate-300 text-sm mb-4"></p>
        <label class="flex items-start gap-3 cursor-pointer mb-4 p-3 rounded-xl bg-slate-900/80 border border-slate-600">
            <input type="checkbox" id="preorderModalCheckbox" class="mt-1 w-4 h-4 rounded border-slate-600 bg-slate-800 text-[#00d4ff] focus:ring-[#00d4ff]/40">
            <span id="preorderModalCheckboxLabel" class="text-slate-300 text-sm"></span>
        </label>
        <div class="flex gap-3">
            <button type="button" id="preorderModalConfirm" class="flex-1 py-2.5 rounded-xl bg-[#00d4ff]/20 text-[#00d4ff] border border-[#00d4ff]/40 font-semibold text-sm hover:bg-[#00d4ff]/30 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                <span id="preorderModalConfirmText"></span>
            </button>
            <button type="button" id="preorderModalCancel" class="py-2.5 px-4 rounded-xl bg-slate-700 text-slate-300 font-medium text-sm hover:bg-slate-600 transition-colors"></button>
        </div>
    </div>
</div>

<script>
(function() {
    var modal = document.getElementById('preorderModal');
    var backdrop = document.getElementById('preorderModalBackdrop');
    var messageEl = document.getElementById('preorderModalMessage');
    var checkbox = document.getElementById('preorderModalCheckbox');
    var checkboxLabel = document.getElementById('preorderModalCheckboxLabel');
    var confirmBtn = document.getElementById('preorderModalConfirm');
    var confirmText = document.getElementById('preorderModalConfirmText');
    var cancelBtn = document.getElementById('preorderModalCancel');
    var pendingForm = null;

    function openPreorderModal(form) {
        pendingForm = form;
        messageEl.textContent = form.dataset.preorderMessage || '';
        checkboxLabel.textContent = form.dataset.preorderCheckbox || '';
        confirmText.textContent = form.dataset.preorderSubmit || 'Confirm';
        cancelBtn.textContent = form.dataset.preorderCancel || 'Cancel';
        checkbox.checked = false;
        confirmBtn.disabled = true;
        modal.classList.remove('hidden');
    }

    function closeModal() {
        modal.classList.add('hidden');
        pendingForm = null;
    }

    checkbox.addEventListener('change', function() {
        confirmBtn.disabled = !this.checked;
    });

    confirmBtn.addEventListener('click', function() {
        if (!checkbox.checked || !pendingForm) return;
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'confirm_preorder';
        input.value = '1';
        pendingForm.appendChild(input);
        pendingForm.submit();
    });

    cancelBtn.addEventListener('click', closeModal);
    backdrop.addEventListener('click', closeModal);

    document.querySelectorAll('.shop-quick-buy-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            if (form.dataset.isPreorder === '1') {
                e.preventDefault();
                openPreorderModal(form);
            }
        });
    });
})();
</script>
@endsection
