@extends('layouts.app')

@section('title', 'Class Attendance')

@section('content')
<div class="space-y-5">
    <!-- Header -->
    <div class="flex items-center gap-3">
        <a href="{{ route('schedule', ['date' => $class->start_time->format('Y-m-d')]) }}" class="text-slate-400 hover:text-white transition-colors">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <div>
            <h1 class="text-xl font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">Class Attendance</h1>
            <p class="text-slate-400 text-sm">{{ $class->title }}</p>
        </div>
    </div>

    <!-- Class Info Card -->
    <div class="glass rounded-2xl p-5 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
        <div class="relative z-10">
            <div class="flex justify-between items-start mb-3">
                <div>
                    <span class="text-amber-500 text-xs font-bold uppercase tracking-wider">
                        {{ $class->start_time->format('l, M j, Y') }}
                    </span>
                    <h2 class="text-2xl font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">
                        {{ $class->start_time->format('H:i') }} - {{ $class->title }}
                    </h2>
                </div>
                <span class="px-2 py-1 rounded text-xs font-bold uppercase {{ $class->type === 'Gi' ? 'bg-blue-500/20 text-blue-400' : 'bg-purple-500/20 text-purple-400' }}">
                    {{ $class->type }}
                </span>
            </div>
            
            @if($class->instructor)
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-8 h-8 rounded-full overflow-hidden bg-slate-700">
                        @if($class->instructor->avatar)
                            <img src="{{ $class->instructor->avatar }}" alt="" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-slate-400 text-xs font-bold">
                                {{ substr($class->instructor->first_name, 0, 1) }}{{ substr($class->instructor->last_name, 0, 1) }}
                            </div>
                        @endif
                    </div>
                    <span class="text-slate-300 text-sm">Instructor: {{ $class->instructor->name }}</span>
                </div>
            @endif

            <div class="flex items-center gap-4 text-sm">
                <span class="text-slate-400">
                    <span class="material-symbols-outlined text-sm align-middle mr-1">schedule</span>
                    {{ $class->duration_minutes }} minutes
                </span>
                <span class="text-emerald-400 font-semibold">
                    <span class="material-symbols-outlined text-sm align-middle mr-1">groups</span>
                    {{ $bookings->count() }} / {{ $class->capacity }} booked
                </span>
            </div>
        </div>
    </div>

    <!-- Attendance List -->
    <div class="glass rounded-2xl p-5 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
        <div class="relative z-10">
            <h3 class="text-lg font-bold text-white mb-4" style="font-family: 'Bebas Neue', sans-serif;">
                Booked Members ({{ $bookings->count() }})
            </h3>

            @if($bookings->count() > 0)
                <div class="space-y-3">
                    @foreach($bookings as $index => $booking)
                        @php
                            $member = $booking->user;
                            $beltColors = [
                                'White' => 'bg-gray-200',
                                'Grey' => 'bg-slate-400',
                                'Yellow' => 'bg-yellow-400',
                                'Orange' => 'bg-orange-500',
                                'Green' => 'bg-green-500',
                                'Blue' => 'bg-blue-600',
                                'Purple' => 'bg-purple-600',
                                'Brown' => 'bg-yellow-800',
                                'Black' => 'bg-black',
                            ];
                        @endphp
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-800/40 border border-slate-700/30">
                            <!-- Number -->
                            <div class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center text-slate-400 font-bold text-sm flex-shrink-0">
                                {{ $index + 1 }}
                            </div>

                            <!-- Avatar -->
                            <div class="w-10 h-10 rounded-full overflow-hidden bg-slate-700 flex-shrink-0">
                                @if($member->avatar)
                                    <img src="{{ $member->avatar }}" alt="{{ $member->name }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-400 text-sm font-bold">
                                        {{ strtoupper(substr($member->first_name, 0, 1) . substr($member->last_name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>

                            <!-- Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-white font-medium truncate">{{ $member->name }}</h4>
                                    @if($member->age_group === 'Kids')
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-400">KIDS</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <!-- Belt -->
                                    <div class="w-10 h-3 rounded-sm {{ $beltColors[$member->rank] ?? 'bg-gray-200' }} relative flex items-center justify-end pr-0.5">
                                        @if($member->rank === 'Black')
                                            <div class="h-full w-4 bg-red-600 flex items-center justify-around px-0.5">
                                                @for ($i = 0; $i < $member->stripes; $i++)
                                                    <div class="w-0.5 h-full bg-white"></div>
                                                @endfor
                                            </div>
                                        @else
                                            <div class="h-full w-4 bg-black flex items-center justify-around px-0.5">
                                                @for ($i = 0; $i < $member->stripes; $i++)
                                                    <div class="w-0.5 h-full bg-white"></div>
                                                @endfor
                                            </div>
                                        @endif
                                    </div>
                                    <span class="text-slate-500 text-xs">{{ $member->rank }} Belt</span>
                                </div>
                            </div>

                            <!-- Booked Time -->
                            <div class="text-right flex-shrink-0">
                                <p class="text-slate-500 text-xs">Booked</p>
                                <p class="text-slate-400 text-xs">{{ $booking->booked_at ? \Carbon\Carbon::parse($booking->booked_at)->diffForHumans() : 'N/A' }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <span class="material-symbols-outlined text-4xl text-slate-600 mb-2">person_off</span>
                    <p class="text-slate-500">No one has booked this class yet.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
