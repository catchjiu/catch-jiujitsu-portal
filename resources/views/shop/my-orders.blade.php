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
            <div class="px-4 pb-4 flex justify-between items-center border-t border-slate-700/50 pt-3">
                <span class="text-slate-500 text-sm">{{ __('app.shop.total') }}</span>
                <span class="text-[#00d4ff] font-bold">NT$ {{ number_format($order->total_price) }}</span>
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
</div>
@endsection
