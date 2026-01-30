@extends('layouts.app')

@section('title', 'Leaderboard')

@section('content')
<div class="space-y-5">
    <!-- Header -->
    <div class="flex items-center gap-3">
        <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-white transition-colors">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h1 class="text-xl font-bold text-white">Leaderboard</h1>
    </div>

    <!-- Tab Selector -->
    <div class="flex gap-2 p-1 bg-slate-800/60 rounded-xl">
        <a href="{{ route('leaderboard', ['tab' => 'hours']) }}"
           class="flex-1 py-2.5 text-center text-sm font-semibold rounded-lg transition-all
           {{ $tab === 'hours' ? 'bg-blue-500 text-white shadow-lg' : 'text-slate-400 hover:text-white' }}">
            <span class="material-symbols-outlined text-sm align-middle mr-1">schedule</span>
            Hours This Year
        </a>
        <a href="{{ route('leaderboard', ['tab' => 'classes']) }}"
           class="flex-1 py-2.5 text-center text-sm font-semibold rounded-lg transition-all
           {{ $tab === 'classes' ? 'bg-blue-500 text-white shadow-lg' : 'text-slate-400 hover:text-white' }}">
            <span class="material-symbols-outlined text-sm align-middle mr-1">event_available</span>
            Classes This Month
        </a>
    </div>

    <!-- Your Rank Card -->
    @if($currentUser)
        <div class="glass rounded-2xl p-4 border-l-4 border-l-blue-500 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
            <div class="relative z-10 flex items-center gap-4">
                <div class="w-12 h-12 rounded-full overflow-hidden bg-slate-700 border-2 border-blue-500 flex-shrink-0">
                    @if($currentUser->avatar_url)
                        <img src="{{ $currentUser->avatar_url }}" alt="" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-slate-400 font-bold">
                            {{ strtoupper(substr($currentUser->name, 0, 2)) }}
                        </div>
                    @endif
                </div>
                <div class="flex-1">
                    <p class="text-xs text-slate-400 uppercase tracking-wider">Your Rank</p>
                    <p class="text-white font-semibold">{{ $currentUser->name }}</p>
                </div>
                <div class="text-right">
                    @if($tab === 'hours')
                        <p class="text-2xl font-bold text-blue-500" style="font-family: 'Bebas Neue', sans-serif;">
                            #{{ $myHoursRank ?? '-' }}
                        </p>
                        <p class="text-xs text-slate-400">{{ $myHours }} hrs</p>
                    @else
                        <p class="text-2xl font-bold text-blue-500" style="font-family: 'Bebas Neue', sans-serif;">
                            #{{ $myClassesRank ?? '-' }}
                        </p>
                        <p class="text-xs text-slate-400">{{ $myClasses }} classes</p>
                    @endif
                </div>
            </div>
            @if(!$currentUser->public_profile)
                <p class="text-xs text-slate-500 mt-3 flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">visibility_off</span>
                    Your profile is private. <a href="{{ route('goals') }}" class="text-blue-400 hover:underline">Enable public profile</a> to appear on the leaderboard.
                </p>
            @endif
        </div>
    @endif

    <!-- Leaderboard List -->
    @if($tab === 'hours')
        <!-- Hours Leaderboard -->
        <div class="space-y-2">
            @forelse($hoursLeaderboard as $index => $entry)
                @php
                    $rank = $index + 1;
                    $isTop3 = $rank <= 3;
                    $medalColors = [1 => 'text-amber-400', 2 => 'text-slate-300', 3 => 'text-amber-700'];
                    $isCurrentUser = $currentUser && $entry['user']->id === $currentUser->id;
                @endphp
                <div class="flex items-center gap-3 p-3 rounded-xl {{ $isCurrentUser ? 'bg-blue-500/10 border border-blue-500/30' : 'bg-slate-800/40 border border-slate-700/30' }}">
                    <!-- Rank -->
                    <div class="w-8 text-center flex-shrink-0">
                        @if($isTop3)
                            <span class="material-symbols-outlined {{ $medalColors[$rank] }}" style="font-size: 28px;">emoji_events</span>
                        @else
                            <span class="text-lg font-bold text-slate-500">{{ $rank }}</span>
                        @endif
                    </div>

                    <!-- Avatar -->
                    <div class="w-10 h-10 rounded-full overflow-hidden bg-slate-700 flex-shrink-0 {{ $isTop3 ? 'ring-2 ring-offset-2 ring-offset-slate-900 ' . ($rank === 1 ? 'ring-amber-400' : ($rank === 2 ? 'ring-slate-300' : 'ring-amber-700')) : '' }}">
                        @if($entry['user']->avatar_url)
                            <img src="{{ $entry['user']->avatar_url }}" alt="" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-slate-400 text-sm font-bold">
                                {{ strtoupper(substr($entry['user']->name, 0, 2)) }}
                            </div>
                        @endif
                    </div>

                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <p class="text-white font-medium truncate {{ $isCurrentUser ? 'text-blue-400' : '' }}">
                            {{ $entry['user']->name }}
                            @if($isCurrentUser)
                                <span class="text-blue-400 text-xs">(You)</span>
                            @endif
                        </p>
                        <p class="text-slate-500 text-xs">{{ $entry['user']->rank }} Belt</p>
                    </div>

                    <!-- Hours -->
                    <div class="text-right flex-shrink-0">
                        <p class="text-xl font-bold {{ $isTop3 ? 'text-white' : 'text-slate-300' }}" style="font-family: 'Bebas Neue', sans-serif;">
                            {{ $entry['hours'] }}
                        </p>
                        <p class="text-[10px] text-slate-500 uppercase">hours</p>
                    </div>
                </div>
            @empty
                <div class="p-10 text-center text-slate-500 bg-slate-900/50 rounded-xl border border-dashed border-slate-700">
                    <span class="material-symbols-outlined text-4xl mb-2">leaderboard</span>
                    <p>No public profiles yet.</p>
                    <p class="text-sm mt-1">Be the first to enable your public profile!</p>
                </div>
            @endforelse
        </div>
    @else
        <!-- Classes Leaderboard -->
        <div class="space-y-2">
            @forelse($classesLeaderboard as $index => $entry)
                @php
                    $rank = $index + 1;
                    $isTop3 = $rank <= 3;
                    $medalColors = [1 => 'text-amber-400', 2 => 'text-slate-300', 3 => 'text-amber-700'];
                    $isCurrentUser = $currentUser && $entry['user']->id === $currentUser->id;
                @endphp
                <div class="flex items-center gap-3 p-3 rounded-xl {{ $isCurrentUser ? 'bg-blue-500/10 border border-blue-500/30' : 'bg-slate-800/40 border border-slate-700/30' }}">
                    <!-- Rank -->
                    <div class="w-8 text-center flex-shrink-0">
                        @if($isTop3)
                            <span class="material-symbols-outlined {{ $medalColors[$rank] }}" style="font-size: 28px;">emoji_events</span>
                        @else
                            <span class="text-lg font-bold text-slate-500">{{ $rank }}</span>
                        @endif
                    </div>

                    <!-- Avatar -->
                    <div class="w-10 h-10 rounded-full overflow-hidden bg-slate-700 flex-shrink-0 {{ $isTop3 ? 'ring-2 ring-offset-2 ring-offset-slate-900 ' . ($rank === 1 ? 'ring-amber-400' : ($rank === 2 ? 'ring-slate-300' : 'ring-amber-700')) : '' }}">
                        @if($entry['user']->avatar_url)
                            <img src="{{ $entry['user']->avatar_url }}" alt="" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-slate-400 text-sm font-bold">
                                {{ strtoupper(substr($entry['user']->name, 0, 2)) }}
                            </div>
                        @endif
                    </div>

                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <p class="text-white font-medium truncate {{ $isCurrentUser ? 'text-blue-400' : '' }}">
                            {{ $entry['user']->name }}
                            @if($isCurrentUser)
                                <span class="text-blue-400 text-xs">(You)</span>
                            @endif
                        </p>
                        <p class="text-slate-500 text-xs">{{ $entry['user']->rank }} Belt</p>
                    </div>

                    <!-- Classes -->
                    <div class="text-right flex-shrink-0">
                        <p class="text-xl font-bold {{ $isTop3 ? 'text-white' : 'text-slate-300' }}" style="font-family: 'Bebas Neue', sans-serif;">
                            {{ $entry['classes'] }}
                        </p>
                        <p class="text-[10px] text-slate-500 uppercase">classes</p>
                    </div>
                </div>
            @empty
                <div class="p-10 text-center text-slate-500 bg-slate-900/50 rounded-xl border border-dashed border-slate-700">
                    <span class="material-symbols-outlined text-4xl mb-2">leaderboard</span>
                    <p>No public profiles yet.</p>
                    <p class="text-sm mt-1">Be the first to enable your public profile!</p>
                </div>
            @endforelse
        </div>
    @endif

    <!-- Info Note -->
    <div class="glass rounded-xl p-4 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
        <div class="relative z-10 flex items-start gap-3">
            <span class="material-symbols-outlined text-blue-500">info</span>
            <p class="text-slate-400 text-sm">
                Only members with <strong class="text-slate-300">Public Profile</strong> enabled appear on the leaderboard. 
                You can manage this in <a href="{{ route('goals') }}" class="text-blue-400 hover:underline">Goals & Settings</a>.
            </p>
        </div>
    </div>
</div>
@endsection
