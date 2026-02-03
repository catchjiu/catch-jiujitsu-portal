@extends('layouts.admin')

@section('title', 'Attendance - ' . $class->title)

@section('content')
<div class="space-y-5">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.classes') }}" class="text-slate-400 hover:text-white transition-colors">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <h1 class="text-lg font-bold text-white">Admin Dashboard</h1>
        </div>
        <button class="text-slate-400 hover:text-white transition-colors">
            <span class="material-symbols-outlined">more_vert</span>
        </button>
    </div>

    <!-- Class Info Card -->
    <div class="space-y-2">
        <div class="flex items-center gap-2">
            <h2 class="text-2xl font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">
                {{ $class->title }} - {{ $class->type }}
            </h2>
            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-500/20 text-emerald-400">
                Active
            </span>
        </div>
        <div class="flex items-center gap-3 text-slate-400 text-sm">
            <span class="material-symbols-outlined text-lg">calendar_today</span>
            <span>{{ $class->start_time->format('l, g:i A') }}</span>
            <span class="text-slate-600">•</span>
            <span>{{ $class->duration_minutes }} min</span>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-3 gap-3">
        <div class="glass rounded-xl p-4 text-center border-t-2 border-t-blue-500">
            <p class="text-2xl font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">{{ $class->bookings_count }}</p>
            <p class="text-[10px] text-slate-400 uppercase tracking-wider">Booked</p>
        </div>
        <div class="glass rounded-xl p-4 text-center border-t-2 border-t-emerald-500">
            <p class="text-2xl font-bold text-emerald-500" style="font-family: 'Bebas Neue', sans-serif;">{{ $checkedInCount }}</p>
            <p class="text-[10px] text-slate-400 uppercase tracking-wider">Checked-in</p>
        </div>
        <div class="glass rounded-xl p-4 text-center border-t-2 border-t-amber-500">
            <p class="text-2xl font-bold text-amber-500" style="font-family: 'Bebas Neue', sans-serif;">{{ $waitlistCount }}</p>
            <p class="text-[10px] text-slate-400 uppercase tracking-wider">Waitlist</p>
        </div>
    </div>

    <!-- Search -->
    <div class="relative">
        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-500">search</span>
        <input type="text" id="searchInput" placeholder="Search student by name..."
            class="w-full pl-12 pr-4 py-3 rounded-xl bg-slate-800/60 border border-slate-700/50 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 transition-colors"
            onkeyup="filterStudents()">
    </div>

    <!-- Attendees Header -->
    <div class="flex items-center justify-between">
        <p class="text-sm text-slate-400 uppercase tracking-wider font-medium">Attendees</p>
        <p class="text-sm text-slate-500">{{ $class->bookings_count + ($trials ?? collect())->count() }} / {{ $class->capacity }} spots filled</p>
    </div>

    <!-- Attendee List (slide left to reveal delete) -->
    <div class="space-y-2" id="attendeeList">
        @foreach($bookedUsers as $item)
            @php
                $user = $item['user'];
                $booking = $item['booking'];
                $isCheckedIn = $booking->checked_in ?? false;
            @endphp
            <div class="attendee-item rounded-xl overflow-hidden border border-slate-700/30" data-name="{{ strtolower($user->name) }}">
                <div class="overflow-x-auto scrollbar-hide" style="-webkit-overflow-scrolling: touch; scroll-snap-type: x mandatory;">
                    <div class="flex flex-shrink-0" style="width: calc(100% + 72px); min-width: calc(100% + 72px); scroll-snap-align: start;">
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-800/40 flex-shrink-0" style="width: calc(100% - 72px); min-width: calc(100% - 72px);">
                            <div class="relative flex-shrink-0">
                                <div class="w-12 h-12 rounded-full overflow-hidden bg-slate-700 border-2 border-slate-600">
                                    @if($user->avatar)
                                        <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-slate-400 font-bold">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-white font-medium truncate">{{ $user->name }}</h3>
                                <p class="text-slate-500 text-xs">Joined {{ $user->created_at->format('Y') }} • {{ $user->rank }} Belt</p>
                            </div>
                            <div class="flex items-center gap-2">
                                @if($isCheckedIn)
                                    <span class="px-2 py-1 rounded text-[10px] font-bold uppercase bg-emerald-500/20 text-emerald-400">Checked in</span>
                                @endif
                                <form action="{{ route('admin.attendance.toggle', [$class->id, $booking->id]) }}" method="POST" class="flex-shrink-0">
                                    @csrf
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer" {{ $isCheckedIn ? 'checked' : '' }} onchange="this.form.submit()">
                                        <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
                                    </label>
                                </form>
                            </div>
                        </div>
                        <form action="{{ route('admin.attendance.booking.remove', [$class->id, $booking->id]) }}" method="POST" class="flex-shrink-0 w-[72px] flex items-center justify-center bg-red-500/90 rounded-r-xl" onclick="event.stopPropagation()">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full h-full flex items-center justify-center text-white p-3" title="Remove from class">
                                <span class="material-symbols-outlined">delete</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach

        @foreach($trials ?? [] as $trial)
            <div class="attendee-item rounded-xl overflow-hidden border border-slate-700/30 border-l-4 border-l-amber-500/50" data-name="{{ strtolower($trial->name) }}">
                <div class="overflow-x-auto scrollbar-hide" style="-webkit-overflow-scrolling: touch; scroll-snap-type: x mandatory;">
                    <div class="flex flex-shrink-0" style="width: calc(100% + 72px); min-width: calc(100% + 72px); scroll-snap-align: start;">
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-800/40 flex-shrink-0" style="width: calc(100% - 72px); min-width: calc(100% - 72px);">
                            <div class="relative flex-shrink-0">
                                <div class="w-12 h-12 rounded-full overflow-hidden bg-slate-700 border-2 border-slate-600 flex items-center justify-center text-slate-400 font-bold">
                                    {{ strtoupper(substr($trial->name, 0, 2)) }}
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-white font-medium truncate">{{ $trial->name }}</h3>
                                <p class="text-slate-500 text-xs">Trial @if($trial->age) • Age {{ $trial->age }} @endif</p>
                            </div>
                            <span class="px-2 py-1 rounded text-[10px] font-bold uppercase bg-amber-500/20 text-amber-400 flex-shrink-0">Trial</span>
                        </div>
                        <form action="{{ route('admin.attendance.trial.remove', [$class->id, $trial->id]) }}" method="POST" class="flex-shrink-0 w-[72px] flex items-center justify-center bg-red-500/90 rounded-r-xl" onclick="event.stopPropagation()">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full h-full flex items-center justify-center text-white p-3" title="Remove trial">
                                <span class="material-symbols-outlined">delete</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach

        @if($bookedUsers->isEmpty() && ($trials ?? collect())->isEmpty())
            <div class="p-10 text-center text-slate-500 bg-slate-900/50 rounded-xl border border-dashed border-slate-700">
                <span class="material-symbols-outlined text-4xl mb-2">person_off</span>
                <p>No bookings for this class yet.</p>
            </div>
        @endif
    </div>

    <!-- Non-booked members (for walk-ins), filtered by class age group -->
    @if($availableMembers->count() > 0)
        <div class="pt-4 border-t border-slate-800" id="walkInSection">
            <p class="text-sm text-slate-400 uppercase tracking-wider font-medium mb-3">Available for Walk-in</p>
            <div class="space-y-2" id="walkInList">
                @foreach($availableMembers as $member)
                    <div class="walkin-item flex items-center gap-3 p-3 rounded-xl bg-slate-800/20 border border-slate-700/20"
                         data-name="{{ strtolower($member->name) }}">
                        <div class="w-10 h-10 rounded-full overflow-hidden bg-slate-700">
                            <div class="w-full h-full flex items-center justify-center text-slate-500 text-sm font-bold">
                                {{ strtoupper(substr($member->name, 0, 2)) }}
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-slate-400 font-medium truncate">{{ $member->name }}</h3>
                            <p class="text-slate-600 text-xs">{{ $member->rank }} Belt</p>
                        </div>
                        <form action="{{ route('admin.attendance.walkin', $class->id) }}" method="POST" class="flex-shrink-0">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $member->id }}">
                            <button type="submit" class="w-8 h-8 rounded-full bg-slate-700/50 flex items-center justify-center text-slate-500 hover:text-white hover:bg-slate-700 transition-colors" title="Add as walk-in">
                                <span class="material-symbols-outlined text-lg">add</span>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

@endsection

@section('scripts')
<script>
function filterStudents() {
    const search = document.getElementById('searchInput').value.toLowerCase().trim();
    const attendeeItems = document.querySelectorAll('.attendee-item');
    const walkinItems = document.querySelectorAll('.walkin-item');

    attendeeItems.forEach(item => {
        const name = (item.dataset.name || '').toLowerCase();
        item.style.display = name.includes(search) ? 'flex' : 'none';
    });
    walkinItems.forEach(item => {
        const name = (item.dataset.name || '').toLowerCase();
        item.style.display = name.includes(search) ? 'flex' : 'none';
    });
}
</script>
@endsection
