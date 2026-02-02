@extends('layouts.admin')

@section('title', 'Admin Overview')

@section('content')
<div class="space-y-5">
    <!-- Welcome Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <button onclick="openMenu()" class="text-slate-400 hover:text-white transition-colors">
                <span class="material-symbols-outlined">menu</span>
            </button>
            <div class="w-10 h-10 rounded-full overflow-hidden bg-slate-700 border-2 border-slate-600">
                @if($user->avatar)
                    <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-slate-400 font-bold text-sm">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                @endif
            </div>
            <div>
                <p class="text-slate-400 text-xs">Welcome back</p>
                <h1 class="text-white font-bold text-lg">Coach {{ explode(' ', $user->name)[0] }}</h1>
            </div>
        </div>
        <!-- Notifications Bell -->
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 hover:text-white transition-colors relative">
                <span class="material-symbols-outlined">notifications</span>
                @if($notificationCount > 0)
                    <span class="absolute top-1 right-1 w-2 h-2 bg-blue-500 rounded-full"></span>
                @endif
            </button>
            
            <!-- Notifications Dropdown -->
            <div x-show="open" 
                 @click.away="open = false"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute right-0 top-12 w-80 bg-slate-800 border border-slate-700 rounded-xl shadow-xl z-50 overflow-hidden"
                 style="display: none;">
                
                <div class="p-3 border-b border-slate-700">
                    <h4 class="text-white font-semibold text-sm">Notifications</h4>
                </div>
                
                <div class="max-h-80 overflow-y-auto">
                    @if($notificationCount === 0)
                        <div class="p-4 text-center text-slate-500 text-sm">
                            No new notifications
                        </div>
                    @else
                        <!-- Pending Payments -->
                        @foreach($pendingPayments as $payment)
                            <a href="{{ route('admin.members.show', $payment->user->id) }}" 
                               class="flex items-center gap-3 p-3 hover:bg-slate-700/50 transition-colors border-b border-slate-700/50">
                                <div class="w-9 h-9 rounded-full bg-blue-500/20 flex items-center justify-center flex-shrink-0">
                                    <span class="material-symbols-outlined text-blue-500 text-lg">payments</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-white text-sm font-medium truncate">{{ $payment->user->name }}</p>
                                    <p class="text-blue-400 text-xs">
                                        Payment pending - NT${{ number_format($payment->amount) }}
                                    </p>
                                </div>
                            </a>
                        @endforeach
                        
                        <!-- Expiring Memberships -->
                        @foreach($expiringMemberships as $member)
                            <a href="{{ route('admin.members.show', $member->id) }}" 
                               class="flex items-center gap-3 p-3 hover:bg-slate-700/50 transition-colors border-b border-slate-700/50">
                                <div class="w-9 h-9 rounded-full bg-amber-500/20 flex items-center justify-center flex-shrink-0">
                                    <span class="material-symbols-outlined text-amber-500 text-lg">schedule</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-white text-sm font-medium truncate">{{ $member->name }}</p>
                                    <p class="text-amber-400 text-xs">
                                        Membership expires {{ $member->membership_expires_at->diffForHumans() }}
                                    </p>
                                </div>
                            </a>
                        @endforeach
                        
                        <!-- New Signups -->
                        @foreach($newSignupsToday as $signup)
                            <a href="{{ route('admin.members.show', $signup->id) }}" 
                               class="flex items-center gap-3 p-3 hover:bg-slate-700/50 transition-colors border-b border-slate-700/50">
                                <div class="w-9 h-9 rounded-full bg-emerald-500/20 flex items-center justify-center flex-shrink-0">
                                    <span class="material-symbols-outlined text-emerald-500 text-lg">person_add</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-white text-sm font-medium truncate">{{ $signup->name }}</p>
                                    <p class="text-emerald-400 text-xs">
                                        New signup {{ $signup->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </a>
                        @endforeach
                    @endif
                </div>
                
                @if($notificationCount > 0)
                    <div class="p-2 border-t border-slate-700">
                        <a href="{{ route('admin.members') }}" class="block text-center text-blue-400 hover:text-blue-300 text-xs py-1">
                            View All Members
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Overview Title -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">Overview</h2>
            <p class="text-slate-400 text-sm">Today, {{ now()->format('M d') }}</p>
        </div>
        <button class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-800/60 text-slate-400 hover:text-white transition-colors text-sm">
            Filter
            <span class="material-symbols-outlined text-lg">tune</span>
        </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 gap-3">
        <div class="glass rounded-2xl p-4 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
            <div class="relative z-10">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-8 h-8 rounded-lg bg-blue-500/20 flex items-center justify-center">
                        <span class="material-symbols-outlined text-blue-500 text-lg">groups</span>
                    </div>
                    <span class="text-emerald-400 text-xs font-medium flex items-center gap-0.5">
                        <span class="material-symbols-outlined text-xs">trending_up</span> 5%
                    </span>
                </div>
                <p class="text-xs text-slate-400 uppercase tracking-wider">Total Members</p>
                <p class="text-3xl font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">{{ $totalMembers }}</p>
            </div>
        </div>
        
        <div class="glass rounded-2xl p-4 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
            <div class="relative z-10">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/20 flex items-center justify-center">
                        <span class="material-symbols-outlined text-emerald-500 text-lg">event_available</span>
                    </div>
                    <span class="text-emerald-400 text-xs font-medium flex items-center gap-0.5">
                        <span class="material-symbols-outlined text-xs">trending_up</span> 12
                    </span>
                </div>
                <p class="text-xs text-slate-400 uppercase tracking-wider">Active Bookings</p>
                <p class="text-3xl font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">{{ $activeBookings }}</p>
            </div>
        </div>
    </div>

    <!-- Pending Payments Card -->
    @if($pendingPayments->count() > 0)
        <div class="glass rounded-2xl p-5 relative overflow-hidden border-l-4 border-l-amber-500">
            <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
            <div class="relative z-10">
                <div class="flex justify-between items-start mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-amber-500/20 flex items-center justify-center">
                            <span class="material-symbols-outlined text-amber-500">pending_actions</span>
                        </div>
                        <div>
                            <h3 class="text-white font-semibold">Pending Payments</h3>
                            <p class="text-amber-400 text-xs">{{ $pendingPayments->count() }} payment(s) awaiting verification</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.payments') }}" class="text-amber-400 hover:text-amber-300 text-xs font-bold uppercase">
                        Review
                    </a>
                </div>
                
                <div class="space-y-2">
                    @foreach($pendingPayments->take(3) as $payment)
                        <a href="{{ route('admin.members.show', $payment->user->id) }}" 
                           class="flex items-center justify-between p-3 rounded-lg bg-slate-800/50 hover:bg-slate-700/50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full overflow-hidden bg-slate-700 flex-shrink-0">
                                    @if($payment->user->avatar)
                                        <img src="{{ $payment->user->avatar }}" alt="" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-slate-400 text-xs font-bold">
                                            {{ substr($payment->user->name, 0, 1) }}
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-white text-sm font-medium">{{ $payment->user->name }}</p>
                                    <p class="text-slate-500 text-xs">{{ $payment->month }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-amber-400 font-bold">NT${{ number_format($payment->amount) }}</p>
                                <p class="text-slate-500 text-xs">{{ $payment->submitted_at?->diffForHumans(null, true) ?? 'Just now' }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
                
                @if($pendingPayments->count() > 3)
                    <a href="{{ route('admin.payments') }}" class="block text-center text-amber-400 hover:text-amber-300 text-xs mt-3 py-2">
                        View all {{ $pendingPayments->count() }} pending payments →
                    </a>
                @endif
            </div>
        </div>
    @endif

    <!-- Today's Attendance Card -->
    <div class="glass rounded-2xl p-5 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
        <div class="relative z-10">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-white font-semibold">Today's Attendance</h3>
                    <p class="text-slate-500 text-xs">Peak: {{ $peakHoursText }}</p>
                </div>
                <div class="text-right">
                    <p class="text-3xl font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">{{ $todayCheckIns }}</p>
                    <p class="text-slate-500 text-xs uppercase">Bookings</p>
                </div>
            </div>
            
            <!-- Chart Visualization -->
            <div class="relative h-32 flex items-end justify-between gap-0.5 mt-4 border-b border-slate-700/50 pb-2">
                @php
                    $maxHeight = collect($hourlyData)->max('height');
                    $peakIndex = collect($hourlyData)->search(function($item) use ($maxHeight) {
                        return $item['height'] == $maxHeight && $item['count'] > 0;
                    });
                @endphp
                @foreach($hourlyData as $index => $data)
                    @php
                        $isPeak = $data['height'] == $maxHeight && $data['count'] > 0;
                        $hasClasses = $data['count'] > 0;
                    @endphp
                    <div class="flex-1 flex flex-col items-center group relative">
                        <div class="w-full rounded-t transition-all duration-300 min-h-[2px]
                            {{ $hasClasses ? ($isPeak ? 'bg-gradient-to-t from-cyan-500 to-cyan-400' : 'bg-blue-500/70') : 'bg-slate-700/50' }}" 
                             style="height: {{ max($data['height'], 2) }}%"></div>
                        
                        @if($hasClasses)
                            <!-- Tooltip on hover -->
                            <div class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 bg-slate-800 px-2 py-1 rounded text-[10px] text-white border border-slate-700 whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity z-30 pointer-events-none">
                                @foreach($data['classes'] as $class)
                                    <div>{{ $class['time'] }}: {{ $class['count'] }} booked</div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
                
                @if($peakIndex !== false && $hourlyData[$peakIndex]['count'] > 0)
                    <!-- Peak indicator tooltip -->
                    @php
                        $peakData = $hourlyData[$peakIndex];
                        $peakClass = $peakData['classes'][0] ?? null;
                    @endphp
                    @if($peakClass)
                        <div class="absolute top-0 left-1/2 -translate-x-1/2 bg-slate-800 px-2 py-1 rounded text-[10px] text-slate-300 border border-slate-700 whitespace-nowrap">
                            {{ $peakClass['time'] }}: {{ $peakData['count'] }} students
                        </div>
                    @endif
                @endif
            </div>
            
            <!-- Time labels -->
            <div class="flex justify-between text-[10px] text-slate-500 mt-1">
                <span>6am</span>
                <span>9am</span>
                <span>12pm</span>
                <span>3pm</span>
                <span>6pm</span>
                <span>9pm</span>
            </div>
            
            @if($todayCheckIns == 0)
                <p class="text-center text-slate-500 text-xs mt-3">No classes scheduled for today</p>
            @endif
        </div>
    </div>

    <!-- Live Feed -->
    <div>
        <div class="flex justify-between items-center mb-3">
            <h3 class="text-white font-semibold">Live Feed</h3>
            <a href="{{ route('admin.members') }}" class="text-blue-500 text-sm hover:text-blue-400">View All</a>
        </div>
        
        <div class="space-y-3">
            @foreach($recentActivity as $activity)
                <a href="{{ route('admin.members.show', $activity->user->id) }}" 
                   class="flex items-center gap-3 p-3 rounded-xl bg-slate-800/40 border border-slate-700/30 hover:bg-slate-700/50 transition-colors">
                    <div class="w-10 h-10 rounded-full overflow-hidden bg-slate-700 flex-shrink-0">
                        @if($activity->user->avatar)
                            <img src="{{ $activity->user->avatar }}" alt="" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-slate-400 text-sm font-bold">
                                {{ substr($activity->user->name, 0, 1) }}
                            </div>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-white text-sm font-medium truncate">{{ $activity->user->name }}</p>
                        <p class="text-slate-500 text-xs truncate">Checked in - {{ $activity->classSession->title ?? 'Class' }}</p>
                    </div>
                    <span class="text-slate-500 text-xs flex-shrink-0">{{ $activity->booked_at->diffForHumans(null, true) }}</span>
                </a>
            @endforeach

            @foreach($recentSignups as $signup)
                <a href="{{ route('admin.members.show', $signup->id) }}" 
                   class="flex items-center gap-3 p-3 rounded-xl bg-slate-800/40 border border-emerald-500/30 hover:bg-slate-700/50 transition-colors">
                    <div class="w-10 h-10 rounded-full overflow-hidden bg-gradient-to-br from-emerald-500 to-teal-500 flex-shrink-0 flex items-center justify-center">
                        <span class="material-symbols-outlined text-white text-lg">person_add</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-white text-sm font-medium truncate">New Signup</p>
                        <p class="text-slate-500 text-xs truncate">{{ $signup->name }} joined</p>
                    </div>
                    <span class="text-emerald-400 text-xs flex-shrink-0">{{ $signup->created_at->diffForHumans(null, true) }}</span>
                </a>
            @endforeach

            @foreach($pendingPayments->take(5) as $payment)
                <a href="{{ route('admin.members.show', $payment->user->id) }}" 
                   class="flex items-center gap-3 p-3 rounded-xl bg-slate-800/40 border border-blue-500/30 hover:bg-slate-700/50 transition-colors">
                    <div class="w-10 h-10 rounded-full overflow-hidden bg-gradient-to-br from-blue-500 to-indigo-500 flex-shrink-0 flex items-center justify-center">
                        <span class="material-symbols-outlined text-white text-lg">payments</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-white text-sm font-medium truncate">Payment Pending</p>
                        <p class="text-slate-500 text-xs truncate">{{ $payment->user->name }} - NT${{ number_format($payment->amount) }}</p>
                    </div>
                    <span class="text-blue-400 text-xs flex-shrink-0">{{ $payment->submitted_at ? $payment->submitted_at->diffForHumans(null, true) : 'New' }}</span>
                </a>
            @endforeach
            
            @if($recentActivity->isEmpty() && $recentSignups->isEmpty() && $pendingPayments->isEmpty())
                <div class="text-center py-6 text-slate-500 text-sm">
                    No recent activity
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
