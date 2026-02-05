@extends('layouts.app')

@section('content')
<div class="space-y-4">
    <h2 class="text-2xl font-bold text-white uppercase tracking-wide" style="font-family: 'Bebas Neue', sans-serif;">
        {{ __('app.shop.title') }}
    </h2>

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
                    @php $variants = $product->variants->where('stock_quantity', '>', 0); @endphp
                    @if($variants->isEmpty())
                        <p class="text-slate-500 text-xs mt-2">{{ __('app.shop.out_of_stock') }}</p>
                    @else
                        <form action="{{ route('shop.quick-buy') }}" method="POST" class="mt-2 space-y-2">
                            @csrf
                            <select name="product_variant_id" required class="w-full rounded-lg bg-slate-900 border border-slate-600 text-white text-sm py-2 px-3 focus:border-[#00d4ff]/60 outline-none">
                                @foreach($variants as $v)
                                    <option value="{{ $v->id }}">{{ $v->size }}{{ $v->color ? ' · ' . $v->color : '' }} ({{ $v->stock_quantity }})</option>
                                @endforeach
                            </select>
                            <button type="submit" class="w-full py-2.5 rounded-xl bg-[#00d4ff]/20 text-[#00d4ff] border border-[#00d4ff]/40 font-semibold text-sm hover:bg-[#00d4ff]/30 transition-colors flex items-center justify-center gap-1">
                                <span class="material-symbols-outlined text-lg">shopping_cart</span>
                                {{ __('app.shop.order') }}
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
@endsection
