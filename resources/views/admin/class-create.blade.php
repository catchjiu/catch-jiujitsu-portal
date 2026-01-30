@extends('layouts.admin')

@section('title', 'Add New Class')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.classes') }}" class="text-slate-400 hover:text-white transition-colors">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h1 class="text-xl font-bold text-white">Add New Class</h1>
    </div>

    <!-- Form -->
    <form action="{{ route('admin.classes.store') }}" method="POST" class="space-y-5">
        @csrf

        @if ($errors->any())
            <div class="p-3 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Class Name / Title -->
        <div>
            <label class="block text-xs font-medium text-slate-400 uppercase tracking-wider mb-2">Class Name</label>
            <input type="text" name="title" value="{{ old('title') }}" required
                class="w-full px-4 py-3 rounded-xl bg-slate-800/60 border border-slate-700/50 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 transition-colors"
                placeholder="BJJ Fundamentals">
        </div>

        <!-- Class Type -->
        <div>
            <label class="block text-xs font-medium text-slate-400 uppercase tracking-wider mb-2">Class Type</label>
            <select name="type" required
                class="w-full px-4 py-3 rounded-xl bg-slate-800/60 border border-slate-700/50 text-white focus:outline-none focus:border-blue-500 transition-colors appearance-none cursor-pointer">
                <option value="" disabled selected>Select class type</option>
                <option value="Gi" {{ old('type') == 'Gi' ? 'selected' : '' }}>Gi</option>
                <option value="No-Gi" {{ old('type') == 'No-Gi' ? 'selected' : '' }}>No-Gi</option>
                <option value="Fundamentals" {{ old('type') == 'Fundamentals' ? 'selected' : '' }}>Fundamentals</option>
                <option value="Open Mat" {{ old('type') == 'Open Mat' ? 'selected' : '' }}>Open Mat</option>
            </select>
        </div>

        <!-- Age Group -->
        <div>
            <label class="block text-xs font-medium text-slate-400 uppercase tracking-wider mb-2">Age Group</label>
            <select name="age_group" required
                class="w-full px-4 py-3 rounded-xl bg-slate-800/60 border border-slate-700/50 text-white focus:outline-none focus:border-blue-500 transition-colors appearance-none cursor-pointer">
                <option value="Adults" {{ old('age_group', 'Adults') == 'Adults' ? 'selected' : '' }}>Adults</option>
                <option value="Kids" {{ old('age_group') == 'Kids' ? 'selected' : '' }}>Kids</option>
                <option value="All" {{ old('age_group') == 'All' ? 'selected' : '' }}>All Ages</option>
            </select>
        </div>

        <!-- Instructor -->
        <div>
            <label class="block text-xs font-medium text-slate-400 uppercase tracking-wider mb-2">Instructor</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-500">search</span>
                <input type="text" name="instructor_name" value="{{ old('instructor_name') }}" required
                    class="w-full pl-12 pr-12 py-3 rounded-xl bg-slate-800/60 border border-slate-700/50 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 transition-colors"
                    placeholder="Search instructor...">
                <div class="absolute right-3 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center">
                    <span class="material-symbols-outlined text-slate-400 text-lg">person</span>
                </div>
            </div>
        </div>

        <!-- Date and Time -->
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-medium text-slate-400 uppercase tracking-wider mb-2">Date</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 text-lg">calendar_today</span>
                    <input type="date" name="date" value="{{ old('date', now()->format('Y-m-d')) }}" required
                        class="w-full pl-12 pr-4 py-3 rounded-xl bg-slate-800/60 border border-slate-700/50 text-white focus:outline-none focus:border-blue-500 transition-colors">
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400 uppercase tracking-wider mb-2">Time</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 text-lg">schedule</span>
                    <input type="time" name="time" value="{{ old('time', '18:00') }}" required
                        class="w-full pl-12 pr-4 py-3 rounded-xl bg-slate-800/60 border border-slate-700/50 text-white focus:outline-none focus:border-blue-500 transition-colors">
                </div>
            </div>
        </div>

        <!-- Duration -->
        <div>
            <label class="block text-xs font-medium text-slate-400 uppercase tracking-wider mb-2">Duration (minutes)</label>
            <select name="duration_minutes" required
                class="w-full px-4 py-3 rounded-xl bg-slate-800/60 border border-slate-700/50 text-white focus:outline-none focus:border-blue-500 transition-colors">
                <option value="60" {{ old('duration_minutes', 60) == 60 ? 'selected' : '' }}>60 minutes</option>
                <option value="90" {{ old('duration_minutes') == 90 ? 'selected' : '' }}>90 minutes</option>
                <option value="120" {{ old('duration_minutes') == 120 ? 'selected' : '' }}>120 minutes</option>
            </select>
        </div>

        <!-- Capacity Limit -->
        <div>
            <label class="block text-xs font-medium text-slate-400 uppercase tracking-wider mb-2">Capacity Limit</label>
            <div class="flex items-center gap-4 p-4 rounded-xl bg-slate-800/60 border border-slate-700/50">
                <div class="w-10 h-10 rounded-lg bg-blue-500/20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-blue-500">groups</span>
                </div>
                <span class="text-slate-300 flex-1">Max Students</span>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="decrementCapacity()" 
                        class="w-8 h-8 rounded-lg bg-slate-700 text-white hover:bg-slate-600 transition-colors flex items-center justify-center">
                        <span class="material-symbols-outlined text-lg">remove</span>
                    </button>
                    <input type="number" name="capacity" id="capacity" value="{{ old('capacity', 25) }}" required min="1" max="100"
                        class="w-14 text-center py-2 rounded-lg bg-slate-700 text-white border-none focus:outline-none focus:ring-2 focus:ring-blue-500 font-bold">
                    <button type="button" onclick="incrementCapacity()" 
                        class="w-8 h-8 rounded-lg bg-blue-500 text-white hover:bg-blue-600 transition-colors flex items-center justify-center">
                        <span class="material-symbols-outlined text-lg">add</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Recurring Class Toggle -->
        <div class="flex items-center justify-between p-4 rounded-xl bg-slate-800/60 border border-slate-700/50">
            <div>
                <p class="text-white font-medium">Recurring Class</p>
                <p class="text-slate-500 text-xs">Repeat weekly on this day</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="recurring" value="1" class="sr-only peer" {{ old('recurring') ? 'checked' : '' }}>
                <div class="w-12 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
            </label>
        </div>

        <!-- Submit Button -->
        <button type="submit"
            class="w-full py-4 rounded-xl bg-blue-500 hover:bg-blue-600 text-white font-bold transition-colors shadow-lg shadow-blue-500/20 flex items-center justify-center gap-2">
            <span class="material-symbols-outlined">check</span>
            Create Class
        </button>

        <!-- Cancel Link -->
        <a href="{{ route('admin.classes') }}" class="block text-center text-slate-500 hover:text-slate-300 transition-colors py-2">
            Cancel
        </a>
    </form>
</div>

@endsection

@section('scripts')
<script>
function incrementCapacity() {
    const input = document.getElementById('capacity');
    const current = parseInt(input.value) || 0;
    if (current < 100) input.value = current + 1;
}

function decrementCapacity() {
    const input = document.getElementById('capacity');
    const current = parseInt(input.value) || 0;
    if (current > 1) input.value = current - 1;
}
</script>
@endsection
