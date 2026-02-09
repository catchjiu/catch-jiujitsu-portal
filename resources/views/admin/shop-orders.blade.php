@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-3">
        <button type="button" onclick="openMenu()" class="text-slate-400 hover:text-white transition-colors p-1">
            <span class="material-symbols-outlined">menu</span>
        </button>
        <h1 class="text-2xl font-bold text-white uppercase tracking-wide" style="font-family: 'Bebas Neue', sans-serif;">
            {{ __('app.admin.order_tracker') }}
        </h1>
    </div>
    <p class="text-slate-400 text-sm">{{ __('app.admin.member_orders') }}</p>

    @if(session('success'))
        <p class="rounded-lg bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 px-4 py-2 text-sm">{{ session('success') }}</p>
    @endif

    <div class="space-y-4">
        @forelse($orders as $order)
            <div class="rounded-xl bg-slate-800/60 border border-slate-700/50 overflow-hidden" data-order-id="{{ $order->id }}">
                <div class="p-4 border-b border-slate-700/50 space-y-3">
                    {{-- Top row: order number, name, date across --}}
                    <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1 text-sm">
                        <span class="text-white font-semibold">#{{ $order->id }}</span>
                        <span class="text-slate-400">·</span>
                        <span class="text-slate-300">{{ $order->user->name }}{{ $order->user->chinese_name ? ' (' . $order->user->chinese_name . ')' : '' }}</span>
                        <span class="text-slate-500">·</span>
                        <span class="text-slate-500 text-xs">{{ $order->created_at->format('M j, Y g:i A') }}</span>
                    </div>
                    {{-- Row below: status + actions --}}
                    <div class="flex items-center gap-2 flex-wrap">
                        <span data-order-status class="px-3 py-1 rounded-full text-xs font-medium
                            @if($order->status === 'Pending') bg-amber-500/20 text-amber-400 border border-amber-500/40
                            @elseif($order->status === 'Processing') bg-[#00d4ff]/20 text-[#00d4ff] border border-[#00d4ff]/40
                            @elseif($order->status === 'Cancelled') bg-slate-500/20 text-slate-400 border border-slate-500/40
                            @else bg-emerald-500/20 text-emerald-400 border border-emerald-500/40
                            @endif">{{ $order->status }}</span>
                        <div class="flex gap-2 flex-wrap items-center" data-order-actions>
                            @if($order->status === 'Pending')
                                <button type="button" data-set-status="Processing" class="px-3 py-1.5 rounded-lg bg-[#00d4ff]/20 text-[#00d4ff] border border-[#00d4ff]/40 text-sm font-medium hover:bg-[#00d4ff]/30 transition-colors">{{ __('app.admin.mark_processing') }}</button>
                            @endif
                            @if($order->status === 'Pending' || $order->status === 'Processing')
                                <button type="button" data-set-status="Delivered" class="px-3 py-1.5 rounded-lg bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 text-sm font-medium hover:bg-emerald-500/30 transition-colors">{{ __('app.admin.mark_delivered') }}</button>
                            @endif
                            <form action="{{ route('admin.shop.orders.destroy', $order) }}" method="POST" class="inline order-delete-form" data-confirm="{{ __('app.admin.confirm_delete_order') }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-500/20 text-red-400 border border-red-500/40 text-sm font-medium hover:bg-red-500/30 transition-colors">{{ __('app.admin.delete_order') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
                <ul class="p-4 space-y-2">
                    @foreach($order->items as $item)
                        @php $v = $item->productVariant; $p = $v->product; @endphp
                        <li class="flex justify-between text-sm">
                            <span class="text-slate-300">{{ $p->name }}@if($item->is_preorder) <span class="text-amber-400 text-[10px] uppercase">({{ __('app.shop.preorder_badge') }})</span>@endif · {{ $v->size }}{{ $v->color ? ' · ' . $v->color : '' }} × {{ $item->quantity }}</span>
                            <span class="text-[#00d4ff]">NT$ {{ number_format($item->unit_price * $item->quantity) }}</span>
                        </li>
                    @endforeach
                </ul>
                <div class="px-4 pb-4 flex justify-between items-center">
                    <span class="text-slate-500 text-sm">{{ __('app.shop.total') }}</span>
                    <span class="text-[#00d4ff] font-bold">NT$ {{ number_format($order->total_price) }}</span>
                </div>
            </div>
        @empty
            <div class="py-12 text-center text-slate-500 rounded-xl bg-slate-800/40 border border-slate-700/50">
                <span class="material-symbols-outlined text-4xl mb-2 block">local_shipping</span>
                <p>{{ __('app.admin.no_orders') }}</p>
            </div>
        @endforelse
    </div>

    @if($orders->hasPages())
        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    @endif
</div>

<script>
(function() {
    document.querySelectorAll('.order-delete-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            if (!confirm(form.getAttribute('data-confirm'))) {
                e.preventDefault();
            }
        });
    });
    document.querySelectorAll('[data-set-status]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var orderId = btn.closest('[data-order-id]').getAttribute('data-order-id');
            var status = btn.getAttribute('data-set-status');
            var url = '{{ url("/admin/shop/orders") }}/' + orderId + '/status';
            var body = new FormData();
            body.append('_token', '{{ csrf_token() }}');
            body.append('status', status);

            fetch(url, { method: 'POST', body: body, headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.ok) {
                        location.reload();
                    }
                })
                .catch(function() { location.reload(); });
        });
    });
})();
</script>
@endsection
