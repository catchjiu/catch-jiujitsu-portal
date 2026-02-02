@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-3">
        <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-white transition-colors">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h1 class="text-xl font-bold text-white">Settings</h1>
    </div>

    @if(session('success'))
        <div class="p-3 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <!-- Profile Picture -->
    <div class="space-y-3">
        <div>
            <h2 class="text-lg font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">Profile Picture</h2>
            <p class="text-slate-500 text-sm">Upload a photo for your profile</p>
        </div>

        <div class="glass rounded-2xl p-5 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
            <div class="relative z-10">
                <div class="flex items-center gap-5">
                    <!-- Current Avatar -->
                    <div class="relative">
                        <div class="w-20 h-20 rounded-full overflow-hidden bg-slate-700 border-2 border-slate-600">
                            @if($user->avatar)
                                <img src="{{ $user->avatar }}" alt="Avatar" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-slate-400 text-2xl font-bold" style="font-family: 'Bebas Neue', sans-serif;">
                                    {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        @if($user->avatar_url)
                            <form action="{{ route('settings.avatar.remove') }}" method="POST" class="absolute -top-1 -right-1">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-6 h-6 rounded-full bg-red-500 hover:bg-red-600 text-white flex items-center justify-center transition-colors">
                                    <span class="material-symbols-outlined text-sm">close</span>
                                </button>
                            </form>
                        @endif
                    </div>

                    <!-- Upload Form -->
                    <form action="{{ route('settings.avatar') }}" method="POST" enctype="multipart/form-data" class="flex-1">
                        @csrf
                        <label class="block">
                            <input type="file" name="avatar" accept="image/*" required
                                class="block w-full text-sm text-slate-400
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-lg file:border-0
                                    file:text-sm file:font-semibold
                                    file:bg-blue-500 file:text-white
                                    hover:file:bg-blue-600
                                    file:cursor-pointer cursor-pointer">
                        </label>
                        <p class="text-slate-500 text-xs mt-2">JPG, PNG, GIF or WebP. Max 2MB (auto-compressed).</p>
                        @error('avatar')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <button type="submit" class="mt-3 px-4 py-2 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold transition-colors">
                            Upload Photo
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Settings -->
    <div class="space-y-3">
        <div>
            <h2 class="text-lg font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">Profile</h2>
            <p class="text-slate-500 text-sm">Update your personal information</p>
        </div>

        <form action="{{ route('settings.profile') }}" method="POST">
            @csrf
            
            <div class="glass rounded-2xl p-5 relative overflow-hidden space-y-4">
                <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
                <div class="relative z-10 space-y-4">
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">First Name</label>
                            <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" required
                                class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-blue-500 transition-colors">
                            @error('first_name')
                                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Last Name</label>
                            <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" required
                                class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-blue-500 transition-colors">
                            @error('last_name')
                                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                            class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-blue-500 transition-colors">
                        @error('email')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Privacy & Notifications -->
                    <div class="pt-4 border-t border-slate-700/50 space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-white font-medium">Public Profile</p>
                                <p class="text-slate-500 text-xs">Show on leaderboard</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="public_profile" value="1" class="sr-only peer" 
                                    {{ $user->public_profile ? 'checked' : '' }}>
                                <div class="w-12 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-white font-medium">Class Reminders</p>
                                <p class="text-slate-500 text-xs">Get notified about upcoming classes</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="reminders_enabled" value="1" class="sr-only peer" 
                                    {{ $user->reminders_enabled ? 'checked' : '' }}>
                                <div class="w-12 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
                            </label>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full py-3 rounded-lg bg-blue-500 hover:bg-blue-600 text-white font-bold uppercase text-sm tracking-wider transition-colors">
                        Save Profile
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Change Password -->
    <div class="space-y-3">
        <div>
            <h2 class="text-lg font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">Change Password</h2>
            <p class="text-slate-500 text-sm">Update your account password</p>
        </div>

        <form action="{{ route('settings.password') }}" method="POST">
            @csrf

            <div class="glass rounded-2xl p-5 relative overflow-hidden space-y-4">
                <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
                <div class="relative z-10 space-y-4">
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Current Password</label>
                        <input type="password" name="current_password" required
                            class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-blue-500 transition-colors"
                            placeholder="Enter current password">
                        @error('current_password')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">New Password</label>
                        <input type="password" name="password" required
                            class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-blue-500 transition-colors"
                            placeholder="Minimum 8 characters">
                        @error('password')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Confirm New Password</label>
                        <input type="password" name="password_confirmation" required
                            class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-blue-500 transition-colors"
                            placeholder="Confirm new password">
                    </div>

                    <button type="submit"
                        class="w-full py-3 rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-bold uppercase text-sm tracking-wider transition-colors">
                        Update Password
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Quick Links -->
    <div class="space-y-3">
        <a href="{{ route('goals') }}" class="glass rounded-2xl p-4 relative overflow-hidden flex items-center gap-4 hover:bg-slate-800/60 transition-colors">
            <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
            <div class="relative z-10 flex items-center gap-4 w-full">
                <div class="w-12 h-12 rounded-xl bg-amber-500/20 flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-amber-500">emoji_events</span>
                </div>
                <div class="flex-1">
                    <h3 class="text-white font-semibold">Training Goals</h3>
                    <p class="text-slate-500 text-xs">Set your monthly targets</p>
                </div>
                <span class="material-symbols-outlined text-slate-500">chevron_right</span>
            </div>
        </a>
    </div>

    <!-- Account Info -->
    <div class="glass rounded-2xl p-5 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
        <div class="relative z-10">
            <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-3">Account Info</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-500">Member Since</span>
                    <span class="text-white">{{ $user->created_at->format('M d, Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Belt Rank</span>
                    <span class="text-white">{{ $user->rank }} Belt ({{ $user->stripes }} stripes)</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Total Mat Hours</span>
                    <span class="text-white">{{ $user->calculated_mat_hours }} hours</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
