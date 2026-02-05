@extends('layouts.app')

@section('title', app()->getLocale() === 'zh-TW' ? '首頁' : 'Dashboard')

@section('content')
<div class="space-y-6">
    @if(!empty($familyBar) && !empty($familyMembers))
    <!-- Family: viewing member + switch -->
    <div class="flex items-center justify-between gap-3 p-3 rounded-xl bg-slate-800/60 border border-slate-700/50">
        <div class="flex items-center gap-3 min-w-0">
            @if($user->avatar)
                <img src="{{ $user->avatar }}" alt="" class="w-10 h-10 rounded-full object-cover border-2 border-slate-600 flex-shrink-0">
            @else
                <div class="w-10 h-10 rounded-full bg-slate-700 border-2 border-slate-600 flex items-center justify-center text-slate-400 font-bold text-sm flex-shrink-0">{{ strtoupper(substr($user->name, 0, 2)) }}</div>
            @endif
            <div class="min-w-0">
                <p class="text-[10px] text-slate-500 uppercase tracking-wider font-medium">{{ app()->getLocale() === 'zh-TW' ? '目前查看' : 'Viewing' }}</p>
                <p class="text-white font-semibold truncate">{{ $user->name }}</p>
            </div>
        </div>
        <a href="{{ route('family.settings') }}" class="flex-shrink-0 px-3 py-2 rounded-lg bg-blue-500/20 text-blue-400 text-sm font-semibold hover:bg-blue-500/30 transition-colors">
            {{ app()->getLocale() === 'zh-TW' ? '切換成員' : 'Switch member' }}
        </a>
    </div>
    @endif

    @if(!empty($pendingPrivateRequests) && $pendingPrivateRequests > 0)
    <!-- Coach: Pending private class requests -->
    <a href="{{ route('coach.private-requests') }}" class="block">
        <div class="rounded-xl p-4 bg-amber-500/20 border-2 border-amber-500/40 hover:bg-amber-500/30 transition-colors">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-amber-500/30 flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-amber-500 text-2xl">mail</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-amber-400 font-bold">{{ app()->getLocale() === 'zh-TW' ? '私教預約請求' : 'Private class requests' }}</p>
                    <p class="text-slate-400 text-sm">{{ $pendingPrivateRequests }} {{ app()->getLocale() === 'zh-TW' ? '筆待處理' : 'pending' }}</p>
                </div>
                <span class="material-symbols-outlined text-amber-500">chevron_right</span>
            </div>
        </div>
    </a>
    @endif

    <!-- Welcome Header -->
    <div class="space-y-1">
        <h2 class="text-2xl font-bold text-white uppercase tracking-wide" style="font-family: 'Bebas Neue', sans-serif;">
            {{ __('app.dashboard.welcome_back') }}, <span class="text-blue-500">{{ explode(' ', $user->name)[0] }}</span>
        </h2>
        <p class="text-slate-400 text-sm">{{ __('app.dashboard.ready_to_train') }}</p>
    </div>

    <!-- Check In Module (at front) -->
    <div id="checkInModule">
        <button type="button" onclick="document.getElementById('checkInModal').classList.remove('hidden')"
                class="w-full glass rounded-2xl p-5 border-t-4 border-t-blue-500 relative overflow-hidden hover:bg-slate-800/60 transition-colors text-left">
            <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
            <div class="relative z-10 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-500/20 flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-blue-500 text-2xl">qr_code_scanner</span>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">{{ __('app.dashboard.check_in') }}</h3>
                    <p class="text-slate-500 text-sm">{{ __('app.dashboard.check_in_subtitle') }}</p>
                </div>
                <span class="material-symbols-outlined text-slate-500">chevron_right</span>
            </div>
        </button>

    </div>

    <!-- Rank Card -->
    <div class="glass rounded-2xl p-5 border-t-4 border-t-amber-500 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
        <div class="relative z-10">
            <div class="flex justify-between items-end">
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-widest font-bold mb-1">{{ __('app.dashboard.current_rank') }}</p>
                    <h3 class="text-3xl font-bold text-white uppercase" style="font-family: 'Bebas Neue', sans-serif;">
                        @if(app()->getLocale() === 'zh-TW')
                            {{ __('app.belts.' . strtolower($user->rank)) }}
                        @else
                            {{ $user->rank }} {{ __('app.dashboard.belt') }}
                        @endif
                    </h3>
                </div>
                <div class="text-right">
                    <div class="flex space-x-1">
                        @for ($i = 0; $i < 4; $i++)
                            <div class="w-2 h-6 rounded-sm {{ $i < $user->stripes ? 'bg-white shadow-[0_0_8px_rgba(255,255,255,0.8)]' : 'bg-slate-700/50' }}"></div>
                        @endfor
                    </div>
                </div>
            </div>
            
            <!-- Visual Belt -->
            @if($user->rank === 'Black')
                <div class="mt-4 h-8 w-full rounded shadow-inner relative flex items-center pl-4 bg-black">
                    <div class="h-full w-16 bg-red-600 flex items-center justify-start gap-1 px-1 absolute left-4">
                        @for ($i = 0; $i < $user->stripes; $i++)
                            <div class="w-1.5 h-full bg-white shadow-sm"></div>
                        @endfor
                    </div>
                </div>
            @elseif($user->rank === 'Brown')
                <div class="mt-4 h-8 w-full rounded shadow-inner relative flex items-center pl-4 bg-yellow-900">
                    <div class="h-full w-16 bg-black flex items-center justify-start gap-1 px-1 absolute left-4">
                        @for ($i = 0; $i < $user->stripes; $i++)
                            <div class="w-1.5 h-full bg-white shadow-sm"></div>
                        @endfor
                    </div>
                </div>
            @elseif($user->rank === 'Purple')
                <div class="mt-4 h-8 w-full rounded shadow-inner relative flex items-center pl-4 bg-purple-600">
                    <div class="h-full w-16 bg-black flex items-center justify-start gap-1 px-1 absolute left-4">
                        @for ($i = 0; $i < $user->stripes; $i++)
                            <div class="w-1.5 h-full bg-white shadow-sm"></div>
                        @endfor
                    </div>
                </div>
            @elseif($user->rank === 'Blue')
                <div class="mt-4 h-8 w-full rounded shadow-inner relative flex items-center pl-4 bg-blue-600">
                    <div class="h-full w-16 bg-black flex items-center justify-start gap-1 px-1 absolute left-4">
                        @for ($i = 0; $i < $user->stripes; $i++)
                            <div class="w-1.5 h-full bg-white shadow-sm"></div>
                        @endfor
                    </div>
                </div>
            @elseif($user->rank === 'Green')
                <div class="mt-4 h-8 w-full rounded shadow-inner relative flex items-center bg-green-500 overflow-hidden">
                    @if($user->belt_variation === 'white')
                        <div class="absolute inset-0 flex items-center"><div class="w-full h-2.5 bg-white"></div></div>
                    @elseif($user->belt_variation === 'black')
                        <div class="absolute inset-0 flex items-center"><div class="w-full h-2.5 bg-black"></div></div>
                    @endif
                    <div class="h-full w-16 bg-black flex items-center justify-start gap-1 px-1 absolute left-4 z-10">
                        @for ($i = 0; $i < $user->stripes; $i++)
                            <div class="w-1.5 h-full bg-white shadow-sm"></div>
                        @endfor
                    </div>
                </div>
            @elseif($user->rank === 'Orange')
                <div class="mt-4 h-8 w-full rounded shadow-inner relative flex items-center bg-orange-500 overflow-hidden">
                    @if($user->belt_variation === 'white')
                        <div class="absolute inset-0 flex items-center"><div class="w-full h-2.5 bg-white"></div></div>
                    @elseif($user->belt_variation === 'black')
                        <div class="absolute inset-0 flex items-center"><div class="w-full h-2.5 bg-black"></div></div>
                    @endif
                    <div class="h-full w-16 bg-black flex items-center justify-start gap-1 px-1 absolute left-4 z-10">
                        @for ($i = 0; $i < $user->stripes; $i++)
                            <div class="w-1.5 h-full bg-white shadow-sm"></div>
                        @endfor
                    </div>
                </div>
            @elseif($user->rank === 'Yellow')
                <div class="mt-4 h-8 w-full rounded shadow-inner relative flex items-center bg-yellow-400 overflow-hidden">
                    @if($user->belt_variation === 'white')
                        <div class="absolute inset-0 flex items-center"><div class="w-full h-2.5 bg-white"></div></div>
                    @elseif($user->belt_variation === 'black')
                        <div class="absolute inset-0 flex items-center"><div class="w-full h-2.5 bg-black"></div></div>
                    @endif
                    <div class="h-full w-16 bg-black flex items-center justify-start gap-1 px-1 absolute left-4 z-10">
                        @for ($i = 0; $i < $user->stripes; $i++)
                            <div class="w-1.5 h-full bg-white shadow-sm"></div>
                        @endfor
                    </div>
                </div>
            @elseif($user->rank === 'Grey')
                <div class="mt-4 h-8 w-full rounded shadow-inner relative flex items-center bg-slate-300 overflow-hidden">
                    @if($user->belt_variation === 'white')
                        <div class="absolute inset-0 flex items-center"><div class="w-full h-2.5 bg-white"></div></div>
                    @elseif($user->belt_variation === 'black')
                        <div class="absolute inset-0 flex items-center"><div class="w-full h-2.5 bg-black"></div></div>
                    @endif
                    <div class="h-full w-16 bg-black flex items-center justify-start gap-1 px-1 absolute left-4 z-10">
                        @for ($i = 0; $i < $user->stripes; $i++)
                            <div class="w-1.5 h-full bg-white shadow-sm"></div>
                        @endfor
                    </div>
                </div>
            @else
                <!-- White Belt (default) -->
                <div class="mt-4 h-8 w-full rounded shadow-inner relative flex items-center pl-4 bg-gray-100">
                    <div class="h-full w-16 bg-black flex items-center justify-start gap-1 px-1 absolute left-4">
                        @for ($i = 0; $i < $user->stripes; $i++)
                            <div class="w-1.5 h-full bg-white shadow-sm"></div>
                        @endfor
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Membership Card -->
    @php
        $borderColor = $user->isGratis() ? 'border-t-emerald-500' : 
            ($user->membership_status === 'active' ? 'border-t-emerald-500' : 
            ($user->membership_status === 'pending' ? 'border-t-amber-500' : 'border-t-red-500'));
    @endphp
    <div class="glass rounded-2xl p-5 border-t-4 {{ $borderColor }} relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
        <div class="relative z-10">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-widest font-bold mb-1">{{ __('app.dashboard.membership') }}</p>
                    @if($user->isGratis())
                        <h3 class="text-2xl font-bold text-emerald-400 uppercase" style="font-family: 'Bebas Neue', sans-serif;">{{ __('app.dashboard.gratis') }}</h3>
                    @elseif($user->membershipPackage)
                        <h3 class="text-2xl font-bold text-white uppercase" style="font-family: 'Bebas Neue', sans-serif;">{{ $user->membershipPackage->name }}</h3>
                        <p class="text-slate-400 text-sm mt-1">
                            @if($user->hasFixedDiscount())
                                <span class="line-through text-slate-500">NT${{ number_format($user->membershipPackage->price) }}</span>
                                <span class="text-emerald-400 font-bold ml-2">NT${{ number_format($user->membershipPackage->price - $user->discount_amount) }}</span>
                            @else
                                NT${{ number_format($user->membershipPackage->price) }}
                            @endif
                        </p>
                    @else
                        <h3 class="text-2xl font-bold text-slate-500 uppercase" style="font-family: 'Bebas Neue', sans-serif;">{{ __('app.dashboard.no_package') }}</h3>
                    @endif
                </div>
                <div class="text-right flex flex-col gap-1">
                    @if($user->isGratis())
                        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase bg-emerald-500/20 text-emerald-400">
                            {{ __('app.common.active') }}
                        </span>
                    @elseif($user->hasFixedDiscount())
                        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase bg-amber-500/20 text-amber-400">
                            -NT${{ number_format($user->discount_amount) }}
                        </span>
                    @else
                        @php
                            $statusColors = [
                                'active' => 'bg-emerald-500/20 text-emerald-400',
                                'pending' => 'bg-amber-500/20 text-amber-400',
                                'expired' => 'bg-red-500/20 text-red-400',
                                'none' => 'bg-slate-700/50 text-slate-400',
                            ];
                            $statusColor = $statusColors[$user->membership_status] ?? $statusColors['none'];
                            $statusTexts = [
                                'active' => __('app.common.active'),
                                'pending' => __('app.common.pending'),
                                'expired' => __('app.common.expired'),
                                'none' => __('app.common.none'),
                            ];
                        @endphp
                        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase {{ $statusColor }}">
                            {{ $statusTexts[$user->membership_status] ?? __('app.common.none') }}
                        </span>
                    @endif
                </div>
            </div>
            
            <!-- Membership Details -->
            <div class="mt-4 p-3 rounded-lg bg-slate-800/50 border border-slate-700/50">
                @if($user->isGratis())
                    <p class="text-emerald-400 text-sm text-center">
                        <span class="material-symbols-outlined text-sm align-middle mr-1">verified</span>
                        {{ __('app.dashboard.complimentary_membership') }}
                    </p>
                @elseif($user->membership_status === 'active')
                    @if($user->membershipPackage && $user->membershipPackage->duration_type === 'classes')
                        <div class="flex justify-between items-center">
                            <span class="text-slate-400 text-sm">{{ __('app.dashboard.classes_remaining') }}</span>
                            <span class="text-white font-bold text-lg" style="font-family: 'Bebas Neue', sans-serif;">{{ $user->classes_remaining ?? 0 }}</span>
                        </div>
                    @elseif($user->membership_expires_at)
                        <div class="flex justify-between items-center">
                            <span class="text-slate-400 text-sm">{{ __('app.dashboard.valid_until') }}</span>
                            <span class="text-white font-bold" style="font-family: 'Bebas Neue', sans-serif;">{{ $user->membership_expires_at->format('M d, Y') }}</span>
                        </div>
                    @else
                        <p class="text-slate-400 text-sm text-center">{{ __('app.dashboard.unlimited_access') }}</p>
                    @endif
                @elseif($user->membership_status === 'pending')
                    <p class="text-amber-400 text-sm text-center">
                        <span class="material-symbols-outlined text-sm align-middle mr-1">hourglass_top</span>
                        {{ __('app.dashboard.payment_pending') }}
                    </p>
                @elseif($user->membership_status === 'expired')
                    <p class="text-red-400 text-sm text-center">
                        <span class="material-symbols-outlined text-sm align-middle mr-1">warning</span>
                        {{ __('app.dashboard.membership_expired') }}
                    </p>
                @else
                    <p class="text-slate-500 text-sm text-center">
                        {{ __('app.dashboard.contact_gym') }}
                    </p>
                @endif
            </div>
            
            <!-- Update Payment Button -->
            @if(!$user->isGratis())
                <a href="{{ route('payments') }}" 
                   class="mt-3 w-full py-2.5 rounded-lg bg-blue-500/20 border border-blue-500/30 text-blue-400 font-semibold text-sm text-center hover:bg-blue-500/30 transition-colors flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-lg">payments</span>
                    {{ __('app.dashboard.update_payment') }}
                </a>
            @endif
        </div>
    </div>

    @if(isset($shopOrders) && $shopOrders->isNotEmpty())
    <!-- Order tracking (Gym Shop) – only when member has orders -->
    <a href="{{ route('shop.index') }}" class="block">
        <div class="w-full glass rounded-2xl p-5 border-t-4 border-t-[#00d4ff] relative overflow-hidden hover:bg-slate-800/60 transition-colors text-left">
            <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
            <div class="relative z-10 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-[#00d4ff]/20 flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-[#00d4ff] text-2xl">local_shipping</span>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">{{ app()->getLocale() === 'zh-TW' ? '我的訂單' : 'My orders' }}</h3>
                    <p class="text-slate-500 text-sm">{{ $shopOrders->count() }} {{ app()->getLocale() === 'zh-TW' ? '筆訂單' : 'order(s)' }}</p>
                </div>
                <span class="material-symbols-outlined text-slate-500 flex-shrink-0">chevron_right</span>
            </div>
            <div class="relative z-10 mt-3 space-y-2">
                @foreach($shopOrders->take(3) as $order)
                    @php
                        $firstItem = $order->items->first();
                        $itemNames = $order->items->map(fn($i) => $i->productVariant->product->localized_name)->unique()->take(2);
                        $nameLabel = $itemNames->isEmpty() ? '#' . $order->id : $itemNames->implode(', ');
                        if ($order->items->count() > 2) {
                            $nameLabel .= (app()->getLocale() === 'zh-TW' ? ' 等 ' . $order->items->count() . ' 件' : ' + ' . ($order->items->count() - 2) . ' more');
                        }
                    @endphp
                    <div class="flex items-center justify-between gap-2 py-2 border-t border-slate-700/50 first:border-t-0 first:pt-0">
                        <div class="min-w-0 flex-1">
                            <p class="text-white text-sm font-medium truncate">{{ $nameLabel }}</p>
                            <p class="text-slate-500 text-xs">#{{ $order->id }} · NT$ {{ number_format($order->total_price) }}</p>
                        </div>
                        <span class="px-2 py-0.5 rounded text-xs font-medium flex-shrink-0
                            @if($order->status === 'Pending') bg-amber-500/20 text-amber-400
                            @elseif($order->status === 'Processing') bg-[#00d4ff]/20 text-[#00d4ff]
                            @else bg-emerald-500/20 text-emerald-400
                            @endif">{{ $order->status }}</span>
                    </div>
                @endforeach
                @if($shopOrders->count() > 3)
                    <p class="text-slate-500 text-xs pt-1">{{ app()->getLocale() === 'zh-TW' ? '點擊查看全部' : 'Tap to see all' }}</p>
                @endif
            </div>
        </div>
    </a>
    @endif

    <!-- Book Private Class (below membership) -->
    <div>
        <button type="button" id="openPrivateClassModal"
                class="w-full glass rounded-2xl p-5 border-t-4 border-t-violet-500 relative overflow-hidden hover:bg-slate-800/60 transition-colors text-left">
            <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
            <div class="relative z-10 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-violet-500/20 flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-violet-500 text-2xl">person_search</span>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">{{ app()->getLocale() === 'zh-TW' ? '預約私教課' : 'Book private class' }}</h3>
                    <p class="text-slate-500 text-sm">{{ app()->getLocale() === 'zh-TW' ? '選擇教練與時段' : 'Choose a coach and time slot' }}</p>
                </div>
                <span class="material-symbols-outlined text-slate-500">chevron_right</span>
            </div>
        </button>
    </div>

    <!-- Next Class (moved here, under membership) -->
    <div>
        <h3 class="text-lg font-bold text-white mb-3 flex items-center gap-2" style="font-family: 'Bebas Neue', sans-serif;">
            <span class="material-symbols-outlined text-amber-500">event</span>
            {{ __('app.dashboard.my_next_class') }}
        </h3>
        @if($nextClass)
            <div class="glass rounded-2xl p-5 bg-gradient-to-br from-slate-800 to-slate-900 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
                <div class="relative z-10">
                    <div class="flex justify-between items-start mb-2">
                        <span class="px-2 py-1 rounded bg-blue-500/20 text-blue-400 text-xs font-bold uppercase tracking-wider">
                            {{ $nextClass->type }}
                        </span>
                        <span class="text-slate-400 text-xs font-mono">
                            {{ $nextClass->start_time->format('H:i') }}
                        </span>
                    </div>
                    <h4 class="text-xl font-bold text-white mb-1">{{ $nextClass->localized_title }}</h4>
                    
                    <!-- Instructor with Profile Picture -->
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-full overflow-hidden bg-slate-700 border-2 border-slate-600 flex-shrink-0">
                            @if($nextClass->instructor && $nextClass->instructor->avatar)
                                <img src="{{ $nextClass->instructor->avatar }}" alt="{{ $nextClass->instructor_display_name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-slate-400 text-sm font-bold">
                                    {{ substr($nextClass->instructor_display_name, 0, 1) }}
                                </div>
                            @endif
                        </div>
                        <div>
                            <p class="text-white text-sm font-medium">{{ $nextClass->instructor_display_name }}</p>
                            <p class="text-slate-500 text-xs">{{ __('app.dashboard.instructor') }}</p>
                        </div>
                    </div>
                    
                    <div class="text-center text-slate-500 text-xs mb-4">
                        {{ $nextClass->start_time->format('l, F j') }}
                    </div>
                    
                    @if($nextBooking)
                        <form action="{{ route('book.destroy', $nextClass->id) }}" method="POST" 
                              onsubmit="return confirm('{{ __('app.messages.confirm_cancel') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full py-2 rounded-lg border border-red-500/50 text-red-400 text-sm font-medium hover:bg-red-500/10 transition-colors flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined text-lg">event_busy</span>
                                {{ __('app.dashboard.cancel_booking') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @else
            <div class="p-6 rounded-2xl border-2 border-dashed border-slate-700 text-center text-slate-500">
                <p>{{ __('app.dashboard.no_upcoming_classes') }}</p>
                <a href="{{ route('schedule') }}" class="text-blue-400 hover:text-blue-300 text-sm mt-2 inline-block">{{ __('app.dashboard.browse_schedule') }}</a>
            </div>
        @endif
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 gap-4">
        <div class="glass rounded-2xl p-5 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
            <div class="relative z-10 flex flex-col items-center justify-center py-2">
                <span class="text-4xl font-bold text-amber-500" style="font-family: 'Bebas Neue', sans-serif;">{{ $user->total_mat_hours }}</span>
                <span class="text-xs text-slate-400 uppercase tracking-wider mt-1">{{ __('app.dashboard.mat_hours') }}</span>
            </div>
        </div>
        <div class="glass rounded-2xl p-5 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
            <div class="relative z-10 flex flex-col items-center justify-center py-2">
                <span class="text-4xl font-bold text-blue-500" style="font-family: 'Bebas Neue', sans-serif;">{{ $classesThisMonth }}</span>
                <span class="text-xs text-slate-400 uppercase tracking-wider mt-1">{{ __('app.dashboard.classes_mo') }}</span>
            </div>
        </div>
    </div>

    <!-- Monthly Goals Progress -->
    <a href="{{ route('goals') }}" class="block">
        <div class="glass rounded-2xl p-5 relative overflow-hidden hover:bg-slate-800/60 transition-colors">
            <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
            <div class="relative z-10">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2" style="font-family: 'Bebas Neue', sans-serif;">
                        <span class="material-symbols-outlined text-blue-500">emoji_events</span>
                        {{ __('app.dashboard.monthly_goals') }}
                    </h3>
                    <span class="text-slate-500 text-xs">{{ now()->format('F') }}</span>
                </div>
                @php
                    $classesAttended = $user->monthly_classes_attended;
                    $classGoal = $user->monthly_class_goal ?? 12;
                    $classProgress = $classGoal > 0 ? min(100, ($classesAttended / $classGoal) * 100) : 0;
                @endphp
                <div class="flex items-center gap-3">
                    <div class="flex-1">
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-slate-400">{{ __('app.dashboard.classes') }}</span>
                            <span class="text-slate-300">{{ $classesAttended }} / {{ $classGoal }}</span>
                        </div>
                        <div class="h-2 bg-slate-700 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-blue-600 to-blue-400 rounded-full" style="width: {{ $classProgress }}%"></div>
                        </div>
                    </div>
                    <span class="material-symbols-outlined text-slate-600">chevron_right</span>
                </div>
            </div>
        </div>
    </a>

    <!-- Leaderboard Link -->
    <a href="{{ route('leaderboard') }}" class="block">
        <div class="glass rounded-2xl p-4 relative overflow-hidden hover:bg-slate-800/60 transition-colors">
            <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
            <div class="relative z-10 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-white text-2xl">leaderboard</span>
                </div>
                <div class="flex-1">
                    <h3 class="text-white font-semibold">{{ __('app.dashboard.leaderboard') }}</h3>
                    <p class="text-slate-500 text-xs">{{ __('app.dashboard.see_top_trainers') }}</p>
                </div>
                <span class="material-symbols-outlined text-slate-600">chevron_right</span>
            </div>
        </div>
    </a>

    <!-- Previous Classes -->
    <div>
        <h3 class="text-lg font-bold text-white mb-3 flex items-center gap-2" style="font-family: 'Bebas Neue', sans-serif;">
            <span class="material-symbols-outlined text-slate-400">history</span>
            {{ __('app.dashboard.previous_classes') }}
        </h3>
        @if($previousClasses->count() > 0)
            <div class="space-y-3">
                @foreach($previousClasses as $class)
                    <div class="glass rounded-xl p-4 relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
                        <div class="relative z-10 flex items-center gap-4">
                            <!-- Instructor Avatar -->
                            <div class="w-10 h-10 rounded-full overflow-hidden bg-slate-700 border border-slate-600 flex-shrink-0">
                                @if($class->instructor && $class->instructor->avatar)
                                    <img src="{{ $class->instructor->avatar }}" alt="{{ $class->instructor_display_name }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-500 text-sm font-bold">
                                        {{ substr($class->instructor_display_name, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Class Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-white font-semibold text-sm truncate">{{ $class->localized_title }}</h4>
                                    <span class="px-1.5 py-0.5 rounded bg-slate-700 text-slate-400 text-xs uppercase flex-shrink-0">
                                        {{ $class->type }}
                                    </span>
                                </div>
                                <p class="text-slate-500 text-xs mt-0.5">
                                    {{ $class->start_time->format('D, M j') }} at {{ $class->start_time->format('H:i') }}
                                    • {{ $class->instructor_display_name }}
                                </p>
                            </div>
                            
                            <!-- Check mark -->
                            <div class="w-6 h-6 rounded-full bg-emerald-500/20 flex items-center justify-center flex-shrink-0">
                                <span class="material-symbols-outlined text-emerald-400 text-sm">check</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-6 rounded-2xl border-2 border-dashed border-slate-700 text-center text-slate-500">
                <p>{{ __('app.dashboard.no_previous_classes') }}</p>
                <a href="{{ route('schedule') }}" class="text-blue-400 hover:text-blue-300 text-sm mt-2 inline-block">{{ __('app.dashboard.book_first_class') }}</a>
            </div>
        @endif
    </div>

    <!-- Check In Modal - at end of content with high z-index so it sits above all cards -->
    <div id="checkInModal" class="hidden fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
         onclick="if(event.target===this) this.classList.add('hidden')">
        <div class="glass rounded-2xl p-6 max-w-sm w-full relative" onclick="event.stopPropagation()">
            <button type="button" onclick="document.getElementById('checkInModal').classList.add('hidden')"
                    class="absolute top-4 right-4 text-slate-400 hover:text-white">
                <span class="material-symbols-outlined">close</span>
            </button>
            <h3 class="text-xl font-bold text-white mb-4" style="font-family: 'Bebas Neue', sans-serif;">{{ __('app.dashboard.check_in') }}</h3>

            <button type="button" id="checkInTodayBtn"
                    class="w-full py-3 rounded-xl bg-blue-500 hover:bg-blue-600 text-white font-semibold flex items-center justify-center gap-2 mb-4 transition-colors">
                <span class="material-symbols-outlined">event_available</span>
                {{ __('app.dashboard.check_in_for_today') }}
            </button>
            <p class="text-slate-500 text-xs mb-6">{{ __('app.dashboard.check_in_today_desc') }}</p>

            <p class="text-slate-400 text-sm font-semibold mb-2">{{ __('app.dashboard.scan_at_kiosk') }}</p>
            <button type="button" id="qrFullscreenBtn" class="block w-full"
                    onclick="document.getElementById('checkInModal').classList.add('hidden'); document.getElementById('qrFullscreen').classList.remove('hidden')">
                <div class="p-4 rounded-xl bg-white flex justify-center">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=CATCH-{{ $user->id }}"
                         alt="Check-in QR code" class="w-40 h-40">
                </div>
                <p class="text-slate-500 text-xs mt-2">{{ __('app.dashboard.tap_qr_fullscreen') }}</p>
            </button>
        </div>
    </div>

    <!-- QR Fullscreen overlay -->
    <div id="qrFullscreen" class="hidden fixed inset-0 z-[9999] bg-black flex flex-col items-center justify-center p-4"
         onclick="this.classList.add('hidden')">
        <p class="text-white text-sm mb-4">{{ __('app.dashboard.tap_qr_fullscreen') }}</p>
        <div class="p-6 rounded-2xl bg-white">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=320x320&data=CATCH-{{ $user->id }}"
                 alt="Check-in QR code" class="w-64 h-64 sm:w-80 sm:h-80">
        </div>
        <p class="text-slate-500 text-xs mt-4">CATCH-{{ $user->id }}</p>
    </div>
</div>

<script>
(function() {
    var btn = document.getElementById('checkInTodayBtn');
    if (!btn) return;
    var originalHtml = btn.innerHTML;
    btn.addEventListener('click', function() {
        btn.disabled = true;
        btn.innerHTML = '<span class="material-symbols-outlined animate-spin">progress_activity</span>';
        var localDate = (function() {
            var d = new Date();
            return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        })();
        fetch('{{ route("checkin.today") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ date: localDate })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                document.getElementById('checkInModal').classList.add('hidden');
                window.location.reload();
            } else {
                alert(data.message || 'Something went wrong.');
            }
        })
        .catch(function() { alert('Check-in failed. Try again.'); })
        .finally(function() {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        });
    });
})();
</script>
@endsection
