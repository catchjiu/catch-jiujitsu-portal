@extends('layouts.app')

@section('title', __('app.settings.settings'))

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-3">
        <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-white transition-colors">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h1 class="text-xl font-bold text-white">{{ __('app.settings.settings') }}</h1>
    </div>

    @if(session('success'))
        <div class="p-3 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <!-- Profile Picture -->
    <div class="space-y-3">
        <div>
            <h2 class="text-lg font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">{{ __('app.settings.profile') }}</h2>
            <p class="text-slate-500 text-sm">{{ __('app.settings.tap_to_change') }}</p>
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
                        <p class="text-slate-500 text-xs mt-2">JPG, PNG, GIF or WebP. Max 2MB.</p>
                        @error('avatar')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <button type="submit" class="mt-3 px-4 py-2 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold transition-colors">
                            {{ __('app.settings.change_photo') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Settings -->
    <div class="space-y-3">
        <div>
            <h2 class="text-lg font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">{{ __('app.settings.profile') }}</h2>
            <p class="text-slate-500 text-sm">{{ app()->getLocale() === 'zh-TW' ? '更新您的個人資訊' : 'Update your personal information' }}</p>
        </div>

        <form action="{{ route('settings.profile') }}" method="POST">
            @csrf
            
            <div class="glass rounded-2xl p-5 relative overflow-hidden space-y-4">
                <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
                <div class="relative z-10 space-y-4">
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ __('app.settings.first_name') }}</label>
                            <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" required
                                class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-blue-500 transition-colors">
                            @error('first_name')
                                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ __('app.settings.last_name') }}</label>
                            <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" required
                                class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-blue-500 transition-colors">
                            @error('last_name')
                                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ __('app.settings.email') }}</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                            class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-blue-500 transition-colors">
                        @error('email')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ app()->getLocale() === 'zh-TW' ? '出生日期' : 'Date of Birth' }}</label>
                        <input type="date" name="dob" value="{{ old('dob', $user->dob ? $user->dob->format('Y-m-d') : '') }}"
                            class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-blue-500 transition-colors">
                        @error('dob')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        @if($user->dob && $user->bjj_age_category)
                            <p class="text-amber-400 text-xs font-semibold mt-2">{{ app()->getLocale() === 'zh-TW' ? 'BJJ 年齡組別' : 'BJJ age category' }}: {{ $user->bjj_age_category }}</p>
                        @endif
                    </div>

                    <!-- Privacy & Notifications -->
                    <div class="pt-4 border-t border-slate-700/50 space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-white font-medium">{{ app()->getLocale() === 'zh-TW' ? '公開個人資料' : 'Public Profile' }}</p>
                                <p class="text-slate-500 text-xs">{{ app()->getLocale() === 'zh-TW' ? '顯示在排行榜上' : 'Show on leaderboard' }}</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="public_profile" value="1" class="sr-only peer" 
                                    {{ $user->public_profile ? 'checked' : '' }}>
                                <div class="w-12 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-white font-medium">{{ app()->getLocale() === 'zh-TW' ? '課程提醒' : 'Class Reminders' }}</p>
                                <p class="text-slate-500 text-xs">{{ app()->getLocale() === 'zh-TW' ? '接收即將到來的課程通知' : 'Get notified about upcoming classes' }}</p>
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
                        {{ __('app.settings.save_profile') }}
                    </button>
                </div>
            </div>
        </form>
    </div>

    @if($user->is_coach)
    <!-- Private Classes (Coach) -->
    <div class="space-y-3">
        <div>
            <h2 class="text-lg font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">{{ app()->getLocale() === 'zh-TW' ? '一對一私教' : 'Private Classes' }}</h2>
            <p class="text-slate-500 text-sm">{{ app()->getLocale() === 'zh-TW' ? '開放會員預約私教課並設定價格' : 'Accept private class requests and set your price' }}</p>
        </div>

        <form action="{{ route('settings.private-class') }}" method="POST">
            @csrf
            <div class="glass rounded-2xl p-5 relative overflow-hidden space-y-4">
                <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
                <div class="relative z-10 space-y-4">
                    <div class="flex items-center justify-between p-4 rounded-lg bg-slate-800/50 border border-slate-700/50">
                        <div>
                            <p class="text-white font-medium">{{ app()->getLocale() === 'zh-TW' ? '接受私教預約' : 'Accepting private classes' }}</p>
                            <p class="text-slate-500 text-xs">{{ app()->getLocale() === 'zh-TW' ? '會員可看到您並預約時段' : 'Members can see you and request time slots' }}</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="accepting_private_classes" value="1" class="sr-only peer" {{ $user->accepting_private_classes ? 'checked' : '' }}>
                            <div class="w-12 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                        </label>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ app()->getLocale() === 'zh-TW' ? '私教課價格 (NT$)' : 'Private class price (NT$)' }}</label>
                        <input type="number" name="private_class_price" value="{{ old('private_class_price', $user->private_class_price) }}" min="0" step="1" placeholder="e.g. 1500"
                            class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-blue-500 transition-colors">
                        @error('private_class_price')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="w-full py-3 rounded-lg bg-blue-500 hover:bg-blue-600 text-white font-bold uppercase text-sm tracking-wider transition-colors">
                        {{ __('app.common.save') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
    @endif

    <!-- Change Password -->
    <div class="space-y-3">
        <div>
            <h2 class="text-lg font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">{{ __('app.settings.change_password') }}</h2>
            <p class="text-slate-500 text-sm">{{ app()->getLocale() === 'zh-TW' ? '更新您的帳號密碼' : 'Update your account password' }}</p>
        </div>

        <form action="{{ route('settings.password') }}" method="POST">
            @csrf

            <div class="glass rounded-2xl p-5 relative overflow-hidden space-y-4">
                <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
                <div class="relative z-10 space-y-4">
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ __('app.settings.current_password') }}</label>
                        <input type="password" name="current_password" required
                            class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-blue-500 transition-colors"
                            placeholder="{{ app()->getLocale() === 'zh-TW' ? '輸入目前密碼' : 'Enter current password' }}">
                        @error('current_password')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ __('app.settings.new_password') }}</label>
                        <input type="password" name="password" required
                            class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-blue-500 transition-colors"
                            placeholder="{{ app()->getLocale() === 'zh-TW' ? '最少8個字元' : 'Minimum 8 characters' }}">
                        @error('password')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ __('app.settings.confirm_password') }}</label>
                        <input type="password" name="password_confirmation" required
                            class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:border-blue-500 transition-colors"
                            placeholder="{{ app()->getLocale() === 'zh-TW' ? '確認新密碼' : 'Confirm new password' }}">
                    </div>

                    <button type="submit"
                        class="w-full py-3 rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-bold uppercase text-sm tracking-wider transition-colors">
                        {{ __('app.settings.update_password') }}
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Language Settings -->
    <div class="space-y-3">
        <div>
            <h2 class="text-lg font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">{{ __('app.settings.language') }}</h2>
            <p class="text-slate-500 text-sm">{{ __('app.settings.select_language') }}</p>
        </div>

        <form action="{{ route('settings.locale') }}" method="POST">
            @csrf

            <div class="glass rounded-2xl p-5 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
                <div class="relative z-10 space-y-4">
                    
                    <div class="flex gap-3">
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="locale" value="en" class="sr-only peer" {{ app()->getLocale() === 'en' ? 'checked' : '' }}>
                            <div class="p-4 rounded-xl border-2 border-slate-700 peer-checked:border-blue-500 peer-checked:bg-blue-500/10 transition-all">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">🇺🇸</span>
                                    <div>
                                        <p class="text-white font-semibold">English</p>
                                        <p class="text-slate-500 text-xs">English (US)</p>
                                    </div>
                                </div>
                            </div>
                        </label>
                        
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="locale" value="zh-TW" class="sr-only peer" {{ app()->getLocale() === 'zh-TW' ? 'checked' : '' }}>
                            <div class="p-4 rounded-xl border-2 border-slate-700 peer-checked:border-blue-500 peer-checked:bg-blue-500/10 transition-all">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">🇹🇼</span>
                                    <div>
                                        <p class="text-white font-semibold">繁體中文</p>
                                        <p class="text-slate-500 text-xs">Traditional Chinese</p>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>

                    <button type="submit"
                        class="w-full py-3 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white font-bold uppercase text-sm tracking-wider transition-colors">
                        {{ __('app.common.save') }}
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
                    <h3 class="text-white font-semibold">{{ app()->getLocale() === 'zh-TW' ? '訓練目標' : 'Training Goals' }}</h3>
                    <p class="text-slate-500 text-xs">{{ app()->getLocale() === 'zh-TW' ? '設定每月目標' : 'Set your monthly targets' }}</p>
                </div>
                <span class="material-symbols-outlined text-slate-500">chevron_right</span>
            </div>
        </a>
    </div>

    <!-- Account Info -->
    <div class="glass rounded-2xl p-5 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
        <div class="relative z-10">
            <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-3">{{ app()->getLocale() === 'zh-TW' ? '帳號資訊' : 'Account Info' }}</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-500">{{ app()->getLocale() === 'zh-TW' ? '會員自' : 'Member Since' }}</span>
                    <span class="text-white">{{ $user->created_at->format('M d, Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">{{ app()->getLocale() === 'zh-TW' ? '腰帶等級' : 'Belt Rank' }}</span>
                    <span class="text-white">{{ $user->rank }} {{ app()->getLocale() === 'zh-TW' ? '帶' : 'Belt' }} ({{ $user->stripes }} {{ app()->getLocale() === 'zh-TW' ? '條紋' : 'stripes' }})</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">{{ __('app.settings.total_mat_hours') }}</span>
                    <span class="text-white">{{ $user->calculated_mat_hours }} {{ __('app.settings.hours') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
