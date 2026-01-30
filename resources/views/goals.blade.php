@extends('layouts.app')

@section('title', 'Goals & Settings')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-3">
        <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-white transition-colors">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h1 class="text-xl font-bold text-white">Goals & Settings</h1>
    </div>

    <form action="{{ route('goals.update') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Training Goals Section -->
        <div class="space-y-3">
            <div>
                <h2 class="text-lg font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">Training Goals</h2>
                <p class="text-slate-500 text-sm">Set your targets for this month</p>
            </div>

            <!-- Monthly Classes Goal -->
            <div class="glass rounded-2xl p-4 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
                <div class="relative z-10 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-500/20 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-blue-500">emoji_events</span>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-white font-semibold">Monthly Classes</h3>
                        <p class="text-slate-500 text-xs">Target classes to attend</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="decrementValue('monthly_class_goal')" 
                            class="w-8 h-8 rounded-lg bg-slate-700 text-white hover:bg-slate-600 transition-colors flex items-center justify-center">
                            <span class="material-symbols-outlined text-lg">remove</span>
                        </button>
                        <input type="number" name="monthly_class_goal" id="monthly_class_goal" 
                            value="{{ old('monthly_class_goal', $user->monthly_class_goal) }}" 
                            min="1" max="50"
                            class="w-12 text-center py-1 rounded-lg bg-transparent text-white text-xl font-bold border-none focus:outline-none">
                        <button type="button" onclick="incrementValue('monthly_class_goal', 50)" 
                            class="w-8 h-8 rounded-lg bg-blue-500 text-white hover:bg-blue-600 transition-colors flex items-center justify-center">
                            <span class="material-symbols-outlined text-lg">add</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mat Hours Goal -->
            <div class="glass rounded-2xl p-4 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
                <div class="relative z-10 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-500/20 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-blue-500">schedule</span>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-white font-semibold">Mat Hours</h3>
                        <p class="text-slate-500 text-xs">Total time on the mats</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="decrementValue('monthly_hours_goal')" 
                            class="w-8 h-8 rounded-lg bg-slate-700 text-white hover:bg-slate-600 transition-colors flex items-center justify-center">
                            <span class="material-symbols-outlined text-lg">remove</span>
                        </button>
                        <input type="number" name="monthly_hours_goal" id="monthly_hours_goal" 
                            value="{{ old('monthly_hours_goal', $user->monthly_hours_goal) }}" 
                            min="1" max="100"
                            class="w-12 text-center py-1 rounded-lg bg-transparent text-white text-xl font-bold border-none focus:outline-none">
                        <button type="button" onclick="incrementValue('monthly_hours_goal', 100)" 
                            class="w-8 h-8 rounded-lg bg-blue-500 text-white hover:bg-blue-600 transition-colors flex items-center justify-center">
                            <span class="material-symbols-outlined text-lg">add</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Progress Section -->
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">Current Progress</h2>
                <span class="text-blue-500 text-sm font-medium">{{ $currentMonth }}</span>
            </div>

            <div class="glass rounded-2xl p-5 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
                <div class="relative z-10 space-y-5">
                    <!-- Classes Attended Progress -->
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-slate-300">Classes Attended</span>
                            <span class="text-white font-bold">
                                <span class="text-blue-500 text-xl" style="font-family: 'Bebas Neue', sans-serif;">{{ $classesAttended }}</span>
                                <span class="text-slate-500">/ {{ $user->monthly_class_goal }}</span>
                            </span>
                        </div>
                        <div class="h-2 bg-slate-700 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-blue-600 to-blue-400 rounded-full transition-all duration-500" 
                                 style="width: {{ $classProgress }}%"></div>
                        </div>
                        @if($classesRemaining > 0)
                            <p class="text-slate-500 text-xs mt-1 text-right">{{ $classesRemaining }} more to reach goal</p>
                        @else
                            <p class="text-emerald-400 text-xs mt-1 text-right">Goal reached! 🎉</p>
                        @endif
                    </div>

                    <!-- Hours Trained Progress -->
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-slate-300">Hours Trained</span>
                            <span class="text-white font-bold">
                                <span class="text-blue-500 text-xl" style="font-family: 'Bebas Neue', sans-serif;">{{ $hoursTrained }}</span>
                                <span class="text-slate-500">/ {{ $user->monthly_hours_goal }}</span>
                            </span>
                        </div>
                        <div class="h-2 bg-slate-700 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-blue-600 to-blue-400 rounded-full transition-all duration-500" 
                                 style="width: {{ $hoursProgress }}%"></div>
                        </div>
                        @if($hoursRemaining > 0)
                            <p class="text-slate-500 text-xs mt-1 text-right">{{ $hoursRemaining }} hrs to reach goal</p>
                        @else
                            <p class="text-emerald-400 text-xs mt-1 text-right">Goal reached! 🎉</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Preferences Section -->
        <div class="space-y-3">
            <h2 class="text-lg font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">Preferences</h2>

            <div class="glass rounded-2xl overflow-hidden relative">
                <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
                <div class="relative z-10">
                    <!-- Reminders -->
                    <div class="flex items-center justify-between p-4 border-b border-slate-700/50">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-slate-700/50 flex items-center justify-center">
                                <span class="material-symbols-outlined text-slate-400">notifications</span>
                            </div>
                            <div>
                                <h3 class="text-white font-medium">Reminders</h3>
                                <p class="text-slate-500 text-xs">Get notified before class</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="reminders_enabled" value="1" class="sr-only peer" 
                                {{ $user->reminders_enabled ? 'checked' : '' }}>
                            <div class="w-12 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
                        </label>
                    </div>

                    <!-- Public Profile -->
                    <div class="flex items-center justify-between p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-slate-700/50 flex items-center justify-center">
                                <span class="material-symbols-outlined text-slate-400">visibility</span>
                            </div>
                            <div>
                                <h3 class="text-white font-medium">Public Profile</h3>
                                <p class="text-slate-500 text-xs">Allow members to see stats</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="public_profile" value="1" class="sr-only peer" 
                                {{ $user->public_profile ? 'checked' : '' }}>
                            <div class="w-12 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Save Button -->
        <button type="submit"
            class="w-full py-4 rounded-xl bg-blue-500 hover:bg-blue-600 text-white font-bold transition-colors shadow-lg shadow-blue-500/20 flex items-center justify-center gap-2">
            <span class="material-symbols-outlined">save</span>
            Save Changes
        </button>
    </form>
</div>

<script>
function incrementValue(id, max) {
    const input = document.getElementById(id);
    const current = parseInt(input.value) || 0;
    if (current < max) input.value = current + 1;
}

function decrementValue(id) {
    const input = document.getElementById(id);
    const current = parseInt(input.value) || 0;
    if (current > 1) input.value = current - 1;
}
</script>
@endsection
