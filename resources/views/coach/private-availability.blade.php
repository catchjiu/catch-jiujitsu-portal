@extends('layouts.app')

@section('title', app()->getLocale() === 'zh-TW' ? '私教時段' : 'Private Class Availability')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">{{ app()->getLocale() === 'zh-TW' ? '私教時段' : 'Private Class Availability' }}</h1>
            <p class="text-slate-500 text-sm">{{ app()->getLocale() === 'zh-TW' ? '設定可預約時段並查看已預約私教' : 'Set your available slots and see booked private classes' }}</p>
        </div>
        <a href="{{ route('coach.private-requests') }}" class="px-4 py-2 rounded-lg bg-amber-500/20 text-amber-400 text-sm font-semibold hover:bg-amber-500/30 transition-colors flex items-center gap-2">
            <span class="material-symbols-outlined text-lg">mail</span>
            {{ app()->getLocale() === 'zh-TW' ? '預約請求' : 'Requests' }}
        </a>
    </div>

    @if(session('success'))
        <div class="p-3 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm">{{ session('success') }}</div>
    @endif

    <!-- Private class price -->
    <div class="glass rounded-2xl p-5 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
        <div class="relative z-10">
            <h2 class="text-lg font-bold text-white mb-4" style="font-family: 'Bebas Neue', sans-serif;">{{ app()->getLocale() === 'zh-TW' ? '私教課價格' : 'Private class price' }}</h2>
            <form action="{{ route('settings.private-class') }}" method="POST" class="flex gap-3 items-end">
                @csrf
                <div class="flex-1">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">{{ app()->getLocale() === 'zh-TW' ? '每節價格 (NT$)' : 'Price per session (NT$)' }}</label>
                    <input type="number" name="private_class_price" value="{{ old('private_class_price', auth()->user()->private_class_price) }}" min="0" step="1" placeholder="e.g. 1500"
                        class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-blue-500 transition-colors">
                </div>
                <button type="submit" class="px-4 py-3 rounded-lg bg-blue-500 hover:bg-blue-600 text-white font-bold uppercase text-sm tracking-wider transition-colors">{{ __('app.common.save') }}</button>
            </form>
        </div>
    </div>

    <!-- Recurring weekly availability -->
    <div class="glass rounded-2xl p-5 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
        <div class="relative z-10">
            <h2 class="text-lg font-bold text-white mb-4" style="font-family: 'Bebas Neue', sans-serif;">{{ app()->getLocale() === 'zh-TW' ? '每週可預約時段' : 'Weekly availability' }}</h2>
            <form action="{{ route('coach.private-availability.save') }}" method="POST" id="availabilityForm">
                @csrf
                <div id="slotsContainer" class="space-y-4">
                    @foreach($availability as $slot)
                    <div class="slot-row p-4 rounded-xl bg-slate-800/50 border border-slate-700/50 grid grid-cols-12 gap-2 items-end">
                        <div class="col-span-12 sm:col-span-3">
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">{{ app()->getLocale() === 'zh-TW' ? '星期' : 'Day' }}</label>
                            <select name="slots[{{ $loop->index }}][day_of_week]" class="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-white text-sm focus:outline-none focus:border-blue-500">
                                @foreach($dayNames as $num => $name)
                                    <option value="{{ $num }}" {{ $slot->day_of_week == $num ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-6 sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">{{ app()->getLocale() === 'zh-TW' ? '開始' : 'Start' }}</label>
                            <input type="time" name="slots[{{ $loop->index }}][start_time]" value="{{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}" class="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-white text-sm focus:outline-none focus:border-blue-500">
                        </div>
                        <div class="col-span-6 sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">{{ app()->getLocale() === 'zh-TW' ? '結束' : 'End' }}</label>
                            <input type="time" name="slots[{{ $loop->index }}][end_time]" value="{{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}" class="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-white text-sm focus:outline-none focus:border-blue-500">
                        </div>
                        <div class="col-span-6 sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">{{ app()->getLocale() === 'zh-TW' ? '時長(分)' : 'Duration (min)' }}</label>
                            <input type="number" name="slots[{{ $loop->index }}][slot_duration_minutes]" value="{{ $slot->slot_duration_minutes }}" min="30" max="180" step="15" class="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-white text-sm focus:outline-none focus:border-blue-500">
                        </div>
                        <div class="col-span-6 sm:col-span-2 flex items-end">
                            <button type="button" class="remove-slot px-3 py-2 rounded-lg border border-red-500/50 text-red-400 text-sm hover:bg-red-500/10 transition-colors">{{ app()->getLocale() === 'zh-TW' ? '刪除' : 'Remove' }}</button>
                        </div>
                    </div>
                    @endforeach
                </div>
                <input type="hidden" name="slots_index" id="slotsIndex" value="{{ $availability->count() }}">
                <button type="button" id="addSlotBtn" class="mt-4 w-full py-2.5 rounded-lg border-2 border-dashed border-slate-600 text-slate-400 text-sm font-semibold hover:border-blue-500 hover:text-blue-400 transition-colors flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined">add</span>
                    {{ app()->getLocale() === 'zh-TW' ? '新增時段' : 'Add time slot' }}
                </button>
                <button type="submit" class="mt-4 w-full py-3 rounded-lg bg-blue-500 hover:bg-blue-600 text-white font-bold uppercase text-sm tracking-wider transition-colors">
                    {{ __('app.common.save') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Upcoming booked private classes (calendar style) -->
    <div class="glass rounded-2xl p-5 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
        <div class="relative z-10">
            <h2 class="text-lg font-bold text-white mb-4" style="font-family: 'Bebas Neue', sans-serif;">{{ app()->getLocale() === 'zh-TW' ? '已預約私教' : 'Booked private classes' }}</h2>
            @if($bookings->isEmpty())
                <p class="text-slate-500 text-sm py-4">{{ app()->getLocale() === 'zh-TW' ? '尚無預約' : 'No upcoming private classes.' }}</p>
            @else
                <div class="space-y-3">
                    @foreach($bookings as $b)
                        <div class="flex items-center gap-4 p-4 rounded-xl bg-slate-800/50 border border-slate-700/50">
                            @if($b->member->avatar)
                                <img src="{{ $b->member->avatar }}" alt="" class="w-12 h-12 rounded-full object-cover border-2 border-slate-600">
                            @else
                                <div class="w-12 h-12 rounded-full bg-slate-700 border-2 border-slate-600 flex items-center justify-center text-slate-400 font-bold">{{ strtoupper(substr($b->member->name, 0, 2)) }}</div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <p class="text-white font-semibold truncate">{{ $b->member->name }}</p>
                                <p class="text-slate-500 text-sm">{{ $b->scheduled_at->format('D, M j \a\t g:i A') }} · {{ $b->duration_minutes }} min</p>
                                <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $b->status === 'accepted' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-amber-500/20 text-amber-400' }}">{{ $b->status }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<script>
(function() {
    var dayNames = @json(array_values($dayNames));
    var index = parseInt(document.getElementById('slotsIndex').value, 10);
    var container = document.getElementById('slotsContainer');
    var form = document.getElementById('availabilityForm');

    document.getElementById('addSlotBtn').addEventListener('click', function() {
        var html = '<div class="slot-row p-4 rounded-xl bg-slate-800/50 border border-slate-700/50 grid grid-cols-12 gap-2 items-end">' +
            '<div class="col-span-12 sm:col-span-3"><label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Day</label><select name="slots[' + index + '][day_of_week]" class="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-white text-sm">' +
            dayNames.map(function(_, i) { return '<option value="' + i + '">' + (['Sun','Mon','Tue','Wed','Thu','Fri','Sat'][i]) + '</option>'; }).join('') +
            '</select></div>' +
            '<div class="col-span-6 sm:col-span-2"><label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Start</label><input type="time" name="slots[' + index + '][start_time]" value="09:00" class="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-white text-sm"></div>' +
            '<div class="col-span-6 sm:col-span-2"><label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">End</label><input type="time" name="slots[' + index + '][end_time]" value="17:00" class="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-white text-sm"></div>' +
            '<div class="col-span-6 sm:col-span-2"><label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Duration (min)</label><input type="number" name="slots[' + index + '][slot_duration_minutes]" value="60" min="30" max="180" step="15" class="w-full px-3 py-2 rounded-lg bg-slate-800 border border-slate-700 text-white text-sm"></div>' +
            '<div class="col-span-6 sm:col-span-2 flex items-end"><button type="button" class="remove-slot px-3 py-2 rounded-lg border border-red-500/50 text-red-400 text-sm">Remove</button></div></div>';
        container.insertAdjacentHTML('beforeend', html);
        index++;
        document.getElementById('slotsIndex').value = index;
        container.querySelectorAll('.remove-slot').forEach(function(btn) {
            btn.onclick = removeSlot;
        });
    });

    function removeSlot() {
        this.closest('.slot-row').remove();
    }

    container.querySelectorAll('.remove-slot').forEach(function(btn) {
        btn.addEventListener('click', removeSlot);
    });

    form.addEventListener('submit', function() {
        var names = {};
        container.querySelectorAll('.slot-row').forEach(function(row, i) {
            row.querySelectorAll('select, input').forEach(function(el) {
                if (el.name) el.name = el.name.replace(/slots\]\[\d+\]/, 'slots][' + i + ']');
            });
        });
    });
})();
</script>
@endsection
