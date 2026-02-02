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
    </div>

    <!-- Search Bar -->
    <form action="{{ route('admin.members') }}" method="GET" id="filterForm">
        <div class="relative">
            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-500">search</span>
            <input type="text" name="search" value="{{ $search }}" placeholder="Search by name, belt..."
                class="w-full pl-12 pr-4 py-3 rounded-xl bg-slate-800/60 border border-slate-700/50 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors">
        </div>
        
        <!-- Hidden inputs for filters -->
        <input type="hidden" name="age" id="ageFilter" value="{{ request('age', '') }}">
        <input type="hidden" name="status" id="statusFilter" value="{{ request('status', '') }}">
    </form>

    <!-- Filter Tabs -->
    <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-hide">
        <!-- Age Group Filters -->
        <button type="button" onclick="toggleFilter('age', '')" 
            class="px-4 py-2 rounded-full text-sm font-semibold whitespace-nowrap transition-all filter-btn
            {{ request('age', '') === '' ? 'bg-blue-500 text-white' : 'bg-slate-800/60 text-slate-400 hover:bg-slate-700/60 hover:text-white' }}">
            All
        </button>
        <button type="button" onclick="toggleFilter('age', 'Adults')"
            class="px-4 py-2 rounded-full text-sm font-semibold whitespace-nowrap transition-all filter-btn
            {{ request('age') === 'Adults' ? 'bg-blue-500 text-white' : 'bg-slate-800/60 text-slate-400 hover:bg-slate-700/60 hover:text-white' }}">
            Adults
        </button>
        <button type="button" onclick="toggleFilter('age', 'Kids')"
            class="px-4 py-2 rounded-full text-sm font-semibold whitespace-nowrap transition-all filter-btn
            {{ request('age') === 'Kids' ? 'bg-blue-500 text-white' : 'bg-slate-800/60 text-slate-400 hover:bg-slate-700/60 hover:text-white' }}">
            Kids
        </button>
        
        <!-- Divider -->
        <div class="w-px bg-slate-700 mx-1"></div>
        
        <!-- Status Filters -->
        <button type="button" onclick="toggleFilter('status', 'active')"
            class="px-4 py-2 rounded-full text-sm font-semibold whitespace-nowrap transition-all filter-btn
            {{ request('status') === 'active' ? 'bg-emerald-500 text-white' : 'bg-slate-800/60 text-slate-400 hover:bg-slate-700/60 hover:text-white' }}">
            Active
        </button>
        <button type="button" onclick="toggleFilter('status', 'inactive')"
            class="px-4 py-2 rounded-full text-sm font-semibold whitespace-nowrap transition-all filter-btn
            {{ request('status') === 'inactive' ? 'bg-red-500 text-white' : 'bg-slate-800/60 text-slate-400 hover:bg-slate-700/60 hover:text-white' }}">
            Inactive
        </button>
    </div>
    
    <script>
        function toggleFilter(filterType, value) {
            const form = document.getElementById('filterForm');
            
            if (filterType === 'age') {
                document.getElementById('ageFilter').value = value;
            } else if (filterType === 'status') {
                const currentValue = document.getElementById('statusFilter').value;
                // Toggle off if same value clicked
                document.getElementById('statusFilter').value = currentValue === value ? '' : value;
            }
            
            form.submit();
        }
    </script>

    <!-- Member List -->
    <div class="space-y-3">
        @forelse($members as $member)
            @php
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
                        @elseif($member->discount_type === 'fixed' && ($member->discount_amount ?? 0) > 0)
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase bg-amber-500/20 text-amber-400 flex-shrink-0" title="NT${{ number_format($member->discount_amount) }} Discount">-${{ number_format($member->discount_amount) }}</span>
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
                            @if($member->rank === 'Black')
                                <div class="w-16 h-4 rounded-sm bg-black relative flex items-center pl-1">
                                    <div class="h-full w-6 bg-red-600 flex items-center justify-start gap-0.5 px-0.5">
                                        @for ($i = 0; $i < $member->stripes; $i++)
                                            <div class="w-1 h-full bg-white"></div>
                                        @endfor
                                    </div>
                                </div>
                            @elseif($member->rank === 'Brown')
                                <div class="w-16 h-4 rounded-sm bg-yellow-900 relative flex items-center pl-1">
                                    <div class="h-full w-6 bg-black flex items-center justify-start gap-0.5 px-0.5">
                                        @for ($i = 0; $i < $member->stripes; $i++)
                                            <div class="w-1 h-full bg-white"></div>
                                        @endfor
                                    </div>
                                </div>
                            @elseif($member->rank === 'Purple')
                                <div class="w-16 h-4 rounded-sm bg-purple-600 relative flex items-center pl-1">
                                    <div class="h-full w-6 bg-black flex items-center justify-start gap-0.5 px-0.5">
                                        @for ($i = 0; $i < $member->stripes; $i++)
                                            <div class="w-1 h-full bg-white"></div>
                                        @endfor
                                    </div>
                                </div>
                            @elseif($member->rank === 'Blue')
                                <div class="w-16 h-4 rounded-sm bg-blue-600 relative flex items-center pl-1">
                                    <div class="h-full w-6 bg-black flex items-center justify-start gap-0.5 px-0.5">
                                        @for ($i = 0; $i < $member->stripes; $i++)
                                            <div class="w-1 h-full bg-white"></div>
                                        @endfor
                                    </div>
                                </div>
                            @elseif($member->rank === 'Green')
                                <div class="w-16 h-4 rounded-sm bg-green-500 relative flex items-center pl-1">
                                    <div class="h-full w-6 bg-black flex items-center justify-start gap-0.5 px-0.5">
                                        @for ($i = 0; $i < $member->stripes; $i++)
                                            <div class="w-1 h-full bg-white"></div>
                                        @endfor
                                    </div>
                                </div>
                            @elseif($member->rank === 'Orange')
                                <div class="w-16 h-4 rounded-sm bg-orange-500 relative flex items-center pl-1">
                                    <div class="h-full w-6 bg-black flex items-center justify-start gap-0.5 px-0.5">
                                        @for ($i = 0; $i < $member->stripes; $i++)
                                            <div class="w-1 h-full bg-white"></div>
                                        @endfor
                                    </div>
                                </div>
                            @elseif($member->rank === 'Yellow')
                                <div class="w-16 h-4 rounded-sm bg-yellow-400 relative flex items-center pl-1">
                                    <div class="h-full w-6 bg-black flex items-center justify-start gap-0.5 px-0.5">
                                        @for ($i = 0; $i < $member->stripes; $i++)
                                            <div class="w-1 h-full bg-white"></div>
                                        @endfor
                                    </div>
                                </div>
                            @elseif($member->rank === 'Grey')
                                <div class="w-16 h-4 rounded-sm bg-slate-300 relative flex items-center pl-1">
                                    <div class="h-full w-6 bg-black flex items-center justify-start gap-0.5 px-0.5">
                                        @for ($i = 0; $i < $member->stripes; $i++)
                                            <div class="w-1 h-full bg-white"></div>
                                        @endfor
                                    </div>
                                </div>
                            @else
                                <!-- White Belt (default) -->
                                <div class="w-16 h-4 rounded-sm bg-gray-100 relative flex items-center pl-1">
                                    <div class="h-full w-6 bg-black flex items-center justify-start gap-0.5 px-0.5">
                                        @for ($i = 0; $i < $member->stripes; $i++)
                                            <div class="w-1 h-full bg-white"></div>
                                        @endfor
                                    </div>
                                </div>
                            @endif
                        </div>
                        <span class="text-slate-400 text-sm">{{ $member->rank }} Belt</span>
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
    // Save scroll position and filters before navigating to member detail
    document.querySelectorAll('a[href*="members/"]').forEach(link => {
        link.addEventListener('click', function() {
            sessionStorage.setItem('membersScrollPosition', window.scrollY);
            sessionStorage.setItem('membersFilters', JSON.stringify({
                age: document.getElementById('ageFilter').value,
                status: document.getElementById('statusFilter').value,
                search: document.querySelector('input[name="search"]').value
            }));
        });
    });
    
    // Restore scroll position and filters when page loads
    document.addEventListener('DOMContentLoaded', function() {
        const savedPosition = sessionStorage.getItem('membersScrollPosition');
        const savedFilters = sessionStorage.getItem('membersFilters');
        const urlParams = new URLSearchParams(window.location.search);
        
        // Only restore filters if coming back (URL has no params but we have saved filters)
        if (savedFilters && !urlParams.has('age') && !urlParams.has('status') && !urlParams.has('search')) {
            const filters = JSON.parse(savedFilters);
            if (filters.age || filters.status || filters.search) {
                // Redirect with saved filters
                const newUrl = new URL(window.location.href);
                if (filters.age) newUrl.searchParams.set('age', filters.age);
                if (filters.status) newUrl.searchParams.set('status', filters.status);
                if (filters.search) newUrl.searchParams.set('search', filters.search);
                window.location.href = newUrl.toString();
                return;
            }
        }
        
        // Restore scroll position
        if (savedPosition) {
            window.scrollTo(0, parseInt(savedPosition));
        }
    });
    
    // Clear saved state when navigating away from members section
    const originalPushState = history.pushState;
    const clearIfLeavingMembers = function(url) {
        if (typeof url === 'string' && !url.includes('/admin/members')) {
            sessionStorage.removeItem('membersScrollPosition');
            sessionStorage.removeItem('membersFilters');
        }
    };
    
    // Also handle clicking on menu items or other links
    document.querySelectorAll('a:not([href*="members"])').forEach(link => {
        link.addEventListener('click', function() {
            if (!this.href.includes('/admin/members')) {
                sessionStorage.removeItem('membersScrollPosition');
                sessionStorage.removeItem('membersFilters');
            }
        });
    });
</script>
@endsection
