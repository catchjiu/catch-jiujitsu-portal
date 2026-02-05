@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <button type="button" onclick="openMenu()" class="text-slate-400 hover:text-white transition-colors p-1">
                <span class="material-symbols-outlined">menu</span>
            </button>
            <h1 class="text-2xl font-bold text-white uppercase tracking-wide" style="font-family: 'Bebas Neue', sans-serif;">
                {{ __('app.admin.gym_shop') }}
            </h1>
        </div>
        <a href="{{ route('admin.shop.products.create') }}" class="inline-flex items-center gap-2 px-4 py-3 rounded-xl bg-[#00d4ff]/20 text-[#00d4ff] border border-[#00d4ff]/40 font-semibold hover:bg-[#00d4ff]/30 transition-colors">
            <span class="material-symbols-outlined">add</span>
            {{ __('app.admin.add_product') }}
        </a>
    </div>

    <p class="text-slate-400 text-sm">{{ __('app.admin.products_list_intro') }}</p>

    <div class="space-y-4">
        @forelse($products as $product)
            <div class="rounded-xl bg-slate-800/60 border border-slate-700/50 overflow-hidden">
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
                        <p class="text-slate-500 text-sm">{{ $product->category }} · NT$ {{ number_format($product->price) }} · {{ $product->variants_count }} {{ __('app.admin.variants') }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.shop.products.edit', $product) }}" class="p-2 rounded-lg bg-slate-700 text-slate-300 hover:bg-slate-600 hover:text-white transition-colors" title="{{ __('app.common.edit') }}">
                            <span class="material-symbols-outlined">edit</span>
                        </a>
                        <form action="{{ route('admin.shop.products.destroy', $product) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('app.admin.confirm_delete_product') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 rounded-lg bg-slate-700 text-slate-300 hover:bg-red-500/20 hover:text-red-400 transition-colors" title="{{ __('app.common.delete') }}">
                                <span class="material-symbols-outlined">delete</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-xl bg-slate-800/40 border border-slate-700/50 p-8 text-center">
                <span class="material-symbols-outlined text-5xl text-slate-600 mb-3 block">inventory_2</span>
                <p class="text-slate-400 mb-4">{{ __('app.admin.no_products_yet') }}</p>
                <a href="{{ route('admin.shop.products.create') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-[#00d4ff]/20 text-[#00d4ff] border border-[#00d4ff]/40 font-semibold hover:bg-[#00d4ff]/30 transition-colors">
                    <span class="material-symbols-outlined">add</span>
                    {{ __('app.admin.add_product') }}
                </a>
            </div>
        @endforelse
    </div>

    @if($products->isNotEmpty())
        <div class="flex flex-wrap gap-3 pt-2">
            <a href="{{ route('admin.shop.stock') }}" class="text-sm text-[#00d4ff] hover:underline">{{ __('app.admin.stock_manager') }} →</a>
            <a href="{{ route('admin.shop.orders') }}" class="text-sm text-[#00d4ff] hover:underline">{{ __('app.admin.order_tracker') }} →</a>
        </div>
    @endif
</div>
@endsection
