@extends('layouts.admin')

@section('title', 'Manage Classes')

@section('content')
<div class="space-y-5">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <button onclick="openMenu()" class="text-slate-400 hover:text-white transition-colors">
                <span class="material-symbols-outlined">menu</span>
            </button>
            <h1 class="text-xl font-bold text-white">Manage Classes</h1>
        </div>
        <a href="{{ route('admin.classes', ['date' => now()->format('Y-m-d')]) }}" 
           class="px-3 py-1 text-xs font-semibold rounded-lg bg-slate-700/50 text-slate-300 hover:bg-slate-700 transition-colors">
            Today
        </a>
    </div>

    <!-- Week Navigation -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.classes', ['date' => $prevWeek->format('Y-m-d')]) }}" 
           class="w-10 h-10 rounded-full bg-slate-800/60 flex items-center justify-center text-slate-400 hover:text-white hover:bg-slate-700 transition-colors">
            <span class="material-symbols-outlined">chevron_left</span>
        </a>
        <span class="text-sm font-semibold text-slate-300">
            {{ $weekStart->format('M d') }} - {{ $weekStart->copy()->addDays(6)->format('M d, Y') }}
        </span>
        <a href="{{ route('admin.classes', ['date' => $nextWeek->format('Y-m-d')]) }}" 
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
            <a href="{{ route('admin.classes', ['date' => $day->format('Y-m-d')]) }}"
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

    <!-- Add New Class Button -->
    <a href="{{ route('admin.classes.create', ['date' => $selectedDate->format('Y-m-d')]) }}" 
       class="flex items-center justify-center gap-2 w-full py-3 rounded-xl border-2 border-dashed border-blue-500/50 text-blue-500 hover:bg-blue-500/10 transition-colors font-semibold">
        <span class="material-symbols-outlined">add</span>
        Add New Class
    </a>

    <!-- Private Classes (selected date) -->
    @if($privateClasses->count() > 0)
    <div class="space-y-3">
        <div class="flex items-center gap-2 text-slate-400">
            <span class="material-symbols-outlined text-violet-500 text-lg">person_search</span>
            <span class="text-xs font-bold tracking-wider">PRIVATE CLASSES</span>
        </div>
        <div class="space-y-2">
            @foreach($privateClasses as $pc)
            <div class="glass rounded-2xl p-4 relative overflow-hidden border border-violet-500/30">
                <div class="relative z-10 flex items-center gap-4">
                    <div class="flex flex-col items-center text-center min-w-[50px]">
                        <span class="text-2xl font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">{{ $pc->scheduled_at->format('H:i') }}</span>
                        <span class="text-[10px] text-slate-500 uppercase">{{ $pc->scheduled_at->format('A') }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-white font-semibold truncate">{{ $pc->coach->name ?? 'Coach' }} <span class="text-slate-500 font-normal">→</span> {{ $pc->member->name ?? 'Member' }}</p>
                        <p class="text-slate-500 text-sm">{{ $pc->duration_minutes }} min @if($pc->price) · NT${{ number_format($pc->price) }} @endif</p>
                        <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $pc->status === 'accepted' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-amber-500/20 text-amber-400' }}">{{ $pc->status }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

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
                        $trialsCount = $class->trials_count ?? 0;
                        $paidCount = $class->paid_bookings_count ?? $class->bookings_count;
                        $unpaidCount = $class->unpaid_bookings_count ?? 0;
                        $totalAttendance = $class->bookings_count + $trialsCount;
                        $paidPercent = $class->capacity > 0 ? ($paidCount / $class->capacity) * 100 : 0;
                        $unpaidPercent = $class->capacity > 0 ? ($unpaidCount / $class->capacity) * 100 : 0;
                        $trialsPercent = $class->capacity > 0 ? ($trialsCount / $class->capacity) * 100 : 0;
                        $totalPercent = $paidPercent + $unpaidPercent + $trialsPercent;
                        $isFull = $totalAttendance >= $class->capacity;
                        $isCancelled = $class->is_cancelled ?? false;
                    @endphp
                    
                    <div class="glass rounded-2xl p-4 relative overflow-hidden {{ $isCancelled ? 'opacity-60' : '' }}">
                        <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
                        @if($isCancelled)
                            <div class="absolute top-2 right-2 px-2 py-1 rounded bg-red-500/20 text-red-400 text-[10px] font-bold uppercase z-20">
                                Cancelled
                            </div>
                        @endif
                        <div class="relative z-10">
                            <div class="flex gap-4">
                                <!-- Time Column -->
                                <div class="flex flex-col items-center text-center min-w-[50px]">
                                    <span class="text-2xl font-bold {{ $isCancelled ? 'text-slate-500 line-through' : 'text-white' }}" style="font-family: 'Bebas Neue', sans-serif;">
                                        {{ $class->start_time->format('H:i') }}
                                    </span>
                                    <span class="text-[10px] text-slate-500 uppercase">
                                        {{ $class->start_time->format('A') }}
                                    </span>
                                    @if(!$isCancelled)
                                        <button type="button" onclick="openTrialModal(this)" data-action="{{ route('admin.classes.trials.store', $class->id) }}"
                                                class="mt-2 px-2 py-1 text-[10px] font-semibold rounded bg-emerald-500 text-white hover:bg-emerald-600 transition-colors">
                                            Add trial
                                        </button>
                                    @endif
                                </div>

                                <!-- Class Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between mb-2">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <h3 class="{{ $isCancelled ? 'text-slate-500 line-through' : 'text-white' }} font-semibold">{{ $class->title }}</h3>
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

                                    <!-- Capacity Bar (green = paid, red = unpaid, orange = trials) -->
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs text-slate-500">Capacity</span>
                                        <div class="flex-1 h-2 bg-slate-700 rounded-full overflow-hidden flex">
                                            @if(!$isCancelled && $class->capacity > 0)
                                                @if($paidCount > 0)
                                                    <div class="h-full bg-emerald-500 transition-all duration-500" style="width: {{ min($paidPercent, 100) }}%"></div>
                                                @endif
                                                @if($unpaidCount > 0)
                                                    <div class="h-full bg-red-500 transition-all duration-500" style="width: {{ min($unpaidPercent, max(0, 100 - $paidPercent)) }}%"></div>
                                                @endif
                                                @if($trialsCount > 0)
                                                    <div class="h-full bg-orange-500 transition-all duration-500" style="width: {{ min($trialsPercent, max(0, 100 - $paidPercent - $unpaidPercent)) }}%"></div>
                                                @endif
                                            @elseif($isCancelled)
                                                <div class="h-full bg-slate-600" style="width: {{ min($totalPercent, 100) }}%"></div>
                                            @endif
                                        </div>
                                        <span class="text-xs {{ $isFull && !$isCancelled ? 'text-red-400' : 'text-slate-400' }}">
                                            {{ $totalAttendance }}/{{ $class->capacity }}
                                            @if($isFull && !$isCancelled) <span class="text-red-400 font-medium">Full</span> @endif
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

    <!-- Add Trial Modal - single modal at end so it renders in front of everything -->
    <div id="trialModal" class="hidden fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm" onclick="if(event.target===this) closeTrialModal()">
        <div class="glass rounded-2xl p-6 w-full max-w-sm relative z-[10000]" onclick="event.stopPropagation()">
            <button type="button" onclick="closeTrialModal()" class="absolute top-4 right-4 text-slate-400 hover:text-white">
                <span class="material-symbols-outlined">close</span>
            </button>
            <h3 class="text-lg font-bold text-white mb-4" style="font-family: 'Bebas Neue', sans-serif;">Add trial</h3>
            <form id="trialForm" action="" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="trial-name" class="block text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Name</label>
                    <input type="text" name="name" id="trial-name" required maxlength="255"
                           class="w-full px-4 py-2.5 rounded-lg bg-slate-800 border border-slate-600 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500"
                           placeholder="Trial member name">
                </div>
                <div>
                    <label for="trial-age" class="block text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Age</label>
                    <input type="number" name="age" id="trial-age" min="1" max="120" placeholder="Optional"
                           class="w-full px-4 py-2.5 rounded-lg bg-slate-800 border border-slate-600 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500">
                </div>
                <div class="flex gap-2 pt-2">
                    <button type="button" onclick="closeTrialModal()"
                            class="flex-1 py-2.5 text-sm font-semibold rounded-lg bg-slate-700 text-slate-300 hover:bg-slate-600 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 py-2.5 text-sm font-semibold rounded-lg bg-blue-500 text-white hover:bg-blue-600 transition-colors">
                        Add trial
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openTrialModal(btn) {
    var action = btn.getAttribute('data-action');
    if (action) document.getElementById('trialForm').action = action;
    document.getElementById('trialModal').classList.remove('hidden');
}
function closeTrialModal() {
    document.getElementById('trialModal').classList.add('hidden');
}
</script>
@endsection
