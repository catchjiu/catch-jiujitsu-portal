@extends('layouts.admin')

@section('title', 'Edit Class')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.classes') }}" class="text-slate-400 hover:text-white transition-colors">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h1 class="text-xl font-bold text-white">Edit Class</h1>
    </div>

    <!-- Form -->
    <form action="{{ route('admin.classes.update', $class->id) }}" method="POST" class="space-y-5">
        @csrf
        @method('PUT')

        @if ($errors->any())
            <div class="p-3 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Class Info Display -->
        <div class="glass rounded-xl p-4">
            <p class="text-slate-400 text-xs uppercase tracking-wider mb-1">Scheduled</p>
            <p class="text-white">{{ $class->start_time->format('l, M j, Y \a\t g:i A') }}</p>
        </div>

        <!-- Class Name / Title -->
        <div>
            <label class="block text-xs font-medium text-slate-400 uppercase tracking-wider mb-2">Class Name</label>
            <input type="text" name="title" value="{{ old('title', $class->title) }}" required
                class="w-full px-4 py-3 rounded-xl bg-slate-800/60 border border-slate-700/50 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 transition-colors">
        </div>

        <!-- Class Type -->
        <div>
            <label class="block text-xs font-medium text-slate-400 uppercase tracking-wider mb-2">Class Type</label>
            <select name="type" required
                class="w-full px-4 py-3 rounded-xl bg-slate-800/60 border border-slate-700/50 text-white focus:outline-none focus:border-blue-500 transition-colors">
                @foreach(['Gi', 'No-Gi', 'Fundamentals', 'Open Mat'] as $type)
                    <option value="{{ $type }}" {{ $class->type == $type ? 'selected' : '' }}>{{ $type }}</option>
                @endforeach
            </select>
        </div>

        <!-- Age Group -->
        <div>
            <label class="block text-xs font-medium text-slate-400 uppercase tracking-wider mb-2">Age Group</label>
            <select name="age_group" required
                class="w-full px-4 py-3 rounded-xl bg-slate-800/60 border border-slate-700/50 text-white focus:outline-none focus:border-blue-500 transition-colors">
                <option value="Adults" {{ ($class->age_group ?? 'Adults') == 'Adults' ? 'selected' : '' }}>Adults</option>
                <option value="Kids" {{ ($class->age_group ?? '') == 'Kids' ? 'selected' : '' }}>Kids</option>
                <option value="All" {{ ($class->age_group ?? '') == 'All' ? 'selected' : '' }}>All Ages</option>
            </select>
        </div>

        <!-- Instructor -->
        <div>
            <label class="block text-xs font-medium text-slate-400 uppercase tracking-wider mb-2">Instructor</label>
            <select name="instructor_id" required
                class="w-full px-4 py-3 rounded-xl bg-slate-800/60 border border-slate-700/50 text-white focus:outline-none focus:border-blue-500 transition-colors appearance-none cursor-pointer">
                @foreach($coaches as $coach)
                    <option value="{{ $coach->id }}" {{ old('instructor_id', $class->instructor_id) == $coach->id ? 'selected' : '' }}>
                        {{ $coach->name }} ({{ $coach->rank }} Belt)
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Capacity -->
        <div>
            <label class="block text-xs font-medium text-slate-400 uppercase tracking-wider mb-2">Capacity</label>
            <input type="number" name="capacity" value="{{ old('capacity', $class->capacity) }}" required min="1" max="100"
                class="w-full px-4 py-3 rounded-xl bg-slate-800/60 border border-slate-700/50 text-white focus:outline-none focus:border-blue-500 transition-colors">
            <p class="text-slate-500 text-xs mt-1">Currently {{ $class->bookings()->count() }} booked</p>
        </div>

        <!-- Cancel Class -->
        <div class="glass rounded-xl p-4">
            <label class="flex items-center justify-between cursor-pointer">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg {{ $class->is_cancelled ? 'bg-red-500/20' : 'bg-slate-700/50' }} flex items-center justify-center transition-colors">
                        <span class="material-symbols-outlined {{ $class->is_cancelled ? 'text-red-500' : 'text-slate-400' }}">event_busy</span>
                    </div>
                    <div>
                        <span class="text-white font-medium">Class Cancelled</span>
                        <p class="text-slate-500 text-xs">Mark this class as cancelled</p>
                    </div>
                </div>
                <input type="checkbox" name="is_cancelled" value="1" {{ old('is_cancelled', $class->is_cancelled) ? 'checked' : '' }}
                    class="w-5 h-5 rounded bg-slate-700 border-slate-600 text-red-500 focus:ring-red-500 focus:ring-offset-slate-900 cursor-pointer"
                    onchange="showCancelWarning(this)">
            </label>
            @if($class->bookings()->count() > 0)
                <div id="cancelWarning" class="{{ $class->is_cancelled ? '' : 'hidden' }} mt-3 p-3 rounded-lg bg-amber-500/10 border border-amber-500/20">
                    <p class="text-amber-400 text-sm flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg">warning</span>
                        {{ $class->bookings()->count() }} member(s) have booked this class. They will see it as cancelled.
                    </p>
                </div>
            @endif
        </div>
        
        <script>
            function showCancelWarning(checkbox) {
                const warning = document.getElementById('cancelWarning');
                if (warning) {
                    if (checkbox.checked) {
                        warning.classList.remove('hidden');
                    } else {
                        warning.classList.add('hidden');
                    }
                }
            }
        </script>

        <!-- Submit Button -->
        <button type="submit"
            class="w-full py-4 rounded-xl bg-blue-500 hover:bg-blue-600 text-white font-bold transition-colors shadow-lg shadow-blue-500/20">
            Save Changes
        </button>
    </form>

    <!-- Delete Button -->
    <form action="{{ route('admin.classes.delete', $class->id) }}" method="POST" 
          onsubmit="return confirm('Are you sure you want to delete this class? This will also delete all bookings.')">
        @csrf
        @method('DELETE')
        <button type="submit"
            class="w-full py-3 rounded-xl border border-red-500/50 text-red-400 font-medium hover:bg-red-500/10 transition-colors">
            Delete Class
        </button>
    </form>
</div>
@endsection
