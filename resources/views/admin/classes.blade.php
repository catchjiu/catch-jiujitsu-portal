@extends('layouts.admin')

@section('title', 'Manage Classes')

@section('content')
<div class="space-y-5">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.index') }}" class="text-slate-400 hover:text-white transition-colors">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <h1 class="text-xl font-bold text-white">Manage Classes</h1>
        </div>
        <button class="text-slate-400 hover:text-white transition-colors">
            <span class="material-symbols-outlined">calendar_month</span>
        </button>
    </div>

    <!-- Week Day Selector -->
    <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-hide">
        @foreach($weekDays as $day)
            @php
                $isSelected = $selectedDate->isSameDay($day);
                $isToday = $day->isToday();
            @endphp
            <a href="{{ route('admin.classes', ['date' => $day->format('Y-m-d')]) }}"
               class="flex flex-col items-center justify-center min-w-[56px] py-2 px-3 rounded-xl transition-all
               {{ $isSelected 
                   ? 'bg-blue-500 text-white' 
                   : 'bg-slate-800/60 text-slate-400 hover:bg-slate-700/60' }}">
                <span class="text-[10px] font-bold uppercase">{{ $day->format('D') }}</span>
                <span class="text-xl font-bold" style="font-family: 'Bebas Neue', sans-serif;">{{ $day->format('d') }}</span>
            </a>
        @endforeach
    </div>

    <!-- Add New Class Button -->
    <a href="{{ route('admin.classes.create') }}" 
       class="flex items-center justify-center gap-2 w-full py-3 rounded-xl border-2 border-dashed border-blue-500/50 text-blue-500 hover:bg-blue-500/10 transition-colors font-semibold">
        <span class="material-symbols-outlined">add</span>
        Add New Class
    </a>

    <!-- Classes by Time Period -->
    @php
        $periods = [
            'morning' => ['icon' => 'wb_sunny', 'label' => 'MORNING', 'color' => 'text-amber-500'],
            'afternoon' => ['icon' => 'wb_twilight', 'label' => 'AFTERNOON', 'color' => 'text-orange-500'],
            'evening' => ['icon' => 'nights_stay', 'label' => 'EVENING', 'color' => 'text-purple-500'],
        ];
    @endphp

    @foreach($periods as $periodKey => $period)
        @if(isset($classes[$periodKey]) && $classes[$periodKey]->count() > 0)
            <div class="space-y-3">
                <div class="flex items-center gap-2 text-slate-400">
                    <span class="material-symbols-outlined {{ $period['color'] }} text-lg">{{ $period['icon'] }}</span>
                    <span class="text-xs font-bold tracking-wider">{{ $period['label'] }}</span>
                </div>

                @foreach($classes[$periodKey] as $class)
                    @php
                        $capacityPercent = $class->capacity > 0 ? ($class->bookings_count / $class->capacity) * 100 : 0;
                        $isFull = $class->bookings_count >= $class->capacity;
                    @endphp
                    
                    <div class="glass rounded-2xl p-4 relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
                        <div class="relative z-10">
                            <div class="flex gap-4">
                                <!-- Time Column -->
                                <div class="flex flex-col items-center text-center min-w-[50px]">
                                    <span class="text-2xl font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">
                                        {{ $class->start_time->format('H:i') }}
                                    </span>
                                    <span class="text-[10px] text-slate-500 uppercase">
                                        {{ $class->start_time->format('A') }}
                                    </span>
                                </div>

                                <!-- Class Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between mb-2">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <h3 class="text-white font-semibold">{{ $class->title }}</h3>
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ ($class->age_group ?? 'Adults') === 'Kids' ? 'bg-emerald-500/20 text-emerald-400' : (($class->age_group ?? 'Adults') === 'All' ? 'bg-blue-500/20 text-blue-400' : 'bg-slate-700/50 text-slate-400') }}">
                                                    {{ $class->age_group ?? 'Adults' }}
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-2 text-slate-400 text-sm">
                                                <span class="material-symbols-outlined text-sm">person</span>
                                                {{ $class->instructor_name }}
                                            </div>
                                        </div>
                                        <a href="{{ route('admin.classes.edit', $class->id) }}" 
                                           class="w-8 h-8 rounded-full bg-slate-700/50 flex items-center justify-center text-slate-400 hover:text-white transition-colors">
                                            <span class="material-symbols-outlined text-lg">edit</span>
                                        </a>
                                    </div>

                                    <!-- Capacity Bar -->
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs text-slate-500">Capacity</span>
                                        <div class="flex-1 h-2 bg-slate-700 rounded-full overflow-hidden">
                                            <div class="h-full transition-all duration-500 rounded-full
                                                {{ $isFull ? 'bg-red-500' : ($capacityPercent > 80 ? 'bg-amber-500' : 'bg-emerald-500') }}"
                                                 style="width: {{ min($capacityPercent, 100) }}%"></div>
                                        </div>
                                        <span class="text-xs {{ $isFull ? 'text-red-400' : 'text-slate-400' }}">
                                            {{ $class->bookings_count }}/{{ $class->capacity }}
                                            @if($isFull) <span class="text-red-400 font-medium">Full</span> @endif
                                        </span>
                                    </div>

                                    <!-- Quick Actions -->
                                    <div class="flex gap-2 mt-3">
                                        <a href="{{ route('admin.attendance', $class->id) }}" 
                                           class="flex-1 py-2 text-center text-xs font-semibold rounded-lg bg-slate-700/50 text-slate-300 hover:bg-slate-700 transition-colors">
                                            Attendance
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endforeach

    @if($classes->isEmpty() || $classes->flatten()->isEmpty())
        <div class="p-10 text-center text-slate-500 bg-slate-900/50 rounded-xl border border-dashed border-slate-700">
            <span class="material-symbols-outlined text-4xl mb-2">event_busy</span>
            <p>No classes scheduled for this day.</p>
            <a href="{{ route('admin.classes.create') }}" class="text-blue-400 hover:text-blue-300 text-sm mt-2 inline-block">
                Add a class
            </a>
        </div>
    @endif
</div>
@endsection
