<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Catch Jiu Jitsu - Taiwan's Premier Jiu Jitsu Academy</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700&family=Noto+Sans+TC:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@400" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @if(app()->getLocale() === 'zh-TW')
    <style>
        body { font-family: 'Noto Sans TC', 'Inter', sans-serif; }
    </style>
    @endif
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 font-sans antialiased">
    <!-- Top Bar -->
    <header class="fixed top-0 w-full z-50 bg-slate-950/80 backdrop-blur-md border-b border-white/5 px-4 sm:px-6 py-4">
        <div class="max-w-lg mx-auto flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center font-bold text-black text-lg" style="font-family: 'Bebas Neue', sans-serif;">C</div>
                <h1 class="font-bold text-xl tracking-wider text-white" style="font-family: 'Bebas Neue', sans-serif;">
                    CATCH <span class="text-amber-500">JIU JITSU</span>
                </h1>
            </div>
            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ route('settings') }}" class="w-8 h-8 rounded-full overflow-hidden border-2 border-slate-700 bg-slate-800 hover:border-blue-500 transition-colors">
                        @if(auth()->user()->avatar)
                            <img src="{{ auth()->user()->avatar }}" alt="Avatar" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-slate-400 text-sm font-bold">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                        @endif
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="pt-20 pb-24 px-4 max-w-lg mx-auto">
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mb-4 p-4 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm animate-fade-in">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-4 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 text-sm animate-fade-in">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Bottom Navigation -->
    @auth
    <nav class="fixed bottom-0 left-0 w-full bg-slate-900/90 backdrop-blur-lg border-t border-white/10 z-50 pb-safe">
        <div class="flex justify-around items-center h-16 max-w-lg mx-auto">
            @php $isFamily = auth()->user()->isInFamily() ?? false; @endphp
            <a href="{{ $isFamily ? route('family.dashboard') : route('dashboard') }}" class="flex flex-col items-center justify-center w-full h-full space-y-0.5 transition-colors {{ (request()->routeIs('dashboard') || request()->routeIs('family.dashboard')) ? 'text-blue-500' : 'text-slate-500 hover:text-slate-300' }}">
                <span class="material-symbols-outlined text-2xl">home</span>
                <span class="text-[10px] font-medium">{{ $isFamily ? (app()->getLocale() === 'zh-TW' ? '家庭' : 'Family') : __('app.nav.home') }}</span>
            </a>
            <a href="{{ route('schedule') }}" class="flex flex-col items-center justify-center w-full h-full space-y-0.5 transition-colors {{ request()->routeIs('schedule') ? 'text-blue-500' : 'text-slate-500 hover:text-slate-300' }}">
                <span class="material-symbols-outlined text-2xl">calendar_today</span>
                <span class="text-[10px] font-medium">{{ __('app.nav.schedule') }}</span>
            </a>
            <a href="{{ $isFamily ? route('family.settings') : route('settings') }}" class="flex flex-col items-center justify-center w-full h-full space-y-0.5 transition-colors {{ (request()->routeIs('settings') || request()->routeIs('family.settings')) ? 'text-blue-500' : 'text-slate-500 hover:text-slate-300' }}">
                <span class="material-symbols-outlined text-2xl">settings</span>
                <span class="text-[10px] font-medium">{{ __('app.nav.settings') }}</span>
            </a>
            <a href="{{ route('payments') }}" class="flex flex-col items-center justify-center w-full h-full space-y-0.5 transition-colors {{ request()->routeIs('payments') ? 'text-blue-500' : 'text-slate-500 hover:text-slate-300' }}">
                <span class="material-symbols-outlined text-2xl">payments</span>
                <span class="text-[10px] font-medium">{{ __('app.nav.payments') }}</span>
            </a>
            <form action="{{ route('logout') }}" method="POST" class="w-full h-full">
                @csrf
                <button type="submit" class="flex flex-col items-center justify-center w-full h-full space-y-0.5 text-slate-500 hover:text-red-400 transition-colors">
                    <span class="material-symbols-outlined text-2xl">logout</span>
                    <span class="text-[10px] font-medium">{{ __('app.nav.logout') }}</span>
                </button>
            </form>
        </div>
    </nav>
    @endauth
</body>
</html>
