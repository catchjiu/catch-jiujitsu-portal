@extends('layouts.admin')

@section('title', 'Member Directory')

@section('content')
<div class="space-y-5">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div class="flex items-center gap-3">
            <button onclick="openMenu()" class="text-slate-400 hover:text-white transition-colors">
                <span class="material-symbols-outlined">menu</span>
            </button>
            <h1 class="text-2xl font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">Member Directory</h1>
        </div>
        <button class="text-slate-400 hover:text-white transition-colors">
            <span class="material-symbols-outlined">tune</span>
        </button>
    </div>

    <!-- Search Bar -->
    <form action="{{ route('admin.members') }}" method="GET">
        <div class="relative">
            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-500">search</span>
            <input type="text" name="search" value="{{ $search }}" placeholder="Search by name, belt..."
                class="w-full pl-12 pr-4 py-3 rounded-xl bg-slate-800/60 border border-slate-700/50 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors">
        </div>
    </form>

    <!-- Filter Tabs -->
    <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-hide">
        @foreach(['All', 'Adults', 'Kids', 'Competitors'] as $filter)
            <a href="{{ route('admin.members', ['filter' => $filter, 'search' => $search]) }}"
               class="px-4 py-2 rounded-full text-sm font-semibold whitespace-nowrap transition-all
               {{ $currentFilter === $filter 
                   ? 'bg-blue-500 text-white' 
                   : 'bg-slate-800/60 text-slate-400 hover:bg-slate-700/60 hover:text-white' }}">
                {{ $filter }}
            </a>
        @endforeach
    </div>

    <!-- Member List -->
    <div class="space-y-3">
        @forelse($members as $member)
            @php
                $beltColors = [
                    'White' => ['bg' => 'bg-gray-200', 'text' => 'White Belt'],
                    'Grey' => ['bg' => 'bg-gray-300', 'text' => 'Grey Belt'],
                    'Yellow' => ['bg' => 'bg-yellow-400', 'text' => 'Yellow Belt'],
                    'Orange' => ['bg' => 'bg-orange-500', 'text' => 'Orange Belt'],
                    'Green' => ['bg' => 'bg-green-500', 'text' => 'Green Belt'],
                    'Blue' => ['bg' => 'bg-blue-600', 'text' => 'Blue Belt'],
                    'Purple' => ['bg' => 'bg-purple-600', 'text' => 'Purple Belt'],
                    'Brown' => ['bg' => 'bg-yellow-800', 'text' => 'Brown Belt'],
                    'Black' => ['bg' => 'bg-black', 'text' => 'Black Belt'],
                ];
                $belt = $beltColors[$member->rank] ?? $beltColors['White'];
                $isBlackBelt = $member->rank === 'Black';
                
                // Random online status for demo (you can add a real field later)
                $isOnline = rand(0, 1);
            @endphp
            
            <a href="{{ route('admin.members.show', $member->id) }}" 
               class="flex items-center gap-4 p-4 rounded-2xl bg-slate-800/40 border border-slate-700/30 hover:bg-slate-800/60 transition-all group">
                
                <!-- Avatar with status indicator -->
                <div class="relative flex-shrink-0">
                    <div class="w-14 h-14 rounded-full overflow-hidden bg-slate-700 border-2 border-slate-600">
                        @if($member->avatar)
                            <img src="{{ $member->avatar }}" alt="{{ $member->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-slate-400 text-xl font-bold" style="font-family: 'Bebas Neue', sans-serif;">
                                {{ strtoupper(substr($member->name, 0, 2)) }}
                            </div>
                        @endif
                    </div>
                    <!-- Online status dot -->
                    <div class="absolute -bottom-0.5 -left-0.5 w-4 h-4 rounded-full border-2 border-slate-800 {{ $isOnline ? 'bg-emerald-500' : 'bg-amber-500' }}"></div>
                </div>

                <!-- Member Info -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <h3 class="text-white font-semibold truncate">{{ $member->name }}</h3>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ ($member->age_group ?? 'Adults') === 'Kids' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-700/50 text-slate-400' }} flex-shrink-0">
                            {{ $member->age_group ?? 'Adults' }}
                        </span>
                        @if($member->discount_type === 'gratis')
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase bg-emerald-500/20 text-emerald-400 flex-shrink-0" title="Gratis Member">FREE</span>
                        @elseif($member->discount_type === 'percentage' && $member->discount_percentage > 0)
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase bg-amber-500/20 text-amber-400 flex-shrink-0" title="{{ $member->discount_percentage }}% Discount">{{ $member->discount_percentage }}%</span>
                        @endif
                        @if($member->membership_status === 'active' || $member->discount_type === 'gratis')
                            <span class="w-2 h-2 rounded-full bg-emerald-500 flex-shrink-0" title="Active Membership"></span>
                        @elseif($member->membership_status === 'pending')
                            <span class="w-2 h-2 rounded-full bg-amber-500 flex-shrink-0" title="Pending"></span>
                        @elseif($member->membership_status === 'expired')
                            <span class="w-2 h-2 rounded-full bg-red-500 flex-shrink-0" title="Expired"></span>
                        @endif
                    </div>
                    
                    <!-- Belt Display -->
                    <div class="flex items-center gap-2">
                        <!-- Belt bar with stripes -->
                        <div class="flex items-center">
                            <div class="w-16 h-4 rounded-sm {{ $belt['bg'] }} relative flex items-center justify-end pr-1">
                                @if($isBlackBelt)
                                    <!-- Red bar for black belt -->
                                    <div class="h-full w-6 bg-red-600 flex items-center justify-around px-0.5">
                                        @for ($i = 0; $i < $member->stripes; $i++)
                                            <div class="w-1 h-full bg-white"></div>
                                        @endfor
                                    </div>
                                @else
                                    <!-- Black bar for stripes -->
                                    <div class="h-full w-6 bg-black flex items-center justify-around px-0.5">
                                        @for ($i = 0; $i < $member->stripes; $i++)
                                            <div class="w-1 h-full bg-white"></div>
                                        @endfor
                                    </div>
                                @endif
                            </div>
                        </div>
                        <span class="text-slate-400 text-sm">{{ $belt['text'] }}</span>
                    </div>
                    
                    <!-- Membership Expiry -->
                    @if($member->discount_type === 'gratis')
                        <p class="text-emerald-400 text-xs mt-1">
                            <span class="material-symbols-outlined text-xs align-middle">all_inclusive</span>
                            Unlimited Access
                        </p>
                    @elseif($member->membership_expires_at)
                        @php
                            $isExpired = $member->membership_expires_at->isPast();
                            $isExpiringSoon = !$isExpired && $member->membership_expires_at->diffInDays(now()) <= 7;
                        @endphp
                        <p class="text-xs mt-1 {{ $isExpired ? 'text-red-400' : ($isExpiringSoon ? 'text-amber-400' : 'text-slate-500') }}">
                            <span class="material-symbols-outlined text-xs align-middle">event</span>
                            {{ $isExpired ? 'Expired' : 'Expires' }}: {{ $member->membership_expires_at->format('M j, Y') }}
                        </p>
                    @elseif($member->membership_status === 'none' || !$member->membership_status)
                        <p class="text-slate-600 text-xs mt-1">
                            <span class="material-symbols-outlined text-xs align-middle">cancel</span>
                            No membership
                        </p>
                    @endif
                </div>

                <!-- Arrow -->
                <span class="material-symbols-outlined text-slate-600 group-hover:text-slate-400 transition-colors">
                    chevron_right
                </span>
            </a>
        @empty
            <div class="p-10 text-center text-slate-500 bg-slate-900/50 rounded-xl border border-dashed border-slate-700">
                <span class="material-symbols-outlined text-4xl mb-2">person_off</span>
                <p>No members found.</p>
            </div>
        @endforelse
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Save scroll position before navigating away
    document.querySelectorAll('a[href*="members/"]').forEach(link => {
        link.addEventListener('click', function() {
            sessionStorage.setItem('membersScrollPosition', window.scrollY);
        });
    });
    
    // Restore scroll position when page loads
    document.addEventListener('DOMContentLoaded', function() {
        const savedPosition = sessionStorage.getItem('membersScrollPosition');
        if (savedPosition) {
            window.scrollTo(0, parseInt(savedPosition));
            // Clear after restoring so it doesn't affect fresh visits
            // Only clear if we came back from a member detail page
        }
    });
    
    // Clear scroll position if navigating to a different section
    window.addEventListener('beforeunload', function() {
        // Keep the position if going to member detail, clear otherwise
        const currentUrl = window.location.href;
        if (!currentUrl.includes('/admin/members')) {
            sessionStorage.removeItem('membersScrollPosition');
        }
    });
</script>
@endsection
