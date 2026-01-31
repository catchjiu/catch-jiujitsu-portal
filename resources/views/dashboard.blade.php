@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Welcome Header -->
    <div class="space-y-1">
        <h2 class="text-2xl font-bold text-white uppercase tracking-wide" style="font-family: 'Bebas Neue', sans-serif;">
            Welcome Back, <span class="text-blue-500">{{ explode(' ', $user->name)[0] }}</span>
        </h2>
        <p class="text-slate-400 text-sm">Ready to hit the mats?</p>
    </div>

    <!-- Rank Card -->
    <div class="glass rounded-2xl p-5 border-t-4 border-t-amber-500 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
        <div class="relative z-10">
            <div class="flex justify-between items-end">
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-widest font-bold mb-1">Current Rank</p>
                    <h3 class="text-3xl font-bold text-white uppercase" style="font-family: 'Bebas Neue', sans-serif;">{{ $user->rank }} Belt</h3>
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
            @php
                $beltColors = [
                    'White' => 'bg-gray-100 border-gray-300',
                    'Grey' => 'bg-slate-400 border-slate-300',
                    'Yellow' => 'bg-yellow-400 border-yellow-300',
                    'Orange' => 'bg-orange-500 border-orange-400',
                    'Green' => 'bg-green-500 border-green-400',
                    'Blue' => 'bg-blue-600 border-blue-400',
                    'Purple' => 'bg-purple-600 border-purple-400',
                    'Brown' => 'bg-yellow-900 border-yellow-700',
                    'Black' => 'bg-black border-black',
                ];
                $beltClass = $beltColors[$user->rank] ?? 'bg-gray-100';
                $isBlackBelt = $user->rank === 'Black';
            @endphp
            <div class="mt-4 h-8 w-full rounded shadow-inner relative flex items-center justify-end pr-4 {{ $beltClass }}">
                @if($isBlackBelt)
                    <!-- Red bar for black belt -->
                    <div class="h-full w-16 bg-red-600 flex items-center justify-around px-1 absolute right-4">
                        @for ($i = 0; $i < $user->stripes; $i++)
                            <div class="w-1.5 h-full bg-white shadow-sm"></div>
                        @endfor
                    </div>
                @else
                    <!-- Black bar for stripes -->
                    <div class="h-full w-16 bg-black flex items-center justify-around px-1 absolute right-4">
                        @for ($i = 0; $i < $user->stripes; $i++)
                            <div class="w-1.5 h-full bg-white shadow-sm"></div>
                        @endfor
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Membership Card -->
    <div class="glass rounded-2xl p-5 border-t-4 {{ $user->membership_status === 'active' ? 'border-t-emerald-500' : ($user->membership_status === 'pending' ? 'border-t-amber-500' : 'border-t-red-500') }} relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
        <div class="relative z-10">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-widest font-bold mb-1">Membership</p>
                    @if($user->membershipPackage)
                        <h3 class="text-2xl font-bold text-white uppercase" style="font-family: 'Bebas Neue', sans-serif;">{{ $user->membershipPackage->name }}</h3>
                    @else
                        <h3 class="text-2xl font-bold text-slate-500 uppercase" style="font-family: 'Bebas Neue', sans-serif;">No Package</h3>
                    @endif
                </div>
                <div class="text-right">
                    @php
                        $statusColors = [
                            'active' => 'bg-emerald-500/20 text-emerald-400',
                            'pending' => 'bg-amber-500/20 text-amber-400',
                            'expired' => 'bg-red-500/20 text-red-400',
                            'none' => 'bg-slate-700/50 text-slate-400',
                        ];
                        $statusColor = $statusColors[$user->membership_status] ?? $statusColors['none'];
                    @endphp
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase {{ $statusColor }}">
                        {{ $user->membership_status ?? 'None' }}
                    </span>
                </div>
            </div>
            
            <!-- Membership Details -->
            <div class="mt-4 p-3 rounded-lg bg-slate-800/50 border border-slate-700/50">
                @if($user->membership_status === 'active')
                    @if($user->membershipPackage && $user->membershipPackage->duration_type === 'classes')
                        <div class="flex justify-between items-center">
                            <span class="text-slate-400 text-sm">Classes Remaining</span>
                            <span class="text-white font-bold text-lg" style="font-family: 'Bebas Neue', sans-serif;">{{ $user->classes_remaining ?? 0 }}</span>
                        </div>
                    @elseif($user->membership_expires_at)
                        <div class="flex justify-between items-center">
                            <span class="text-slate-400 text-sm">Valid Until</span>
                            <span class="text-white font-bold" style="font-family: 'Bebas Neue', sans-serif;">{{ $user->membership_expires_at->format('M d, Y') }}</span>
                        </div>
                    @else
                        <p class="text-slate-400 text-sm text-center">Unlimited access</p>
                    @endif
                @elseif($user->membership_status === 'pending')
                    <p class="text-amber-400 text-sm text-center">
                        <span class="material-symbols-outlined text-sm align-middle mr-1">hourglass_top</span>
                        Payment pending verification
                    </p>
                @elseif($user->membership_status === 'expired')
                    <p class="text-red-400 text-sm text-center">
                        <span class="material-symbols-outlined text-sm align-middle mr-1">warning</span>
                        Membership expired. Please renew.
                    </p>
                @else
                    <p class="text-slate-500 text-sm text-center">
                        Contact the gym to get started.
                    </p>
                @endif
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 gap-4">
        <div class="glass rounded-2xl p-5 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
            <div class="relative z-10 flex flex-col items-center justify-center py-2">
                <span class="text-4xl font-bold text-amber-500" style="font-family: 'Bebas Neue', sans-serif;">{{ $user->mat_hours }}</span>
                <span class="text-xs text-slate-400 uppercase tracking-wider mt-1">Mat Hours</span>
            </div>
        </div>
        <div class="glass rounded-2xl p-5 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
            <div class="relative z-10 flex flex-col items-center justify-center py-2">
                <span class="text-4xl font-bold text-blue-500" style="font-family: 'Bebas Neue', sans-serif;">{{ $classesThisMonth }}</span>
                <span class="text-xs text-slate-400 uppercase tracking-wider mt-1">Classes / Mo</span>
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
                        MONTHLY GOALS
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
                            <span class="text-slate-400">Classes</span>
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
                    <h3 class="text-white font-semibold">Leaderboard</h3>
                    <p class="text-slate-500 text-xs">See top trainers this month</p>
                </div>
                <span class="material-symbols-outlined text-slate-600">chevron_right</span>
            </div>
        </div>
    </a>

    <!-- Next Class -->
    <div>
        <h3 class="text-lg font-bold text-white mb-3 flex items-center gap-2" style="font-family: 'Bebas Neue', sans-serif;">
            <span class="material-symbols-outlined text-amber-500">event</span>
            MY NEXT CLASS
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
                    <h4 class="text-xl font-bold text-white mb-1">{{ $nextClass->title }}</h4>
                    <p class="text-slate-400 text-sm mb-4">Instructor: {{ $nextClass->instructor_name }}</p>
                    <div class="text-center text-slate-500 text-xs">
                        {{ $nextClass->start_time->format('l, F j') }}
                    </div>
                </div>
            </div>
        @else
            <div class="p-6 rounded-2xl border-2 border-dashed border-slate-700 text-center text-slate-500">
                <p>No upcoming classes booked.</p>
                <a href="{{ route('schedule') }}" class="text-blue-400 hover:text-blue-300 text-sm mt-2 inline-block">Browse schedule</a>
            </div>
        @endif
    </div>
</div>
@endsection
