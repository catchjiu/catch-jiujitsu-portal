<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=1920, height=1080, initial-scale=1">
    <title>Check-In – Catch Jiu Jitsu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL@24,400,0" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'], display: ['Bebas Neue', 'sans-serif'] },
                    colors: { slate: { 850: '#151f32', 900: '#0f172a', 950: '#020617' }, brand: { gold: '#f59e0b', blue: '#3b82f6' } }
                }
            }
        };
    </script>
    <style>
        body { background-color: #020617; color: #f8fafc; margin: 0; overflow: hidden; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
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
        <div id="scanCard" role="button" tabindex="0" class="relative overflow-hidden rounded-3xl border-2 border-dashed border-slate-600 bg-slate-800/40 backdrop-blur-md p-16 flex flex-col items-center justify-center cursor-pointer outline-none focus:border-amber-500 min-w-[720px] min-h-[360px]">
            <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
            <span class="material-symbols-outlined text-8xl text-slate-500 mb-6">qr_code_scanner</span>
            <h2 class="text-3xl font-display font-bold text-white uppercase tracking-wide mb-2">Scan to check in</h2>
            <p class="text-slate-400 text-xl">Position your QR code in front of the scanner</p>
            <p class="text-slate-500 text-sm mt-4">Or type your code and press Enter</p>
            <p id="loadingMsg" class="text-amber-500 text-lg font-medium mt-6 hidden animate-pulse">Looking up...</p>
            <p id="errorMsg" class="text-amber-400 text-lg font-medium mt-6 hidden"></p>
        </div>
        <p class="text-slate-500 text-sm mt-10">Ready for the next member</p>
    </div>

    <!-- Welcome screen (hidden by default) -->
    <div id="welcomeScreen" class="hidden flex flex-col items-center justify-center">
        <div id="welcomeStatus" class="mb-8"></div>
        <div id="welcomeAvatar" class="w-48 h-48 rounded-full overflow-hidden border-4 border-slate-600 bg-slate-800 shadow-2xl mb-8"></div>
        <h2 class="text-4xl font-display font-bold text-white uppercase tracking-wide mb-2">Welcome back</h2>
        <p id="welcomeName" class="text-3xl font-display font-bold text-amber-500 mb-10"></p>
        <div id="welcomeBelt" class="h-14 w-full max-w-md rounded shadow-inner relative flex items-center justify-end pr-4 mb-10"></div>
        <p id="welcomeRank" class="text-slate-400 text-xl mb-12"></p>
        <div class="grid grid-cols-3 gap-8 w-full max-w-2xl">
            <div class="rounded-2xl bg-slate-800/60 border border-white/10 p-6 text-center">
                <p id="welcomeHours" class="text-4xl font-display font-bold text-amber-500">0</p>
                <p class="text-slate-400 text-sm uppercase tracking-wider mt-1">Hours this year</p>
            </div>
            <div class="rounded-2xl bg-slate-800/60 border border-white/10 p-6 text-center">
                <p id="welcomeClasses" class="text-4xl font-display font-bold text-blue-500">0</p>
                <p class="text-slate-400 text-sm uppercase tracking-wider mt-1">Classes this month</p>
            </div>
            <div class="rounded-2xl bg-slate-800/60 border border-white/10 p-6 text-center">
                <p id="welcomeExpiry" class="text-2xl font-display font-bold text-white">—</p>
                <p class="text-slate-400 text-sm uppercase tracking-wider mt-1">Expires</p>
            </div>
        </div>
    </div>

    <script>
(function() {
    const API_URL = '{{ url("/api/checkin") }}';
    const DISPLAY_MS = 4000;
    let buffer = '';

    function beltColor(rank) {
        const map = { White: 'bg-gray-100', Blue: 'bg-blue-600', Purple: 'bg-purple-600', Brown: 'bg-yellow-900', Black: 'bg-slate-900 border-l-8 border-red-600' };
        return map[rank] || 'bg-gray-100';
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
        other.classList.add('hidden');
        other.textContent = '';
        el.textContent = msg;
        el.classList.remove('hidden');
        if (isError) setTimeout(function() { el.classList.add('hidden'); el.textContent = ''; }, 4000);
    }

    function hideMessages() {
        document.getElementById('loadingMsg').classList.add('hidden');
        document.getElementById('errorMsg').classList.add('hidden').textContent = '';
    }

    function showWelcome(data) {
        document.getElementById('scanScreen').classList.add('hidden');
        document.getElementById('welcomeScreen').classList.remove('hidden');

        var status = document.getElementById('welcomeStatus');
        status.innerHTML = data.isActive
            ? '<span class="px-8 py-3 rounded-full text-xl font-display font-bold uppercase tracking-wider bg-emerald-500/20 text-emerald-400 border-2 border-emerald-500/50">Active member</span>'
            : '<span class="px-8 py-3 rounded-full text-xl font-display font-bold uppercase tracking-wider bg-amber-500/20 text-amber-400 border-2 border-amber-500/50">Membership expired</span>';

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
        document.getElementById('welcomeRank').textContent = (data.rank || 'White') + ' Belt' + (data.stripes > 0 ? ' · ' + data.stripes + ' stripe(s)' : '');

        var beltEl = document.getElementById('welcomeBelt');
        beltEl.className = 'h-14 w-full max-w-md rounded shadow-inner relative flex items-center justify-end pr-4 mb-10 ' + beltColor(data.rank || 'White');
        beltEl.innerHTML = '<div class="h-full w-20 bg-black flex items-center justify-around px-1 absolute right-4">' +
            [0,1,2,3].map(function(i) {
                return '<div class="w-2 h-full rounded-sm ' + (i < (data.stripes || 0) ? 'bg-white' : 'bg-slate-700/50') + '"></div>';
            }).join('') + '</div>';

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
                showMessage(err.message === 'not_found' ? 'Member not found' : 'Check-in service unavailable. Try again.', true);
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
