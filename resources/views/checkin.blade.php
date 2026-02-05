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
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Inter:wght@300;400;500;600&family=Noto+Sans+TC:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL@24,400,0" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'Noto Sans TC', 'sans-serif'], display: ['Cormorant Garamond', 'serif'] },
                    colors: { slate: { 850: '#1a2332', 900: '#0f1419', 950: '#080c10' }, cream: '#f5f0e8', gold: '#c9a962', goldDim: 'rgba(201, 169, 98, 0.15)' }
                }
            }
        };
    </script>
    <style>
        body { background: #080c10; color: #f5f0e8; margin: 0; overflow: hidden; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .font-display { font-family: 'Cormorant Garamond', serif; }
        @keyframes fade-in { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes breathe { 0%, 100% { opacity: 0.4; } 50% { opacity: 0.8; } }
        .animate-fade-in { animation: fade-in 0.6s ease-out forwards; }
        .animate-breathe { animation: breathe 3s ease-in-out infinite; }
        .scan-zone { background: linear-gradient(180deg, rgba(201, 169, 98, 0.04) 0%, transparent 50%); }
    </style>
</head>
<body class="font-sans antialiased flex flex-col items-center justify-center overflow-hidden min-h-screen" style="width:1920px;height:1080px;box-sizing:border-box;">
    <input type="text" id="scanInput" autocomplete="off" aria-label="QR code scan" class="absolute opacity-0 w-0 h-0 pointer-events-none" tabindex="0">

    <!-- Scan screen -->
    <div id="scanScreen" class="flex flex-col items-center justify-center w-full px-16">
        <div class="flex items-center gap-4 mb-20">
            <div class="w-12 h-12 rounded-lg bg-[#c9a962]/10 border border-[#c9a962]/30 flex items-center justify-center">
                <span class="font-display font-semibold text-2xl text-[#c9a962]">C</span>
            </div>
            <h1 class="font-display font-semibold text-3xl tracking-[0.2em] text-cream uppercase">Catch Jiu Jitsu</h1>
        </div>
        <div id="scanCard" role="button" tabindex="0" class="scan-zone relative overflow-hidden rounded-2xl border border-[#c9a962]/20 bg-white/[0.02] backdrop-blur-sm p-20 flex flex-col items-center justify-center cursor-pointer outline-none focus:border-[#c9a962]/50 min-w-[680px] min-h-[340px] transition-colors duration-300 hover:border-[#c9a962]/30">
            <span class="material-symbols-outlined text-7xl text-[#c9a962]/50 mb-8 animate-breathe">qr_code_scanner</span>
            <h2 class="font-display font-semibold text-3xl tracking-[0.15em] text-cream uppercase mb-2">{{ __('app.checkin.scan_to_check_in') }}</h2>
            <p class="text-slate-400 text-lg font-light tracking-wide">{{ __('app.checkin.position_qr') }}</p>
            <p class="text-slate-500 text-sm mt-6 font-light">{{ __('app.checkin.or_type_code') }}</p>
            <p id="loadingMsg" class="text-[#c9a962] text-base font-medium mt-8 hidden animate-pulse">{{ __('app.checkin.looking_up') }}</p>
            <p id="errorMsg" class="text-amber-300/90 text-base font-medium mt-8 hidden"></p>
        </div>
        <p class="text-slate-500 text-sm mt-12 font-light tracking-wide">{{ __('app.checkin.ready_for_next') }}</p>
    </div>

    <!-- Welcome screen (hidden by default) -->
    <div id="welcomeScreen" class="hidden flex flex-col items-center justify-center w-full px-16">
        <div id="welcomeStatus" class="mb-10"></div>
        <div id="welcomeAvatar" class="w-40 h-40 rounded-full overflow-hidden border-2 border-[#c9a962]/30 bg-slate-800/50 shadow-2xl mb-8 ring-2 ring-white/5"></div>
        <h2 class="font-display font-semibold text-4xl tracking-[0.12em] text-cream uppercase mb-1">{{ __('app.checkin.welcome_back') }}</h2>
        <p id="welcomeName" class="font-display font-semibold text-3xl text-[#c9a962] tracking-wide mb-8"></p>
        <div id="welcomeBelt" class="h-12 w-full max-w-sm rounded-md shadow-inner relative flex items-center justify-end pr-4 mb-6 overflow-hidden"></div>
        <p id="welcomeRank" class="text-slate-400 text-lg font-light tracking-wide mb-14"></p>
        <div class="grid grid-cols-3 gap-6 w-full max-w-2xl">
            <div class="rounded-xl border border-white/5 bg-white/[0.02] p-8 text-center backdrop-blur-sm">
                <p id="welcomeHours" class="font-display font-semibold text-4xl text-[#c9a962]">0</p>
                <p class="text-slate-500 text-xs uppercase tracking-[0.2em] mt-2 font-light">{{ __('app.checkin.hours_this_year') }}</p>
            </div>
            <div class="rounded-xl border border-white/5 bg-white/[0.02] p-8 text-center backdrop-blur-sm">
                <p id="welcomeClasses" class="font-display font-semibold text-4xl text-cream">0</p>
                <p class="text-slate-500 text-xs uppercase tracking-[0.2em] mt-2 font-light">{{ __('app.checkin.classes_this_month') }}</p>
            </div>
            <div class="rounded-xl border border-white/5 bg-white/[0.02] p-8 text-center backdrop-blur-sm">
                <p id="welcomeExpiry" class="font-display font-semibold text-2xl text-cream">—</p>
                <p class="text-slate-500 text-xs uppercase tracking-[0.2em] mt-2 font-light">{{ __('app.checkin.expires') }}</p>
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
            bandHtml = '<div class="absolute inset-0 flex items-center pointer-events-none"><div class="w-full h-2 bg-white"></div></div>';
        } else if (kidsBelts.indexOf(rank) >= 0 && beltVariation === 'black') {
            bandHtml = '<div class="absolute inset-0 flex items-center pointer-events-none"><div class="w-full h-2 bg-black"></div></div>';
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
            ? '<span class="px-6 py-2 rounded-full text-sm font-medium tracking-[0.15em] uppercase bg-emerald-500/10 text-emerald-400/90 border border-emerald-500/30">' + i18n.activeMember + '</span>'
            : '<span class="px-6 py-2 rounded-full text-sm font-medium tracking-[0.15em] uppercase bg-amber-500/10 text-amber-400/90 border border-amber-500/30">' + i18n.membershipExpired + '</span>';

        var avatar = document.getElementById('welcomeAvatar');
        if (data.avatarUrl) {
            avatar.innerHTML = '<img src="' + escapeHtml(data.avatarUrl) + '" alt="" class="w-full h-full object-cover">';
        } else {
            var initials = (data.name || '').split(/\s+/).map(function(s) { return (s && s[0]) || ''; }).join('').slice(0, 2).toUpperCase();
            avatar.innerHTML = '<div class="w-full h-full flex items-center justify-center font-display font-semibold text-5xl text-slate-500">' + escapeHtml(initials || '?') + '</div>';
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
        beltEl.className = 'h-12 w-full max-w-sm rounded-md shadow-inner relative flex items-center pl-4 mb-6 overflow-hidden ' + styles.baseColor;
        var stripeBarColor = rank === 'Black' ? 'bg-red-600' : 'bg-black';
        var stripeCount = data.stripes || 0;
        var stripeDivs = stripeCount > 0
            ? Array(stripeCount).fill(0).map(function() { return '<div class="w-1 h-full rounded-sm bg-white flex-shrink-0"></div>'; }).join('')
            : '';
        var stripeBar = '<div class="h-full w-16 ' + stripeBarColor + ' flex items-center justify-start gap-0.5 px-1 absolute left-4 z-10">' + stripeDivs + '</div>';
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
