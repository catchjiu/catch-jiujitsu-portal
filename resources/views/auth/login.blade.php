<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Catch Jiu Jitsu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 font-sans antialiased flex items-center justify-center p-4">
    <div class="w-full max-w-sm">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center mx-auto mb-4">
                <span class="text-3xl font-bold text-black" style="font-family: 'Bebas Neue', sans-serif;">C</span>
            </div>
            <h1 class="text-3xl font-bold tracking-wider text-white" style="font-family: 'Bebas Neue', sans-serif;">
                CATCH <span class="text-amber-500">JIU JITSU</span>
            </h1>
            <p class="text-slate-400 text-sm mt-2">Member Portal</p>
        </div>

        <!-- Login Form -->
        <div class="glass rounded-2xl p-6">
            <h2 class="text-xl font-bold text-white mb-6">Welcome Back</h2>

            @if ($errors->any())
                <div class="mb-4 p-3 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors">
                </div>
                <div>
                    <label for="password" class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Password</label>
                    <input type="password" id="password" name="password" required
                        class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-colors">
                </div>
                <div class="flex items-center">
                    <input type="checkbox" id="remember" name="remember" class="w-4 h-4 rounded bg-slate-800 border-slate-700 text-blue-500 focus:ring-blue-500">
                    <label for="remember" class="ml-2 text-sm text-slate-400">Remember me</label>
                </div>
                <button type="submit"
                    class="w-full py-3 rounded-lg bg-blue-500 hover:bg-blue-600 text-white font-bold uppercase text-sm tracking-wider transition-colors shadow-lg shadow-blue-500/20">
                    Sign In
                </button>
            </form>

            <p class="text-center text-slate-400 text-sm mt-6">
                Don't have an account? 
                <a href="{{ route('register') }}" class="text-blue-400 hover:text-blue-300 font-medium">Register</a>
            </p>
        </div>
    </div>
</body>
</html>
