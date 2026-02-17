@extends('layouts.app')

@section('content')
<div class="space-y-6 max-w-lg mx-auto">
    <div class="flex items-center justify-between gap-3">
        <a href="{{ route('shop.index') }}" class="text-slate-400 hover:text-white transition-colors p-1">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h2 class="text-2xl font-bold text-white uppercase tracking-wide" style="font-family: 'Bebas Neue', sans-serif;">
            {{ __('app.shop.my_orders') }}
        </h2>
        <span class="w-8"></span>
    </div>
    <p class="text-slate-400 text-sm">{{ __('app.shop.my_orders_intro') }}</p>

    @if(session('success'))
        <div class="p-3 rounded-xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="p-3 rounded-xl bg-red-500/20 border border-red-500/40 text-red-400 text-sm">{{ session('error') }}</div>
    @endif

    @forelse($orders as $order)
        <div class="rounded-xl bg-slate-800/60 border border-slate-700/50 overflow-hidden">
            <div class="p-4 border-b border-slate-700/50 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <p class="text-white font-semibold">#{{ $order->id }}</p>
                    <p class="text-slate-500 text-xs mt-0.5">{{ $order->created_at->format('M j, Y g:i A') }}</p>
                </div>
                <div class="text-right">
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-medium
                        @if($order->status === 'Pending') bg-amber-500/20 text-amber-400 border border-amber-500/40
                        @elseif($order->status === 'Processing') bg-[#00d4ff]/20 text-[#00d4ff] border border-[#00d4ff]/40
                        @elseif($order->status === 'Cancelled') bg-slate-500/20 text-slate-400 border border-slate-500/40
                        @else bg-emerald-500/20 text-emerald-400 border border-emerald-500/40
                        @endif">{{ __('app.shop.status_' . strtolower($order->status)) }}</span>
                    @if($order->expected_delivery)
                        <p class="text-amber-400/90 text-xs mt-2 font-medium">{{ __('app.shop.expected_delivery') }}: {{ $order->expected_delivery->format('M j, Y') }}</p>
                    @endif
                </div>
            </div>
            <ul class="p-4 space-y-2">
                @foreach($order->items as $item)
                    @php $v = $item->productVariant; $p = $v->product; @endphp
                    <li class="flex justify-between items-start gap-2 text-sm">
                        <div>
                            <p class="text-white">{{ $p->localized_name }}@if($item->is_preorder) <span class="text-amber-400 text-[10px] uppercase">({{ __('app.shop.preorder_badge') }})</span>@endif</p>
                            <p class="text-slate-500">{{ $v->size }}{{ $v->color ? ' · ' . $v->color : '' }} × {{ $item->quantity }}</p>
                        </div>
                        <p class="text-[#00d4ff] font-semibold">NT$ {{ number_format($item->unit_price * $item->quantity) }}</p>
                    </li>
                @endforeach
            </ul>
            <div class="px-4 pb-4 flex flex-wrap justify-between items-center gap-3 border-t border-slate-700/50 pt-3">
                <div class="flex items-center gap-3">
                    <span class="text-slate-500 text-sm">{{ __('app.shop.total') }}</span>
                    <span class="text-[#00d4ff] font-bold">NT$ {{ number_format($order->total_price) }}</span>
                </div>
                @if($order->status === 'Pending')
                    <form action="{{ route('shop.orders.cancel', $order) }}" method="POST" class="inline" onsubmit="return confirm('{{ addslashes(__('app.shop.cancel_confirm')) }}');">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 rounded-lg border border-red-500/50 text-red-400 text-xs font-medium hover:bg-red-500/20 transition-colors">
                            {{ __('app.shop.cancel_order') }}
                        </button>
                    </form>
                @endif
            </div>
        </div>
    @empty
        <div class="py-12 text-center text-slate-500 rounded-xl bg-slate-800/40 border border-slate-700/50">
            <span class="material-symbols-outlined text-4xl mb-2 block">shopping_bag</span>
            <p>{{ __('app.shop.no_orders') }}</p>
            <a href="{{ route('shop.index') }}" class="inline-block mt-4 px-4 py-2 rounded-xl bg-[#00d4ff]/20 text-[#00d4ff] border border-[#00d4ff]/40 text-sm font-medium hover:bg-[#00d4ff]/30 transition-colors">
                {{ __('app.shop.back_to_shop') }}
            </a>
        </div>
    @endforelse

    @if($pendingOrders->isNotEmpty())
        <div class="rounded-xl bg-slate-800/60 border border-slate-700/50 overflow-hidden">
            <div class="p-4 border-b border-slate-700/50">
                <p class="text-slate-400 text-xs uppercase tracking-wider mb-1">{{ __('app.shop.grand_total') }}</p>
                <p class="text-2xl font-bold text-[#00d4ff]" style="font-family: 'Bebas Neue', sans-serif;">NT$ {{ number_format($grandTotal) }}</p>
            </div>
            <div class="p-4">
                <h3 class="text-sm font-bold text-white mb-3">{{ __('app.shop.pay_via_bank_transfer') }}</h3>
                <form action="{{ route('shop.orders.submit-payment') }}" method="POST"
                      x-data="{ paymentDate: '{{ now()->format('Y-m-d') }}', accountLast5: '' }">
                    @csrf
                    <input type="hidden" name="payment_method" value="bank">
                    <div class="mb-3">
                        <label class="block text-xs text-slate-400 uppercase tracking-wider mb-1">{{ __('app.payments.payment_date') }}</label>
                        <input type="date" name="payment_date" x-model="paymentDate" required
                               class="w-full bg-slate-900 border border-slate-600 rounded px-3 py-2 text-white text-sm focus:outline-none focus:border-[#00d4ff]">
                    </div>
                    <div class="mb-3">
                        <label class="block text-xs text-slate-400 uppercase tracking-wider mb-1">{{ __('app.payments.amount') }} (NT$)</label>
                        <input type="text" readonly value="{{ number_format($grandTotal) }}"
                               class="w-full bg-slate-900/80 border border-slate-600 rounded px-3 py-2 text-[#00d4ff] font-semibold text-sm cursor-default">
                    </div>
                    <div class="mb-4">
                        <label class="block text-xs text-slate-400 uppercase tracking-wider mb-1">{{ __('app.payments.last_5_digits') }}</label>
                        <input type="text" name="account_last_5" x-model="accountLast5" maxlength="5" inputmode="numeric" pattern="[0-9]*"
                               placeholder="{{ app()->getLocale() === 'zh-TW' ? '例如 12345' : 'e.g. 12345' }}" required
                               class="w-full bg-slate-900 border border-slate-600 rounded px-3 py-2 text-white text-sm focus:outline-none focus:border-[#00d4ff] font-mono tracking-widest text-center">
                    </div>
                    <button type="submit"
                            class="w-full py-3 bg-[#00d4ff]/20 hover:bg-[#00d4ff]/30 text-[#00d4ff] font-bold uppercase text-xs tracking-wider rounded border border-[#00d4ff]/40 transition-colors flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="!paymentDate || accountLast5.length !== 5">
                        <span class="material-symbols-outlined text-sm">send</span>
                        {{ __('app.payments.submit') }}
                    </button>
                </form>
            </div>
        </div>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @endif
</div>
@endsection
