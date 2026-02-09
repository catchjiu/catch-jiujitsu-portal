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
    <!-- modals-v2: Check In + Private open on top via body delegation -->
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
                    <a href="{{ route('family.settings') }}" class="w-8 h-8 rounded-full overflow-hidden border-2 border-slate-700 bg-slate-800 hover:border-blue-500 transition-colors" title="{{ app()->getLocale() === 'zh-TW' ? '切換家庭成員' : 'Switch family member' }}">
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
            <a href="{{ $isFamily ? route('family.dashboard') : route('dashboard') }}" class="flex flex-col items-center justify-center w-full h-full space-y-0.5 transition-colors {{ (request()->routeIs('dashboard') || request()->routeIs('family.dashboard')) ? 'text-[#00d4ff]' : 'text-slate-500 hover:text-slate-300' }}">
                <span class="material-symbols-outlined text-2xl">home</span>
                <span class="text-[10px] font-medium">{{ __('app.nav.home') }}</span>
            </a>
            <a href="{{ route('schedule') }}" class="flex flex-col items-center justify-center w-full h-full space-y-0.5 transition-colors {{ request()->routeIs('schedule') ? 'text-[#00d4ff]' : 'text-slate-500 hover:text-slate-300' }}">
                <span class="material-symbols-outlined text-2xl">calendar_today</span>
                <span class="text-[10px] font-medium">{{ __('app.nav.schedule') }}</span>
            </a>
            <a href="{{ route('shop.index') }}" class="flex flex-col items-center justify-center w-full h-full space-y-0.5 transition-colors {{ request()->routeIs('shop.*') ? 'text-[#00d4ff]' : 'text-slate-500 hover:text-slate-300' }}">
                <span class="material-symbols-outlined text-2xl">storefront</span>
                <span class="text-[10px] font-medium">{{ __('app.nav.shop') }}</span>
            </a>
            @if(auth()->user()->is_coach ?? false)
            <a href="{{ route('coach.private-availability') }}" class="flex flex-col items-center justify-center w-full h-full space-y-0.5 transition-colors {{ request()->routeIs('coach.private*') ? 'text-blue-500' : 'text-slate-500 hover:text-slate-300' }}">
                <span class="material-symbols-outlined text-2xl">person_search</span>
                <span class="text-[10px] font-medium">{{ app()->getLocale() === 'zh-TW' ? '私教' : 'Private' }}</span>
            </a>
            @endif
            <a href="{{ route('settings') }}" class="flex flex-col items-center justify-center w-full h-full space-y-0.5 transition-colors {{ request()->routeIs('settings') ? 'text-[#00d4ff]' : 'text-slate-500 hover:text-slate-300' }}">
                <span class="material-symbols-outlined text-2xl">settings</span>
                <span class="text-[10px] font-medium">{{ __('app.nav.settings') }}</span>
            </a>
            <a href="{{ route('payments') }}" class="flex flex-col items-center justify-center w-full h-full space-y-0.5 transition-colors {{ request()->routeIs('payments') ? 'text-[#00d4ff]' : 'text-slate-500 hover:text-slate-300' }}">
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

    <!-- Check In Modal + QR Fullscreen: rendered in body so fixed positioning works on Android -->
    <div id="checkInModal" class="hidden fixed inset-0 z-[10002] modal-overlay-fixed flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
         style="position:fixed !important;top:0 !important;left:0 !important;right:0 !important;bottom:0 !important;width:100% !important;height:100% !important;min-height:100vh;min-height:100dvh;"
         onclick="if(event.target===this) typeof closeCheckInModal === 'function' && closeCheckInModal()">
        <div class="glass rounded-2xl p-6 max-w-sm w-full relative" onclick="event.stopPropagation()">
            <button type="button" onclick="typeof closeCheckInModal === 'function' && closeCheckInModal()"
                    class="absolute top-4 right-4 text-slate-400 hover:text-white">
                <span class="material-symbols-outlined">close</span>
            </button>
            <h3 class="text-xl font-bold text-white mb-4" style="font-family: 'Bebas Neue', sans-serif;">{{ __('app.dashboard.check_in') }}</h3>

            <button type="button" id="checkInTodayBtn"
                    class="w-full py-3 rounded-xl bg-blue-500 hover:bg-blue-600 text-white font-semibold flex items-center justify-center gap-2 mb-4 transition-colors">
                <span class="material-symbols-outlined">event_available</span>
                {{ __('app.dashboard.check_in_for_today') }}
            </button>
            <p class="text-slate-500 text-xs mb-6">{{ __('app.dashboard.check_in_today_desc') }}</p>

            <p class="text-slate-400 text-sm font-semibold mb-2">{{ __('app.dashboard.scan_at_kiosk') }}</p>
            <button type="button" id="qrFullscreenBtn" class="block w-full" onclick="typeof openQrFullscreen === 'function' && openQrFullscreen()">
                <div class="p-4 rounded-xl bg-white flex justify-center">
                    <img id="checkinQrImg" src="" alt="Check-in QR code" class="w-40 h-40" style="min-width:10rem;min-height:10rem;">
                </div>
                <p class="text-slate-500 text-xs mt-2">{{ __('app.dashboard.tap_qr_fullscreen') }}</p>
            </button>
        </div>
    </div>

    <div id="qrFullscreen" class="hidden fixed inset-0 z-[10001] modal-overlay-fixed bg-black flex flex-col items-center justify-center p-4"
         style="position:fixed !important;top:0 !important;left:0 !important;right:0 !important;bottom:0 !important;width:100% !important;height:100% !important;min-height:100vh;min-height:100dvh;"
         onclick="typeof closeQrFullscreen === 'function' && closeQrFullscreen()">
        <p class="text-white text-sm mb-4">{{ __('app.dashboard.tap_qr_fullscreen') }}</p>
        <div class="p-6 rounded-2xl bg-white">
            <img id="checkinQrImgFullscreen" src="" alt="Check-in QR code" class="w-64 h-64 sm:w-80 sm:h-80" style="min-width:16rem;min-height:16rem;">
        </div>
        <p id="checkinQrLabelFullscreen" class="text-slate-500 text-xs mt-4"></p>
    </div>

    <script>
    (function() {
        var checkinTodayUrl = '{{ route("checkin.today") }}';
        var csrfToken = document.querySelector('meta[name="csrf-token"]') && document.querySelector('meta[name="csrf-token"]').content;
        var checkinBodyScrollY = 0;
        function lockBodyForModal() {
            checkinBodyScrollY = window.scrollY || window.pageYOffset || 0;
            document.body.style.overflow = 'hidden';
            document.body.style.position = 'fixed';
            document.body.style.top = '-' + checkinBodyScrollY + 'px';
            document.body.style.left = '0';
            document.body.style.right = '0';
            window.scrollTo(0, 0);
        }
        function unlockBodyForModal() {
            document.body.style.overflow = '';
            document.body.style.position = '';
            document.body.style.top = '';
            document.body.style.left = '';
            document.body.style.right = '';
            if (typeof checkinBodyScrollY === 'number') window.scrollTo(0, checkinBodyScrollY);
        }
        window.closeCheckInModal = function() {
            unlockBodyForModal();
            document.getElementById('checkInModal')?.classList.add('hidden');
        };
        window.closeQrFullscreen = function() {
            unlockBodyForModal();
            document.getElementById('qrFullscreen')?.classList.add('hidden');
        };
        function moveToBodyEnd(el) {
            if (el && el.parentNode && el.parentNode !== document.body) document.body.appendChild(el);
        }
        window.openCheckInModal = function() {
            var uid = window.CHECKIN_USER_ID;
            var m = document.getElementById('checkInModal');
            var q = document.getElementById('qrFullscreen');
            if (q) q.classList.add('hidden');
            if (m) {
                moveToBodyEnd(m);
                lockBodyForModal();
                var img = document.getElementById('checkinQrImg');
                if (img && uid) img.src = 'https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=CATCH-' + uid;
                m.classList.remove('hidden');
            }
        };
        window.openQrFullscreen = function() {
            var uid = window.CHECKIN_USER_ID;
            var m = document.getElementById('checkInModal');
            var q = document.getElementById('qrFullscreen');
            if (m) m.classList.add('hidden');
            if (q) {
                moveToBodyEnd(q);
                lockBodyForModal();
                var img = document.getElementById('checkinQrImgFullscreen');
                var label = document.getElementById('checkinQrLabelFullscreen');
                if (img && uid) img.src = 'https://api.qrserver.com/v1/create-qr-code/?size=320x320&data=CATCH-' + uid;
                if (label) label.textContent = uid ? 'CATCH-' + uid : '';
                q.classList.remove('hidden');
            }
        };
        document.body.addEventListener('click', function(e) {
            var open = e.target && e.target.closest && e.target.closest('[data-open-modal]');
            if (!open) return;
            e.preventDefault();
            e.stopPropagation();
            var which = open.getAttribute('data-open-modal');
            if (which === 'checkin' && typeof window.openCheckInModal === 'function') window.openCheckInModal();
            if (which === 'private' && typeof window.openModalAndLoadCoaches === 'function') window.openModalAndLoadCoaches();
        }, true);
        var btn = document.getElementById('checkInTodayBtn');
        if (btn && checkinTodayUrl && csrfToken) {
            var originalHtml = btn.innerHTML;
            btn.addEventListener('click', function() {
                btn.disabled = true;
                btn.innerHTML = '<span class="material-symbols-outlined animate-spin">progress_activity</span>';
                var d = new Date();
                var localDate = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
                fetch(checkinTodayUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({ date: localDate })
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        if (typeof closeCheckInModal === 'function') closeCheckInModal();
                        window.location.reload();
                    } else {
                        alert(data.message || 'Something went wrong.');
                    }
                })
                .catch(function() { alert('Check-in failed. Try again.'); })
                .finally(function() {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                });
            });
        }
    })();
    </script>

    <!-- Private Class Modal: 1) Choose coach → 2) Next 2 weeks calendar → 3) Book (same full-viewport style as Schedule) -->
    <div id="privateClassModal" class="hidden fixed inset-0 z-[10000] modal-overlay-fixed flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
         style="position:fixed !important;top:0 !important;left:0 !important;right:0 !important;bottom:0 !important;width:100% !important;height:100% !important;min-height:100vh;min-height:100dvh;"
         onclick="if(event.target===this) typeof closePrivateClassModal === 'function' && closePrivateClassModal()">
        <div class="glass rounded-2xl p-6 max-w-sm w-full max-h-[90vh] overflow-hidden flex flex-col relative" onclick="event.stopPropagation()">
            <button type="button" onclick="typeof closePrivateClassModal === 'function' && closePrivateClassModal()" class="absolute top-4 right-4 text-slate-400 hover:text-white z-10">
                <span class="material-symbols-outlined">close</span>
            </button>
            <h3 class="text-xl font-bold text-white mb-4 pr-8" style="font-family: 'Bebas Neue', sans-serif;">{{ app()->getLocale() === 'zh-TW' ? '預約私教課' : 'Book private class' }}</h3>

            <!-- Step 1: Choose a coach -->
            <div id="privateClassStep1" class="space-y-3 overflow-y-auto flex-1 min-h-0">
                <p class="text-slate-500 text-sm">{{ app()->getLocale() === 'zh-TW' ? '選擇教練' : 'Choose a coach' }}</p>
                <div id="privateClassCoaches" class="space-y-2"></div>
                <p id="privateClassCoachesLoading" class="text-slate-500 text-sm py-4 text-center">{{ app()->getLocale() === 'zh-TW' ? '載入中…' : 'Loading…' }}</p>
                <p id="privateClassCoachesEmpty" class="hidden text-slate-500 text-sm py-4 text-center">{{ app()->getLocale() === 'zh-TW' ? '目前沒有可預約的教練' : 'No coaches available for private classes right now.' }}</p>
            </div>

            <!-- Step 2: Next 2 weeks availability calendar -->
            <div id="privateClassStep2" class="hidden flex flex-col flex-1 min-h-0 overflow-hidden">
                <button type="button" onclick="typeof privateClassBackToCoaches === 'function' && privateClassBackToCoaches()" class="text-slate-400 hover:text-white text-sm mb-2 flex items-center gap-1 flex-shrink-0">
                    <span class="material-symbols-outlined text-lg">arrow_back</span> {{ app()->getLocale() === 'zh-TW' ? '返回選擇教練' : 'Back to coaches' }}
                </button>
                <p id="privateClassCoachName" class="text-white font-semibold mb-1 flex-shrink-0"></p>
                <p class="text-slate-500 text-xs mb-3 flex-shrink-0">{{ app()->getLocale() === 'zh-TW' ? '選擇日期與時段（未來兩週）' : 'Choose a date and time (next 2 weeks)' }}</p>
                <div id="privateClassCalendar" class="space-y-4 overflow-y-auto flex-1 min-h-0 pb-2"></div>
                <p id="privateClassCalendarLoading" class="text-slate-500 text-sm py-4 text-center">{{ app()->getLocale() === 'zh-TW' ? '載入可預約時段…' : 'Loading availability…' }}</p>
                <p id="privateClassCalendarEmpty" class="hidden text-slate-500 text-sm py-4 text-center">{{ app()->getLocale() === 'zh-TW' ? '未來兩週暫無可預約時段' : 'No slots in the next 2 weeks.' }}</p>
            </div>

            <form id="privateClassRequestForm" action="{{ route('private-class.request') }}" method="POST" class="hidden">
                @csrf
                <input type="hidden" name="coach_id" id="privateClassCoachId">
                <input type="hidden" name="scheduled_at" id="privateClassScheduledAt">
                <input type="hidden" name="duration_minutes" value="60">
            </form>
        </div>
    </div>

    <script>
    (function() {
        var modal = document.getElementById('privateClassModal');
        if (!modal) return;
        var coachesEl = document.getElementById('privateClassCoaches');
        var coachesLoadingEl = document.getElementById('privateClassCoachesLoading');
        var coachesEmptyEl = document.getElementById('privateClassCoachesEmpty');
        var step1 = document.getElementById('privateClassStep1');
        var step2 = document.getElementById('privateClassStep2');
        var coachNameEl = document.getElementById('privateClassCoachName');
        var calendarEl = document.getElementById('privateClassCalendar');
        var calendarLoadingEl = document.getElementById('privateClassCalendarLoading');
        var calendarEmptyEl = document.getElementById('privateClassCalendarEmpty');
        var form = document.getElementById('privateClassRequestForm');
        var coachIdInput = document.getElementById('privateClassCoachId');
        var scheduledAtInput = document.getElementById('privateClassScheduledAt');

        var selectedCoach = null;
        var availabilityUrl = '{{ url("/private-class/coach") }}';

        var bodyScrollY = 0;
        window.closePrivateClassModal = function() {
            modal.classList.add('hidden');
            step2.classList.add('hidden');
            step1.classList.remove('hidden');
            selectedCoach = null;
            document.body.style.overflow = '';
            document.body.style.position = '';
            document.body.style.top = '';
            if (typeof bodyScrollY === 'number') window.scrollTo(0, bodyScrollY);
        };

        window.privateClassBackToCoaches = function() {
            step2.classList.add('hidden');
            step1.classList.remove('hidden');
            selectedCoach = null;
        };

        function openModalAndLoadCoaches() {
            bodyScrollY = window.scrollY || window.pageYOffset || 0;
            document.body.style.overflow = 'hidden';
            document.body.style.position = 'fixed';
            document.body.style.top = '-' + bodyScrollY + 'px';
            document.body.style.left = '0';
            document.body.style.right = '0';
            window.scrollTo(0, 0);
            document.getElementById('checkInModal')?.classList.add('hidden');
            document.getElementById('qrFullscreen')?.classList.add('hidden');
            if (modal.parentNode !== document.body) document.body.appendChild(modal);
            modal.classList.remove('hidden');
            step2.classList.add('hidden');
            step1.classList.remove('hidden');
            coachesLoadingEl.classList.remove('hidden');
            coachesEmptyEl.classList.add('hidden');
            coachesEl.innerHTML = '';
            fetch('{{ route("private-class.coaches") }}', { headers: { 'Accept': 'application/json' } })
                .then(function(r) { return r.json(); })
                .then(function(coaches) {
                    coachesLoadingEl.classList.add('hidden');
                    if (!coaches || coaches.length === 0) {
                        coachesEmptyEl.classList.remove('hidden');
                        return;
                    }
                    coaches.forEach(function(c) {
                        var btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'w-full flex items-center gap-3 p-4 rounded-xl bg-slate-800/50 border border-slate-700/50 hover:bg-violet-500/20 hover:border-violet-500/50 text-white text-left transition-colors';
                        var avatarHtml = c.avatar ? '<img src="' + c.avatar + '" alt="" class="w-12 h-12 rounded-full object-cover border-2 border-slate-600 flex-shrink-0">' : '<div class="w-12 h-12 rounded-full bg-slate-700 border-2 border-slate-600 flex items-center justify-center text-slate-400 font-bold flex-shrink-0">' + (c.name ? c.name.substring(0, 2).toUpperCase() : '') + '</div>';
                        btn.innerHTML = '<div class="flex-shrink-0">' + avatarHtml + '</div><div class="flex-1 min-w-0"><span class="font-semibold text-white block">' + (c.name || '') + '</span>' + (c.price ? '<span class="text-slate-500 text-sm">NT$' + c.price.toLocaleString() + '</span>' : '') + '</div><span class="material-symbols-outlined text-slate-500 flex-shrink-0">chevron_right</span>';
                        btn.onclick = function() { selectCoach(c); };
                        coachesEl.appendChild(btn);
                    });
                })
                .catch(function() {
                    coachesLoadingEl.classList.add('hidden');
                    coachesEmptyEl.textContent = '{{ app()->getLocale() === "zh-TW" ? "載入失敗" : "Failed to load." }}';
                    coachesEmptyEl.classList.remove('hidden');
                });
        }

        window.openModalAndLoadCoaches = openModalAndLoadCoaches;
        document.getElementById('openPrivateClassModal')?.addEventListener('click', openModalAndLoadCoaches);
        document.getElementById('openPrivateClassModalSchedule')?.addEventListener('click', openModalAndLoadCoaches);

        function selectCoach(coach) {
            selectedCoach = coach;
            coachIdInput.value = coach.id;
            step1.classList.add('hidden');
            step2.classList.remove('hidden');
            coachNameEl.textContent = coach.name || '';
            calendarEl.innerHTML = '';
            calendarLoadingEl.classList.remove('hidden');
            calendarEmptyEl.classList.add('hidden');
            fetch(availabilityUrl + '/' + coach.id + '/availability?weeks=2', { headers: { 'Accept': 'application/json' } })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    calendarLoadingEl.classList.add('hidden');
                    var slots = data.slots || {};
                    var slotList = Object.keys(slots).map(function(k) { var s = slots[k]; s._key = k; return s; });
                    if (slotList.length === 0) {
                        calendarEmptyEl.classList.remove('hidden');
                        return;
                    }
                    var byDate = {};
                    slotList.forEach(function(s) {
                        var key = s._key ? s._key.slice(0, 10) : (s.datetime ? s.datetime.slice(0, 10) : '');
                        if (!key) return;
                        if (!byDate[key]) byDate[key] = [];
                        byDate[key].push(s);
                    });
                    var sortedDates = Object.keys(byDate).sort();
                    sortedDates.forEach(function(dateStr) {
                        var daySlots = byDate[dateStr];
                        var first = daySlots[0];
                        var dayLabel = first && first.label ? first.label.split(' at ')[0].trim() : dateStr;
                        var dayCard = document.createElement('div');
                        dayCard.className = 'rounded-xl bg-slate-800/50 border border-slate-700/50 overflow-hidden';
                        var dayHeader = document.createElement('div');
                        dayHeader.className = 'px-3 py-2 bg-slate-800 border-b border-slate-700/50 text-white font-semibold text-sm';
                        dayHeader.textContent = dayLabel;
                        dayCard.appendChild(dayHeader);
                        var slotsWrap = document.createElement('div');
                        slotsWrap.className = 'p-2 flex flex-wrap gap-2';
                        daySlots.forEach(function(s) {
                            var slotBtn = document.createElement('button');
                            slotBtn.type = 'button';
                            slotBtn.className = 'px-3 py-2 rounded-lg bg-slate-700/80 border border-slate-600 hover:bg-violet-500/30 hover:border-violet-500/50 text-white text-sm transition-colors';
                            slotBtn.textContent = (s.label && s.label.indexOf(' at ') !== -1) ? s.label.split(' at ')[1] : (s.datetime ? new Date(s.datetime).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '');
                            slotBtn.onclick = function() {
                                var iso = s.datetime ? s.datetime.replace('Z', '').replace(/\.[0-9]+/, '').slice(0, 19) : (dateStr + 'T09:00:00');
                                scheduledAtInput.value = iso;
                                form.submit();
                            };
                            slotsWrap.appendChild(slotBtn);
                        });
                        dayCard.appendChild(slotsWrap);
                        calendarEl.appendChild(dayCard);
                    });
                })
                .catch(function() {
                    calendarLoadingEl.classList.add('hidden');
                    calendarEmptyEl.textContent = '{{ app()->getLocale() === "zh-TW" ? "載入失敗" : "Failed to load." }}';
                    calendarEmptyEl.classList.remove('hidden');
                });
        }
    })();
    </script>
    @endauth
</body>
</html>
