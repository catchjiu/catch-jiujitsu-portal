<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Catch Jiu Jitsu') }} - @yield('title', 'Admin')</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL@20..48,100..700,0..1" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .material-symbols-outlined.filled {
            font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 font-sans antialiased">
    <!-- Slide-out Menu Overlay -->
    <div id="menuOverlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 opacity-0 pointer-events-none transition-opacity duration-300"></div>

    <!-- Slide-out Menu -->
    <div id="slideMenu" class="fixed top-0 left-0 h-full w-72 bg-slate-900 z-50 transform -translate-x-full transition-transform duration-300 shadow-2xl">
        <div class="p-5">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-xl font-bold text-white" style="font-family: 'Bebas Neue', sans-serif;">Admin Menu</h2>
                <button onclick="closeMenu()" class="text-slate-400 hover:text-white transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <!-- Menu Items -->
            <nav class="space-y-2">
                <a href="{{ route('admin.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('admin.index') ? 'bg-blue-500/20 text-blue-400' : 'text-slate-300 hover:bg-slate-800' }} transition-colors">
                    <span class="material-symbols-outlined">dashboard</span>
                    <span class="font-medium">Dashboard</span>
                </a>
                <a href="{{ route('admin.members') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('admin.members*') ? 'bg-blue-500/20 text-blue-400' : 'text-slate-300 hover:bg-slate-800' }} transition-colors">
                    <span class="material-symbols-outlined">groups</span>
                    <span class="font-medium">Members</span>
                </a>
                <a href="{{ route('admin.classes') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('admin.classes*') ? 'bg-blue-500/20 text-blue-400' : 'text-slate-300 hover:bg-slate-800' }} transition-colors">
                    <span class="material-symbols-outlined">calendar_today</span>
                    <span class="font-medium">Classes</span>
                </a>
                <a href="{{ route('admin.finance') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('admin.finance') ? 'bg-blue-500/20 text-blue-400' : 'text-slate-300 hover:bg-slate-800' }} transition-colors">
                    <span class="material-symbols-outlined">account_balance</span>
                    <span class="font-medium">Finance</span>
                </a>
                <a href="{{ route('admin.payments') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('admin.payments') ? 'bg-blue-500/20 text-blue-400' : 'text-slate-300 hover:bg-slate-800' }} transition-colors">
                    <span class="material-symbols-outlined">payments</span>
                    <span class="font-medium">Payment Verification</span>
                </a>

                <div class="border-t border-slate-700 my-4"></div>
                
                <p class="px-4 text-xs text-slate-500 uppercase tracking-wider font-bold mb-2">Settings</p>
                
                <a href="{{ route('admin.packages.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->routeIs('admin.packages*') ? 'bg-blue-500/20 text-blue-400' : 'text-slate-300 hover:bg-slate-800' }} transition-colors">
                    <span class="material-symbols-outlined">inventory_2</span>
                    <span class="font-medium">Membership Packages</span>
                </a>
            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <main class="pb-20 px-4 max-w-lg mx-auto pt-4">
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
    <nav class="fixed bottom-0 left-0 w-full bg-slate-900/95 backdrop-blur-lg border-t border-slate-800 z-50 pb-safe">
        <div class="flex justify-around items-center h-16 max-w-lg mx-auto">
            <a href="{{ route('admin.index') }}" class="flex flex-col items-center justify-center w-full h-full space-y-0.5 transition-colors {{ request()->routeIs('admin.index') ? 'text-blue-500' : 'text-slate-500 hover:text-slate-300' }}">
                <span class="material-symbols-outlined {{ request()->routeIs('admin.index') ? 'filled' : '' }}" style="font-size: 24px;">home</span>
                <span class="text-[10px] font-medium">Home</span>
            </a>
            <a href="{{ route('admin.members') }}" class="flex flex-col items-center justify-center w-full h-full space-y-0.5 transition-colors {{ request()->routeIs('admin.members*') ? 'text-blue-500' : 'text-slate-500 hover:text-slate-300' }}">
                <span class="material-symbols-outlined {{ request()->routeIs('admin.members*') ? 'filled' : '' }}" style="font-size: 24px;">groups</span>
                <span class="text-[10px] font-medium">Members</span>
            </a>
            <a href="{{ route('admin.classes') }}" class="flex flex-col items-center justify-center w-full h-full space-y-0.5 transition-colors {{ request()->routeIs('admin.classes*') || request()->routeIs('admin.attendance*') ? 'text-blue-500' : 'text-slate-500 hover:text-slate-300' }}">
                <span class="material-symbols-outlined {{ request()->routeIs('admin.classes*') ? 'filled' : '' }}" style="font-size: 24px;">calendar_today</span>
                <span class="text-[10px] font-medium">Classes</span>
            </a>
            <a href="{{ route('admin.finance') }}" class="flex flex-col items-center justify-center w-full h-full space-y-0.5 transition-colors {{ request()->routeIs('admin.finance') || request()->routeIs('admin.payments') ? 'text-blue-500' : 'text-slate-500 hover:text-slate-300' }}">
                <span class="material-symbols-outlined {{ request()->routeIs('admin.finance') ? 'filled' : '' }}" style="font-size: 24px;">account_balance</span>
                <span class="text-[10px] font-medium">Finance</span>
            </a>
            <form action="{{ route('logout') }}" method="POST" class="w-full h-full">
                @csrf
                <button type="submit" class="flex flex-col items-center justify-center w-full h-full space-y-0.5 text-slate-500 hover:text-red-400 transition-colors">
                    <span class="material-symbols-outlined" style="font-size: 24px;">logout</span>
                    <span class="text-[10px] font-medium">Logout</span>
                </button>
            </form>
        </div>
    </nav>

    <script>
        function openMenu() {
            document.getElementById('slideMenu').classList.remove('-translate-x-full');
            document.getElementById('menuOverlay').classList.remove('opacity-0', 'pointer-events-none');
        }
        
        function closeMenu() {
            document.getElementById('slideMenu').classList.add('-translate-x-full');
            document.getElementById('menuOverlay').classList.add('opacity-0', 'pointer-events-none');
        }
        
        document.getElementById('menuOverlay').addEventListener('click', closeMenu);
    </script>
    @yield('scripts')
</body>
</html>
