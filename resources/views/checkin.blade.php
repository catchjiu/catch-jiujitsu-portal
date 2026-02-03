<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark" data-locale="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <script>
        (function() {
            var params = new URLSearchParams(window.location.search);
            if (params.has('locale')) return;
            var preferred = (navigator.languages && navigator.languages[0]) || navigator.language || navigator.userLanguage || 'en';
            var wantZh = /^zh/.test(preferred);
            var current = document.documentElement.getAttribute('data-locale') || 'en';
            if (wantZh && current !== 'zh-TW') {
                window.location.replace(window.location.pathname + '?locale=zh-TW');
            } else if (!wantZh && current === 'zh-TW') {
                window.location.replace(window.location.pathname + '?locale=en');
            }
        })();
    </script>
    <meta name="viewport" content="width=1920, height=1080, initial-scale=1">
    <title>{{ __('app.checkin.title') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700&family=Noto+Sans+TC:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL@24,400,0" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'Noto Sans TC', 'sans-serif'], display: ['Bebas Neue', 'sans-serif'] },
                    colors: { slate: { 850: '#151f32', 900: '#0f172a', 950: '#020617' }, brand: { gold: '#f59e0b', blue: '#3b82f6' } }
                }
            }
        };
    </script>
    <style>
        body { background-color: #020617; color: #f8fafc; margin: 0; overflow: hidden; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        @keyframes scan-pulse {
            0%, 100% { opacity: 0.6; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.05); }
        }
        @keyframes scan-line {
            0% { top: 0; opacity: 0.3; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { top: 100%; opacity: 0.3; }
        }
        @keyframes border-glow {
            0%, 100% { border-color: rgb(71 85 105); box-shadow: 0 0 20px rgba(251 191 36 / 0.1); }
            50% { border-color: rgb(251 191 36 / 0.6); box-shadow: 0 0 30px rgba(251 191 36 / 0.2); }
        }
        @keyframes float-dots {
            0%, 100% { transform: translateY(0) scale(1); opacity: 0.3; }
            50% { transform: translateY(-8px) scale(1.1); opacity: 0.6; }
        }
        .animate-scan-pulse { animation: scan-pulse 2s ease-in-out infinite; }
        .animate-border-glow { animation: border-glow 3s ease-in-out infinite; }
        .scan-line { position: absolute; left: 0; right: 0; height: 2px; background: linear-gradient(transparent, rgba(251 191 36 / 0.8), transparent); animation: scan-line 2.5s ease-in-out infinite; }
        .float-dot { animation: float-dots 3s ease-in-out infinite; }
        .float-dot:nth-child(2) { animation-delay: 0.5s; }
        .float-dot:nth-child(3) { animation-delay: 1s; }
        .float-dot:nth-child(4) { animation-delay: 1.5s; }
    </style>
</head>
<body class="font-sans antialiased flex flex-col items-center justify-center overflow-hidden" style="width:1920px;height:1080px;box-sizing:border-box;">
    <input type="text" id="scanInput" autocomplete="off" aria-label="QR code scan" class="absolute opacity-0 w-0 h-0 pointer-events-none" tabindex="0">

    <!-- Scan screen -->
    <div id="scanScreen" class="flex flex-col items-center justify-center">
        <div class="flex items-center gap-3 mb-16">
            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center font-display font-bold text-black text-2xl">C</div>
            <h1 class="font-display font-bold text-4xl tracking-wider text-white">CATCH <span class="text-amber-500">BJJ</span></h1>
        </div>
        <div id="scanCard" role="button" tabindex="0" class="relative overflow-hidden rounded-3xl border-2 border-dashed bg-slate-800/40 backdrop-blur-md p-16 flex flex-col items-center justify-center cursor-pointer outline-none focus:border-amber-500 min-w-[720px] min-h-[360px] animate-border-glow border-slate-600">
            <div class="scan-line"></div>
            <div class="absolute top-4 left-1/2 -translate-x-1/2 flex gap-2">
                <span class="w-2 h-2 rounded-full bg-amber-500/60 float-dot"></span>
                <span class="w-2 h-2 rounded-full bg-amber-500/60 float-dot"></span>
                <span class="w-2 h-2 rounded-full bg-amber-500/60 float-dot"></span>
                <span class="w-2 h-2 rounded-full bg-amber-500/60 float-dot"></span>
            </div>
            <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
            <span class="material-symbols-outlined text-8xl text-slate-500 mb-6 animate-scan-pulse">qr_code_scanner</span>
            <h2 class="text-3xl font-display font-bold text-white uppercase tracking-wide mb-2">{{ __('app.checkin.scan_to_check_in') }}</h2>
            <p class="text-slate-400 text-xl">{{ __('app.checkin.position_qr') }}</p>
            <p class="text-slate-500 text-sm mt-4">{{ __('app.checkin.or_type_code') }}</p>
            <p id="loadingMsg" class="text-amber-500 text-lg font-medium mt-6 hidden animate-pulse">{{ __('app.checkin.looking_up') }}</p>
            <p id="errorMsg" class="text-amber-400 text-lg font-medium mt-6 hidden"></p>
        </div>
        <p class="text-slate-500 text-sm mt-10">{{ __('app.checkin.ready_for_next') }}</p>
    </div>

    <!-- Welcome screen (hidden by default) -->
    <div id="welcomeScreen" class="hidden flex flex-col items-center justify-center">
        <div id="welcomeStatus" class="mb-8"></div>
        <div id="welcomeAvatar" class="w-48 h-48 rounded-full overflow-hidden border-4 border-slate-600 bg-slate-800 shadow-2xl mb-8"></div>
        <h2 class="text-4xl font-display font-bold text-white uppercase tracking-wide mb-2">{{ __('app.checkin.welcome_back') }}</h2>
        <p id="welcomeName" class="text-3xl font-display font-bold text-amber-500 mb-10"></p>
        <div id="welcomeBelt" class="h-14 w-full max-w-md rounded shadow-inner relative flex items-center justify-end pr-4 mb-10"></div>
        <p id="welcomeRank" class="text-slate-400 text-xl mb-12"></p>
        <div class="grid grid-cols-3 gap-8 w-full max-w-2xl">
            <div class="rounded-2xl bg-slate-800/60 border border-white/10 p-6 text-center">
                <p id="welcomeHours" class="text-4xl font-display font-bold text-amber-500">0</p>
                <p class="text-slate-400 text-sm uppercase tracking-wider mt-1">{{ __('app.checkin.hours_this_year') }}</p>
            </div>
            <div class="rounded-2xl bg-slate-800/60 border border-white/10 p-6 text-center">
                <p id="welcomeClasses" class="text-4xl font-display font-bold text-blue-500">0</p>
                <p class="text-slate-400 text-sm uppercase tracking-wider mt-1">{{ __('app.checkin.classes_this_month') }}</p>
            </div>
            <div class="rounded-2xl bg-slate-800/60 border border-white/10 p-6 text-center">
                <p id="welcomeExpiry" class="text-2xl font-display font-bold text-white">—</p>
                <p class="text-slate-400 text-sm uppercase tracking-wider mt-1">{{ __('app.checkin.expires') }}</p>
            </div>
        </div>
    </div>

    <script>
(function() {
    const API_URL = '{{ url("/api/checkin") }}';
    const DISPLAY_MS = 4000;
    let buffer = '';

    const i18n = {
        memberNotFound: @json(__('app.checkin.member_not_found')),
        serviceUnavailable: @json(__('app.checkin.service_unavailable')),
        activeMember: @json(__('app.checkin.active_member')),
        membershipExpired: @json(__('app.checkin.membership_expired')),
        belt: @json(__('app.checkin.belt')),
        stripes: @json(__('app.checkin.stripes')),
        belts: {
            white: @json(__('app.belts.white')),
            grey: @json(__('app.belts.grey')),
            yellow: @json(__('app.belts.yellow')),
            orange: @json(__('app.belts.orange')),
            green: @json(__('app.belts.green')),
            blue: @json(__('app.belts.blue')),
            purple: @json(__('app.belts.purple')),
            brown: @json(__('app.belts.brown')),
            black: @json(__('app.belts.black'))
        }
    };

    function getBeltStyles(rank, beltVariation) {
        const baseColors = { White: 'bg-gray-100', Grey: 'bg-slate-300', Yellow: 'bg-yellow-400', Orange: 'bg-orange-500', Green: 'bg-green-500', Blue: 'bg-blue-600', Purple: 'bg-purple-600', Brown: 'bg-yellow-900', Black: 'bg-black' };
        const kidsBelts = ['Grey', 'Yellow', 'Orange', 'Green'];
        const baseColor = baseColors[rank] || 'bg-gray-100';
        var bandHtml = '';
        if (kidsBelts.indexOf(rank) >= 0 && beltVariation === 'white') {
            bandHtml = '<div class="absolute inset-0 flex items-center pointer-events-none"><div class="w-full h-3 bg-white"></div></div>';
        } else if (kidsBelts.indexOf(rank) >= 0 && beltVariation === 'black') {
            bandHtml = '<div class="absolute inset-0 flex items-center pointer-events-none"><div class="w-full h-3 bg-black"></div></div>';
        }
        return { baseColor: baseColor, bandHtml: bandHtml, isKidsBelt: kidsBelts.indexOf(rank) >= 0 };
    }

    function playSound(active) {
        try {
            const Ctx = window.AudioContext || window.webkitAudioContext;
            if (!Ctx) return;
            const ctx = new Ctx();
            function beep(freq, dur, type) {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain); gain.connect(ctx.destination);
                osc.frequency.value = freq; osc.type = type || 'sine';
                gain.gain.setValueAtTime(0.15, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + dur);
                osc.start(ctx.currentTime); osc.stop(ctx.currentTime + dur);
            }
            if (active) {
                beep(523.25, 0.15);
                setTimeout(function() { beep(659.25, 0.2); }, 120);
                setTimeout(function() { beep(783.99, 0.25); }, 280);
            } else {
                beep(200, 0.4, 'square');
                setTimeout(function() { beep(180, 0.5, 'square'); }, 350);
            }
        } catch (e) {}
    }

    function showMessage(msg, isError) {
        var el = document.getElementById(isError ? 'errorMsg' : 'loadingMsg');
        var other = document.getElementById(isError ? 'loadingMsg' : 'errorMsg');
        if (other) { other.classList.add('hidden'); other.textContent = ''; }
        if (el) { el.textContent = msg; el.classList.remove('hidden'); }
        if (isError && el) setTimeout(function() { el.classList.add('hidden'); el.textContent = ''; }, 4000);
    }

    function hideMessages() {
        var loadingEl = document.getElementById('loadingMsg');
        var errorEl = document.getElementById('errorMsg');
        if (loadingEl) loadingEl.classList.add('hidden');
        if (errorEl) {
            errorEl.classList.add('hidden');
            errorEl.textContent = '';
        }
    }

    function showWelcome(data) {
        document.getElementById('scanScreen').classList.add('hidden');
        document.getElementById('welcomeScreen').classList.remove('hidden');

        var status = document.getElementById('welcomeStatus');
        status.innerHTML = data.isActive
            ? '<span class="px-8 py-3 rounded-full text-xl font-display font-bold uppercase tracking-wider bg-emerald-500/20 text-emerald-400 border-2 border-emerald-500/50">' + i18n.activeMember + '</span>'
            : '<span class="px-8 py-3 rounded-full text-xl font-display font-bold uppercase tracking-wider bg-amber-500/20 text-amber-400 border-2 border-amber-500/50">' + i18n.membershipExpired + '</span>';

        var avatar = document.getElementById('welcomeAvatar');
        if (data.avatarUrl) {
            avatar.innerHTML = '<img src="' + escapeHtml(data.avatarUrl) + '" alt="" class="w-full h-full object-cover">';
        } else {
            var initials = (data.name || '').split(/\s+/).map(function(s) { return (s && s[0]) || ''; }).join('').slice(0, 2).toUpperCase();
            avatar.innerHTML = '<div class="w-full h-full flex items-center justify-center text-6xl font-display font-bold text-slate-500">' + escapeHtml(initials || '?') + '</div>';
        }

        document.getElementById('welcomeName').textContent = data.name || '';
        document.getElementById('welcomeHours').textContent = data.hoursThisYear ?? 0;
        document.getElementById('welcomeClasses').textContent = data.classesThisMonth ?? 0;
        document.getElementById('welcomeExpiry').textContent = data.membershipExpiresAt ? new Date(data.membershipExpiresAt).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' }) : '—';

        var rankKey = (data.rank || 'White').toLowerCase();
        var rankLabel = i18n.belts[rankKey] || (data.rank || 'White');
        var rankText = rankLabel + ' ' + i18n.belt + (data.stripes > 0 ? ' · ' + data.stripes + ' ' + i18n.stripes : '');
        document.getElementById('welcomeRank').textContent = rankText;

        var beltEl = document.getElementById('welcomeBelt');
        var rank = data.rank || 'White';
        var beltVariation = data.beltVariation || null;
        var styles = getBeltStyles(rank, beltVariation);
        beltEl.className = 'h-14 w-full max-w-md rounded shadow-inner relative flex items-center pl-4 mb-10 overflow-hidden ' + styles.baseColor;
        var stripeBarColor = rank === 'Black' ? 'bg-red-600' : 'bg-black';
        var stripeCount = data.stripes || 0;
        var stripeDivs = stripeCount > 0
            ? Array(stripeCount).fill(0).map(function() { return '<div class="w-1.5 h-full rounded-sm bg-white flex-shrink-0"></div>'; }).join('')
            : '';
        var stripeBar = '<div class="h-full w-20 ' + stripeBarColor + ' flex items-center justify-start gap-1 px-1 absolute left-4 z-10">' + stripeDivs + '</div>';
        beltEl.innerHTML = styles.bandHtml + stripeBar;

        playSound(data.isActive);
        setTimeout(showScan, DISPLAY_MS);
    }

    function escapeHtml(s) {
        var div = document.createElement('div');
        div.textContent = s;
        return div.innerHTML;
    }

    function showScan() {
        document.getElementById('welcomeScreen').classList.add('hidden');
        document.getElementById('scanScreen').classList.remove('hidden');
        buffer = '';
        var inp = document.getElementById('scanInput');
        if (inp) inp.value = '';
        inp && inp.focus();
    }

    function submitCode() {
        var inp = document.getElementById('scanInput');
        var code = (inp && inp.value && inp.value.trim()) || buffer.trim();
        buffer = '';
        if (inp) inp.value = '';
        if (!code) return;

        document.getElementById('loadingMsg').classList.remove('hidden');
        document.getElementById('errorMsg').classList.add('hidden');

        fetch(API_URL + '?code=' + encodeURIComponent(code))
            .then(function(res) {
                if (res.status === 404) throw new Error('not_found');
                if (!res.ok) throw new Error('server_error');
                return res.json();
            })
            .then(function(data) {
                hideMessages();
                showWelcome(data);
            })
            .catch(function(err) {
                hideMessages();
                showMessage(err.message === 'not_found' ? i18n.memberNotFound : i18n.serviceUnavailable, true);
            });
    }

    var scanCard = document.getElementById('scanCard');
    var scanInput = document.getElementById('scanInput');
    if (scanCard) scanCard.addEventListener('click', function() { scanInput && scanInput.focus(); });
    if (scanCard) scanCard.addEventListener('keydown', function(e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); scanInput && scanInput.focus(); } });

    window.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            submitCode();
            return;
        }
        if (e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) buffer += e.key;
    });

    scanInput && scanInput.addEventListener('paste', function(e) {
        e.preventDefault();
        var text = (e.clipboardData && e.clipboardData.getData('text')) || '';
        if (text.trim()) { buffer = text.trim(); submitCode(); }
    });

    scanInput && scanInput.focus();
})();
    </script>
</body>
</html>
