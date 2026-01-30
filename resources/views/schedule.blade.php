@extends('layouts.app')

@section('title', 'Schedule')

@section('content')
<div class="space-y-6">
    <!-- Header with Filter -->
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-white uppercase tracking-wide" style="font-family: 'Bebas Neue', sans-serif;">Schedule</h2>
        <div class="flex bg-slate-800 rounded-lg p-1">
            @foreach(['All', 'Gi', 'No-Gi'] as $filter)
                <a href="{{ route('schedule', ['filter' => $filter]) }}"
                   class="px-3 py-1 text-xs font-bold rounded-md transition-all {{ $currentFilter === $filter ? 'bg-blue-500 text-white shadow-md' : 'text-slate-400 hover:text-white' }}">
                    {{ $filter }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- Class List -->
    <div class="space-y-4">
        @forelse($classes as $class)
            @php
                $isFull = $class->bookings_count >= $class->capacity;
                $capacityPercent = ($class->bookings_count / $class->capacity) * 100;
                $isBooked = $class->is_booked_by_user;
                
                // Capacity bar color
                if ($capacityPercent >= 100) {
                    $capacityColor = 'bg-red-500';
                } elseif ($capacityPercent >= 80) {
                    $capacityColor = 'bg-amber-500';
                } else {
                    $capacityColor = 'bg-emerald-500';
                }
            @endphp
            
            <div class="glass rounded-2xl p-5 relative overflow-hidden transition-all duration-300 hover:bg-slate-800/60">
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
                        <div class="text-right">
                            <span class="inline-block px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider border 
                                {{ str_contains($class->type, 'No-Gi') ? 'bg-purple-500/10 text-purple-400 border-purple-500/20' : 'bg-blue-500/10 text-blue-400 border-blue-500/20' }}">
                                {{ $class->type }}
                            </span>
                        </div>
                    </div>

                    <!-- Title & Instructor -->
                    <div class="mb-4">
                        <h3 class="text-lg font-bold text-slate-100">{{ $class->title }}</h3>
                        <p class="text-sm text-slate-400">Instr: {{ $class->instructor_name }}</p>
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

                    <!-- Action Button -->
                    @if($isBooked)
                        <form action="{{ route('book.destroy', $class->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full py-2.5 rounded border border-red-500/50 text-red-400 font-bold text-sm hover:bg-red-500/10 transition-colors uppercase tracking-wide">
                                Cancel Booking
                            </button>
                        </form>
                    @else
                        <form action="{{ route('book.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="class_id" value="{{ $class->id }}">
                            <button type="submit" {{ $isFull ? 'disabled' : '' }}
                                class="w-full py-2.5 rounded font-bold text-sm uppercase tracking-wide transition-all shadow-lg 
                                {{ $isFull ? 'bg-slate-700 text-slate-500 cursor-not-allowed' : 'bg-blue-500 hover:bg-blue-600 text-white shadow-blue-500/20' }}">
                                {{ $isFull ? 'Class Full' : 'Book Class' }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="p-10 text-center text-slate-500 bg-slate-900/50 rounded-xl border border-dashed border-slate-700">
                <span class="material-symbols-outlined text-4xl mb-2">event_busy</span>
                <p>No upcoming classes found.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
