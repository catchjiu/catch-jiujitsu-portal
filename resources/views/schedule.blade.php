@extends('layouts.app')

@section('title', 'Schedule')

@section('content')
<div class="space-y-5">
    <!-- Membership Status Banner -->
    @if(!Auth::user()->hasActiveMembership())
        <div class="rounded-xl p-4 bg-amber-500/10 border border-amber-500/20">
            <div class="flex items-start gap-3">
                <span class="material-symbols-outlined text-amber-500 text-lg flex-shrink-0 mt-0.5">warning</span>
                <div>
                    <p class="text-amber-400 font-semibold text-sm">{{ Auth::user()->membership_issue ?? 'Membership Required' }}</p>
                    <p class="text-slate-400 text-xs mt-1">Contact the gym to activate your membership.</p>
                </div>
            </div>
        </div>
    @else
        @if(Auth::user()->membershipPackage && Auth::user()->membershipPackage->duration_type === 'classes' && Auth::user()->classes_remaining !== null)
            <div class="rounded-xl p-3 bg-slate-800/50 border border-slate-700/50 flex items-center justify-between">
                <span class="text-slate-400 text-sm">Classes Remaining</span>
                <span class="text-emerald-400 font-bold">{{ Auth::user()->classes_remaining }}</span>
            </div>
        @elseif(Auth::user()->membership_expires_at)
            <div class="rounded-xl p-3 bg-slate-800/50 border border-slate-700/50 flex items-center justify-between">
                <span class="text-slate-400 text-sm">Membership Expires</span>
                <span class="text-emerald-400 font-bold">{{ Auth::user()->membership_expires_at->format('M d, Y') }}</span>
            </div>
        @endif
    @endif

    <!-- Header with Filter -->
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-white uppercase tracking-wide" style="font-family: 'Bebas Neue', sans-serif;">Schedule</h2>
        <div class="flex bg-slate-800 rounded-lg p-1">
            @foreach(['All', 'Adults', 'Kids'] as $filter)
                <a href="{{ route('schedule', ['filter' => $filter, 'date' => $selectedDate->format('Y-m-d')]) }}"
                   class="px-3 py-1 text-xs font-bold rounded-md transition-all {{ $currentFilter === $filter ? 'bg-blue-500 text-white shadow-md' : 'text-slate-400 hover:text-white' }}">
                    {{ $filter }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- Week Navigation -->
    <div class="flex items-center justify-between">
        <a href="{{ route('schedule', ['date' => $prevWeek->format('Y-m-d'), 'filter' => $currentFilter]) }}" 
           class="w-10 h-10 rounded-full bg-slate-800/60 flex items-center justify-center text-slate-400 hover:text-white hover:bg-slate-700 transition-colors">
            <span class="material-symbols-outlined">chevron_left</span>
        </a>
        <span class="text-sm font-semibold text-slate-300">
            {{ $weekStart->format('M d') }} - {{ $weekStart->copy()->addDays(6)->format('M d, Y') }}
        </span>
        <a href="{{ route('schedule', ['date' => $nextWeek->format('Y-m-d'), 'filter' => $currentFilter]) }}" 
           class="w-10 h-10 rounded-full bg-slate-800/60 flex items-center justify-center text-slate-400 hover:text-white hover:bg-slate-700 transition-colors">
            <span class="material-symbols-outlined">chevron_right</span>
        </a>
    </div>

    <!-- Week Day Selector -->
    <div class="flex gap-1 overflow-x-auto pb-1 scrollbar-hide">
        @foreach($weekDays as $day)
            @php
                $isSelected = $selectedDate->isSameDay($day);
                $isToday = $day->isToday();
                $isPast = $day->isPast() && !$day->isToday();
            @endphp
            <a href="{{ route('schedule', ['date' => $day->format('Y-m-d'), 'filter' => $currentFilter]) }}"
               class="flex flex-col items-center justify-center flex-1 min-w-[44px] py-2 px-1 rounded-xl transition-all
               {{ $isSelected 
                   ? 'bg-blue-500 text-white' 
                   : ($isToday 
                       ? 'bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500/30' 
                       : ($isPast 
                           ? 'bg-slate-800/40 text-slate-500 hover:bg-slate-700/60' 
                           : 'bg-slate-800/60 text-slate-400 hover:bg-slate-700/60')) }}">
                <span class="text-[10px] font-bold uppercase">{{ $day->format('D') }}</span>
                <span class="text-lg font-bold" style="font-family: 'Bebas Neue', sans-serif;">{{ $day->format('d') }}</span>
                @if($isToday && !$isSelected)
                    <div class="w-1.5 h-1.5 rounded-full bg-emerald-400 mt-0.5"></div>
                @endif
            </a>
        @endforeach
    </div>

    <!-- Today Button (if not viewing current week) -->
    @if(!$selectedDate->isSameWeek(now()))
        <div class="text-center">
            <a href="{{ route('schedule', ['date' => now()->format('Y-m-d'), 'filter' => $currentFilter]) }}" 
               class="inline-block px-4 py-2 text-xs font-semibold rounded-lg bg-slate-700/50 text-slate-300 hover:bg-slate-700 transition-colors">
                Back to Today
            </a>
        </div>
    @endif

    <!-- Class List -->
    <div class="space-y-4">
        @forelse($classes as $class)
            @php
                $isFull = $class->bookings_count >= $class->capacity;
                $capacityPercent = ($class->bookings_count / $class->capacity) * 100;
                $isBooked = $class->is_booked_by_user;
                $isPastClass = $class->start_time->isPast();
                
                // Capacity bar color
                if ($capacityPercent >= 100) {
                    $capacityColor = 'bg-red-500';
                } elseif ($capacityPercent >= 80) {
                    $capacityColor = 'bg-amber-500';
                } else {
                    $capacityColor = 'bg-emerald-500';
                }
            @endphp
            
            <div class="glass rounded-2xl p-5 relative overflow-hidden transition-all duration-300 {{ $isPastClass ? 'opacity-50' : 'hover:bg-slate-800/60' }}">
                <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
                <div class="relative z-10">
                    <!-- Date & Type Row -->
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex flex-col">
                            <span class="text-amber-500 text-xs font-bold uppercase tracking-wider mb-0.5">
                                {{ $class->start_time->format('l') }}
                            </span>
                            <span class="text-3xl font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">
                                {{ $class->start_time->format('H:i') }}
                            </span>
                            <span class="text-slate-400 text-xs">{{ $class->duration_minutes }} Minutes</span>
                        </div>
                        <div class="text-right flex flex-col items-end gap-1">
                            <span class="inline-block px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider border 
                                {{ str_contains($class->type, 'No-Gi') ? 'bg-purple-500/10 text-purple-400 border-purple-500/20' : 'bg-blue-500/10 text-blue-400 border-blue-500/20' }}">
                                {{ $class->type }}
                            </span>
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider 
                                {{ ($class->age_group ?? 'Adults') === 'Kids' ? 'bg-emerald-500/20 text-emerald-400' : (($class->age_group ?? 'Adults') === 'All' ? 'bg-blue-500/20 text-blue-400' : 'bg-slate-700/50 text-slate-400') }}">
                                {{ $class->age_group ?? 'Adults' }}
                            </span>
                        </div>
                    </div>

                    <!-- Title & Instructor -->
                    <div class="mb-4">
                        <h3 class="text-lg font-bold text-slate-100">{{ $class->title }}</h3>
                        @if($class->instructor)
                            <div class="flex items-center gap-2 mt-2">
                                <div class="w-8 h-8 rounded-full overflow-hidden bg-slate-700 border-2 border-slate-600 flex-shrink-0">
                                    @if($class->instructor->avatar)
                                        <img src="{{ $class->instructor->avatar }}" alt="{{ $class->instructor->name }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-slate-400 text-xs font-bold">
                                            {{ substr($class->instructor->first_name, 0, 1) }}{{ substr($class->instructor->last_name, 0, 1) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm text-slate-300">{{ $class->instructor->name }}</span>
                                    @php
                                        $instructorBeltColors = [
                                            'White' => 'bg-gray-200',
                                            'Blue' => 'bg-blue-600',
                                            'Purple' => 'bg-purple-600',
                                            'Brown' => 'bg-yellow-800',
                                            'Black' => 'bg-black border border-slate-600',
                                        ];
                                    @endphp
                                    <div class="w-6 h-3 rounded-sm {{ $instructorBeltColors[$class->instructor->rank] ?? 'bg-gray-200' }}"></div>
                                </div>
                            </div>
                        @else
                            <p class="text-sm text-slate-400">Instructor: {{ $class->instructor_display_name }}</p>
                        @endif
                    </div>

                    <!-- Capacity Bar -->
                    <div class="mb-4">
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-slate-400">Mat Capacity</span>
                            <span class="{{ $isFull ? 'text-red-400' : 'text-slate-300' }}">
                                {{ $class->bookings_count }} / {{ $class->capacity }}
                            </span>
                        </div>
                        <div class="w-full h-2 bg-slate-700 rounded-full overflow-hidden">
                            <div class="h-full transition-all duration-500 {{ $capacityColor }}" style="width: {{ min($capacityPercent, 100) }}%"></div>
                        </div>
                    </div>

                    <!-- Coach View Attendance Button -->
                    @if(Auth::user()->isCoach())
                        <a href="{{ route('class.attendance', $class->id) }}" 
                           class="w-full py-2.5 mb-2 rounded bg-amber-500/20 text-amber-400 font-bold text-sm text-center uppercase tracking-wide hover:bg-amber-500/30 transition-colors flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-lg">groups</span>
                            View Attendance ({{ $class->bookings_count }})
                        </a>
                    @endif

                    <!-- Action Button -->
                    @if($isPastClass)
                        <div class="w-full py-2.5 rounded bg-slate-700 text-slate-500 font-bold text-sm text-center uppercase tracking-wide">
                            Class Ended
                        </div>
                    @elseif($isBooked)
                        <form action="{{ route('book.destroy', $class->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full py-2.5 rounded border border-red-500/50 text-red-400 font-bold text-sm hover:bg-red-500/10 transition-colors uppercase tracking-wide">
                                Cancel Booking
                            </button>
                        </form>
                    @else
                        @php
                            $canBook = Auth::user()->hasActiveMembership() && !$isFull;
                            $buttonText = $isFull ? 'Class Full' : (!Auth::user()->hasActiveMembership() ? 'Membership Required' : 'Book Class');
                        @endphp
                        <form action="{{ route('book.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="class_id" value="{{ $class->id }}">
                            <button type="submit" {{ !$canBook ? 'disabled' : '' }}
                                class="w-full py-2.5 rounded font-bold text-sm uppercase tracking-wide transition-all shadow-lg 
                                {{ !$canBook ? 'bg-slate-700 text-slate-500 cursor-not-allowed' : 'bg-blue-500 hover:bg-blue-600 text-white shadow-blue-500/20' }}">
                                {{ $buttonText }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="p-10 text-center text-slate-500 bg-slate-900/50 rounded-xl border border-dashed border-slate-700">
                <span class="material-symbols-outlined text-4xl mb-2">event_busy</span>
                <p>No classes scheduled for {{ $selectedDate->format('l, M j') }}.</p>
                <p class="text-xs mt-2">Try selecting a different day or changing the filter.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
