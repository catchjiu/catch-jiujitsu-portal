@extends('layouts.app')

@section('content')
<div class="space-y-6 max-w-md mx-auto">
    <div class="text-center">
        <div class="w-16 h-16 rounded-full bg-[#00d4ff]/20 border-2 border-[#00d4ff]/50 flex items-center justify-center mx-auto mb-4">
            <span class="material-symbols-outlined text-[#00d4ff] text-3xl">check_circle</span>
        </div>
        <h2 class="text-xl font-bold text-white uppercase tracking-wide" style="font-family: 'Bebas Neue', sans-serif;">
            {{ __('app.shop.confirmation') }}
        </h2>
        <p class="text-slate-400 text-sm mt-1">
            @if($order->items->contains('is_preorder', true))
                {{ __('app.shop.preorder_placed') }}
            @else
                {{ __('app.shop.order_placed') }}
            @endif
        </p>
    </div>

    {{-- Member name (chinese_name on confirmation) --}}
    <div class="rounded-xl bg-slate-800/60 border border-[#00d4ff]/30 p-4">
        <p class="text-[10px] uppercase tracking-wider text-slate-500 font-medium">{{ __('app.shop.order_for') }}</p>
        <p class="text-[#00d4ff] font-bold text-lg mt-1">
            {{ $order->user->chinese_name ?: $order->user->name }}
        </p>
    </div>

    {{-- Order details --}}
    <div class="rounded-xl bg-slate-800/60 border border-slate-700/50 p-4 space-y-3">
        <p class="text-slate-500 text-xs">Order #{{ $order->id }}</p>
        @foreach($order->items as $item)
            @php $v = $item->productVariant; $p = $v->product; @endphp
            <div class="flex justify-between items-start gap-2">
                <div>
                    <p class="text-white font-medium">{{ $p->localized_name }}@if($item->is_preorder) <span class="text-amber-400 text-[10px] uppercase tracking-wider">({{ __('app.shop.preorder_badge') }})</span>@endif</p>
                    <p class="text-slate-400 text-sm">{{ $v->size }}{{ $v->color ? ' · ' . $v->color : '' }} × {{ $item->quantity }}</p>
                </div>
                <p class="text-[#00d4ff] font-semibold">NT$ {{ number_format($item->unit_price * $item->quantity) }}</p>
            </div>
        @endforeach
        <div class="border-t border-slate-700 pt-3 flex justify-between items-center">
            <span class="font-semibold text-white">{{ __('app.shop.total') }}</span>
            <span class="text-[#00d4ff] font-bold text-lg">NT$ {{ number_format($order->total_price) }}</span>
        </div>
    </div>

    <div class="flex flex-col gap-3">
        @if($order->status === 'Pending')
            <a href="{{ route('shop.my-orders') }}" class="block w-full py-3 rounded-xl bg-[#00d4ff] text-slate-950 font-bold text-center hover:bg-[#00d4ff]/90 transition-colors">
                {{ __('app.shop.pay_now') }}
            </a>
            <p class="text-slate-500 text-xs text-center">{{ __('app.shop.pay_now_hint') }}</p>
        @endif
        <a href="{{ route('shop.index') }}" class="block w-full py-3 rounded-xl bg-[#00d4ff]/20 text-[#00d4ff] border border-[#00d4ff]/40 font-semibold text-center hover:bg-[#00d4ff]/30 transition-colors">
            {{ __('app.shop.back_to_shop') }}
        </a>
    </div>
</div>
@endsection
