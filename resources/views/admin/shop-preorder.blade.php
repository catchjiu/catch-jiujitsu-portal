@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-3">
        <button type="button" onclick="openMenu()" class="text-slate-400 hover:text-white transition-colors p-1">
            <span class="material-symbols-outlined">menu</span>
        </button>
        <h1 class="text-2xl font-bold text-white uppercase tracking-wide" style="font-family: 'Bebas Neue', sans-serif;">
            {{ __('app.admin.preorder') }}
        </h1>
    </div>
    <p class="text-slate-400 text-sm">{{ __('app.admin.preorder_intro') }}</p>

    <div class="space-y-4">
        @forelse($products as $product)
            <a href="{{ route('admin.shop.preorder.product', $product) }}" class="block rounded-xl bg-slate-800/60 border border-slate-700/50 overflow-hidden hover:border-[#00d4ff]/40 transition-colors">
                <div class="p-4 flex flex-wrap items-center gap-3">
                    @if($product->getRawOriginal('image_url'))
                        <img src="{{ $product->image_url }}" alt="" class="w-14 h-14 rounded-lg object-cover border border-slate-600">
                    @else
                        <div class="w-14 h-14 rounded-lg bg-slate-700 flex items-center justify-center text-slate-500">
                            <span class="material-symbols-outlined text-2xl">inventory_2</span>
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="text-white font-semibold">{{ $product->name }}</p>
                        <p class="text-slate-500 text-sm">{{ $product->category }} · {{ __('app.admin.preorder_total_ordered') }}: {{ $product->preorder_total ?? 0 }}</p>
                    </div>
                    <span class="material-symbols-outlined text-slate-500">chevron_right</span>
                </div>
            </a>
        @empty
            <div class="py-12 text-center text-slate-500 rounded-xl bg-slate-800/40 border border-slate-700/50">
                <span class="material-symbols-outlined text-4xl mb-2 block">schedule</span>
                <p>{{ __('app.admin.no_preorder_products') }}</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
