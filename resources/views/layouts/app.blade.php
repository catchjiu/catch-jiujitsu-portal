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
                {{-- Language switcher: globe toggles English / Traditional Chinese --}}
                <form action="{{ route('locale.switch') }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="locale" value="{{ app()->getLocale() === 'zh-TW' ? 'en' : 'zh-TW' }}">
                    <button type="submit" class="w-9 h-9 rounded-full flex items-center justify-center text-slate-400 hover:text-white hover:bg-slate-800 transition-colors" title="{{ app()->getLocale() === 'zh-TW' ? 'Switch to English' : '切換至繁體中文' }}" aria-label="{{ app()->getLocale() === 'zh-TW' ? 'Switch to English' : '切換至繁體中文' }}">
                        <span class="material-symbols-outlined text-xl">language</span>
                    </button>
                </form>
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
            @if(auth()->user()->is_coach ?? false)
            <a href="{{ route('coach.private-availability') }}" class="flex flex-col items-center justify-center w-full h-full space-y-0.5 transition-colors {{ request()->routeIs('coach.private*') ? 'text-blue-500' : 'text-slate-500 hover:text-slate-300' }}">
                <span class="material-symbols-outlined text-2xl">person_search</span>
                <span class="text-[10px] font-medium">{{ app()->getLocale() === 'zh-TW' ? '私教' : 'Private' }}</span>
            </a>
            @endif
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

    <!-- Private Class Modal (global for member booking from dashboard or schedule) -->
    <div id="privateClassModal" class="hidden fixed inset-0 z-[9998] flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
         onclick="if(event.target===this) typeof closePrivateClassModal === 'function' && closePrivateClassModal()">
        <div class="glass rounded-2xl p-6 max-w-sm w-full max-h-[90vh] overflow-hidden flex flex-col relative" onclick="event.stopPropagation()">
            <button type="button" onclick="typeof closePrivateClassModal === 'function' && closePrivateClassModal()" class="absolute top-4 right-4 text-slate-400 hover:text-white z-10">
                <span class="material-symbols-outlined">close</span>
            </button>
            <h3 class="text-xl font-bold text-white mb-4 pr-8" style="font-family: 'Bebas Neue', sans-serif;">{{ app()->getLocale() === 'zh-TW' ? '預約私教課' : 'Book private class' }}</h3>
            <div id="privateClassStep1" class="space-y-3 overflow-y-auto flex-1 min-h-0">
                <p class="text-slate-500 text-sm">{{ app()->getLocale() === 'zh-TW' ? '選擇日期' : 'Choose a day' }}</p>
                <div id="privateClassDays" class="space-y-2"></div>
                <p id="privateClassDaysLoading" class="text-slate-500 text-sm py-4 text-center">{{ app()->getLocale() === 'zh-TW' ? '載入中…' : 'Loading…' }}</p>
                <p id="privateClassDaysEmpty" class="hidden text-slate-500 text-sm py-4 text-center">{{ app()->getLocale() === 'zh-TW' ? '目前沒有可預約日期' : 'No available days right now.' }}</p>
            </div>
            <div id="privateClassStep2" class="hidden flex flex-col flex-1 min-h-0">
                <button type="button" onclick="typeof privateClassBackToDays === 'function' && privateClassBackToDays()" class="text-slate-400 hover:text-white text-sm mb-2 flex items-center gap-1">
                    <span class="material-symbols-outlined text-lg">arrow_back</span> {{ app()->getLocale() === 'zh-TW' ? '返回選擇日期' : 'Back to days' }}
                </button>
                <p id="privateClassSelectedDay" class="text-white font-semibold mb-2"></p>
                <p class="text-slate-500 text-sm mb-2">{{ app()->getLocale() === 'zh-TW' ? '選擇時段' : 'Choose a time' }}</p>
                <div id="privateClassSlots" class="space-y-2 overflow-y-auto flex-1 min-h-0 mb-4"></div>
                <form id="privateClassRequestForm" action="{{ route('private-class.request') }}" method="POST" class="hidden">
                    @csrf
                    <input type="hidden" name="coach_id" id="privateClassCoachId">
                    <input type="hidden" name="scheduled_at" id="privateClassScheduledAt">
                    <input type="hidden" name="duration_minutes" value="60">
                </form>
            </div>
        </div>
    </div>

    <script>
    (function() {
        var modal = document.getElementById('privateClassModal');
        if (!modal) return;
        var daysEl = document.getElementById('privateClassDays');
        var daysLoadingEl = document.getElementById('privateClassDaysLoading');
        var daysEmptyEl = document.getElementById('privateClassDaysEmpty');
        var step1 = document.getElementById('privateClassStep1');
        var step2 = document.getElementById('privateClassStep2');
        var selectedDayEl = document.getElementById('privateClassSelectedDay');
        var slotsEl = document.getElementById('privateClassSlots');
        var form = document.getElementById('privateClassRequestForm');
        var coachIdInput = document.getElementById('privateClassCoachId');
        var scheduledAtInput = document.getElementById('privateClassScheduledAt');

        window.closePrivateClassModal = function() {
            modal.classList.add('hidden');
            step1.classList.remove('hidden');
            step2.classList.add('hidden');
        };

        window.privateClassBackToDays = function() {
            step2.classList.add('hidden');
            step1.classList.remove('hidden');
        };

        function openModalAndLoadDays() {
            modal.classList.remove('hidden');
            step2.classList.add('hidden');
            step1.classList.remove('hidden');
            daysLoadingEl.classList.remove('hidden');
            daysEmptyEl.classList.add('hidden');
            daysEl.innerHTML = '';
            fetch('{{ route("private-class.days") }}', { headers: { 'Accept': 'application/json' } })
                .then(function(r) { return r.json(); })
                .then(function(days) {
                    daysLoadingEl.classList.add('hidden');
                    if (!days || days.length === 0) {
                        daysEmptyEl.classList.remove('hidden');
                        return;
                    }
                    days.forEach(function(d) {
                        var btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'w-full p-4 rounded-xl bg-slate-800/50 border border-slate-700/50 hover:bg-violet-500/20 hover:border-violet-500/50 text-white text-sm text-left transition-colors';
                        btn.textContent = d.label || d.date;
                        btn.dataset.date = d.date;
                        btn.onclick = function() { loadSlotsForDay(d.date, d.label); };
                        daysEl.appendChild(btn);
                    });
                })
                .catch(function() {
                    daysLoadingEl.classList.add('hidden');
                    daysEmptyEl.textContent = '{{ app()->getLocale() === "zh-TW" ? "載入失敗" : "Failed to load." }}';
                    daysEmptyEl.classList.remove('hidden');
                });
        }

        document.getElementById('openPrivateClassModal')?.addEventListener('click', openModalAndLoadDays);
        document.getElementById('openPrivateClassModalSchedule')?.addEventListener('click', openModalAndLoadDays);

        function loadSlotsForDay(date, dayLabel) {
            step1.classList.add('hidden');
            step2.classList.remove('hidden');
            selectedDayEl.textContent = dayLabel || date;
            slotsEl.innerHTML = '<p class="text-slate-500 text-sm py-4 text-center">{{ app()->getLocale() === "zh-TW" ? "載入時段…" : "Loading slots…" }}</p>';
            fetch('{{ route("private-class.slots") }}?date=' + encodeURIComponent(date), { headers: { 'Accept': 'application/json' } })
                .then(function(r) { return r.json(); })
                .then(function(slots) {
                    slotsEl.innerHTML = '';
                    if (!slots || slots.length === 0) {
                        slotsEl.innerHTML = '<p class="text-slate-500 text-sm py-4 text-center">{{ app()->getLocale() === "zh-TW" ? "當日暫無可預約時段" : "No slots for this day." }}</p>';
                        return;
                    }
                    slots.forEach(function(s) {
                        var btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'w-full flex items-center gap-3 p-3 rounded-xl bg-slate-800/50 border border-slate-700/50 hover:bg-violet-500/20 hover:border-violet-500/50 text-white text-sm text-left transition-colors';
                        var coachHtml = s.coach_avatar ? '<img src="' + s.coach_avatar + '" alt="" class="w-10 h-10 rounded-full object-cover border-2 border-slate-600 flex-shrink-0">' : '<div class="w-10 h-10 rounded-full bg-slate-700 border-2 border-slate-600 flex items-center justify-center text-slate-400 font-bold text-sm flex-shrink-0">' + (s.coach_name ? s.coach_name.substring(0, 2).toUpperCase() : '') + '</div>';
                        btn.innerHTML = '<div class="flex-shrink-0">' + coachHtml + '</div><div class="flex-1 min-w-0"><span class="font-semibold text-white">' + (s.label || '') + '</span><br><span class="text-slate-500 text-xs">' + (s.coach_name || '') + (s.coach_price ? ' · NT$' + s.coach_price.toLocaleString() : '') + '</span></div><span class="material-symbols-outlined text-slate-500 flex-shrink-0">chevron_right</span>';
                        btn.onclick = function() {
                            coachIdInput.value = s.coach_id;
                            scheduledAtInput.value = s.datetime ? s.datetime.replace('Z', '').replace(/\.[0-9]+/, '').slice(0, 19) : (date + 'T' + (s.label || '').split(' at ')[1] || '09:00');
                            form.submit();
                        };
                        slotsEl.appendChild(btn);
                    });
                })
                .catch(function() {
                    slotsEl.innerHTML = '<p class="text-slate-500 text-sm py-4 text-center">{{ app()->getLocale() === "zh-TW" ? "載入失敗" : "Failed to load." }}</p>';
                });
        }
    })();
    </script>
    @endauth
</body>
</html>
