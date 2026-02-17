@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.shop.preorder') }}" class="text-slate-400 hover:text-white transition-colors p-1">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h1 class="text-2xl font-bold text-white uppercase tracking-wide" style="font-family: 'Bebas Neue', sans-serif;">
            {{ __('app.admin.preorder') }}: {{ $product->name }}
        </h1>
    </div>

    {{-- Breakdown by size --}}
    <div class="rounded-xl bg-slate-800/60 border border-slate-700/50 overflow-hidden">
        <h2 class="px-4 py-3 text-[#00d4ff] font-semibold text-sm uppercase tracking-wider border-b border-slate-700/50">
            {{ __('app.admin.preorder_sizes_breakdown') }}
        </h2>
        <div class="p-4">
            @if($bySize->isEmpty())
                <p class="text-slate-500 text-sm">{{ __('app.admin.no_orders') }}</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-slate-500 border-b border-slate-700">
                                <th class="pb-2 pr-4 font-medium">{{ __('app.shop.size') }}</th>
                                <th class="pb-2 pr-4 font-medium">{{ __('app.admin.color') }}</th>
                                <th class="pb-2 font-medium text-right">{{ __('app.admin.quantity_ordered') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bySize as $row)
                                <tr class="border-b border-slate-700/50">
                                    <td class="py-3 pr-4 text-white">{{ $row['size'] }}</td>
                                    <td class="py-3 pr-4 text-slate-400">{{ $row['color'] ?: '—' }}</td>
                                    <td class="py-3 text-right text-[#00d4ff] font-semibold">{{ $row['quantity'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- List of names and sizes ordered --}}
    <div class="rounded-xl bg-slate-800/60 border border-slate-700/50 overflow-hidden">
        <h2 class="px-4 py-3 text-[#00d4ff] font-semibold text-sm uppercase tracking-wider border-b border-slate-700/50">
            {{ __('app.admin.preorder_names_and_sizes') }}
        </h2>
        <div class="p-4">
            @if($orderItems->isEmpty())
                <p class="text-slate-500 text-sm">{{ __('app.admin.no_orders') }}</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-slate-500 border-b border-slate-700">
                                <th class="pb-2 pr-4 font-medium">{{ __('app.admin.member_name') }}</th>
                                <th class="pb-2 pr-4 font-medium">{{ __('app.shop.size') }}</th>
                                <th class="pb-2 pr-4 font-medium">{{ __('app.admin.color') }}</th>
                                <th class="pb-2 pr-4 font-medium">{{ __('app.admin.quantity_ordered') }}</th>
                                <th class="pb-2 font-medium">{{ __('app.admin.order_date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orderItems as $item)
                                @php
                                    $user = $item->order->user;
                                    $name = $user->chinese_name ? $user->name . ' (' . $user->chinese_name . ')' : $user->name;
                                    $v = $item->productVariant;
                                @endphp
                                <tr class="border-b border-slate-700/50">
                                    <td class="py-3 pr-4 text-white">{{ $name }}</td>
                                    <td class="py-3 pr-4 text-slate-300">{{ $v->size }}</td>
                                    <td class="py-3 pr-4 text-slate-400">{{ $v->color ?: '—' }}</td>
                                    <td class="py-3 pr-4 text-[#00d4ff] font-medium">{{ $item->quantity }}</td>
                                    <td class="py-3 text-slate-500 text-xs">{{ $item->order->created_at->format('M j, Y g:i A') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
