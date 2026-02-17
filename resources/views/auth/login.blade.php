<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catch Jiu Jitsu - Taiwan's Premier Jiu Jitsu Academy</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700&family=Noto+Sans+TC:wght@400;500;600;700&display=swap" rel="stylesheet">
    @include('partials.assets')
    @if(app()->getLocale() === 'zh-TW')
    <style>body { font-family: 'Noto Sans TC', 'Inter', sans-serif; }</style>
    @endif
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 font-sans antialiased">
    {{-- Menu bar: fixed top, logo left, globe right --}}
    <header class="fixed top-0 left-0 right-0 z-50 h-14 flex items-center justify-between px-4 bg-slate-950/95 backdrop-blur border-b border-white/5">
        <a href="{{ url('/') }}" class="flex items-center gap-2">
            <div class="w-8 h-8 rounded bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center font-bold text-black text-lg" style="font-family: 'Bebas Neue', sans-serif;">C</div>
            <span class="font-bold text-lg tracking-wider text-white" style="font-family: 'Bebas Neue', sans-serif;">CATCH <span class="text-amber-500">JIU JITSU</span></span>
        </a>
        <form action="{{ route('locale.switch') }}" method="POST" class="inline">
            @csrf
            <input type="hidden" name="locale" value="{{ app()->getLocale() === 'zh-TW' ? 'en' : 'zh-TW' }}">
            <button type="submit" class="w-10 h-10 rounded-full flex items-center justify-center text-slate-400 hover:text-white hover:bg-slate-800 transition-colors" title="{{ app()->getLocale() === 'zh-TW' ? 'Switch to English' : '切換至繁體中文' }}" aria-label="{{ app()->getLocale() === 'zh-TW' ? 'Switch to English' : '切換至繁體中文' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
            </button>
        </form>
    </header>
    <div class="min-h-screen flex items-center justify-center pt-14 pb-8 px-4">
    <div class="w-full max-w-sm">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center mx-auto mb-4">
                <span class="text-3xl font-bold text-black" style="font-family: 'Bebas Neue', sans-serif;">C</span>
            </div>
            <h1 class="text-3xl font-bold tracking-wider text-white" style="font-family: 'Bebas Neue', sans-serif;">
                CATCH <span class="text-amber-500">JIU JITSU</span>
            </h1>
            <p class="text-slate-400 text-sm mt-2">{{ app()->getLocale() === 'zh-TW' ? '會員入口' : 'Member Portal' }}</p>
        </div>

        <!-- Login Form -->
        <div class="glass rounded-2xl p-6">
            <h2 class="text-xl font-bold text-white mb-6">{{ __('app.auth.welcome_back') }}</h2>

            @if ($errors->any())
                <div class="mb-4 p-3 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ __('app.auth.email') }}</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors">
                </div>
                <div>
                    <label for="password" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ __('app.auth.password') }}</label>
                    <input type="password" id="password" name="password" required
                        class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors">
                </div>
                <div class="flex items-center">
                    <input type="checkbox" id="remember" name="remember" class="w-4 h-4 rounded bg-slate-800 border-slate-700 text-blue-500 focus:ring-blue-500">
                    <label for="remember" class="ml-2 text-sm text-slate-400">{{ __('app.auth.remember_me') }}</label>
                </div>
                <button type="submit"
                    class="w-full py-3 rounded-lg bg-blue-500 hover:bg-blue-600 text-white font-bold uppercase text-sm tracking-wider transition-colors shadow-lg shadow-blue-500/20">
                    {{ __('app.auth.sign_in') }}
                </button>
            </form>

            <p class="text-center text-slate-400 text-sm mt-6">
                {{ __('app.auth.dont_have_account') }}
                <a href="{{ route('register') }}" class="text-blue-400 hover:text-blue-300 font-medium">{{ __('app.auth.register') }}</a>
            </p>
        </div>
    </div>
    </div>
</body>
</html>
