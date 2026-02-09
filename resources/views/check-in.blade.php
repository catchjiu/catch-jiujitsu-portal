@extends('layouts.app')

@section('title', __('app.dashboard.check_in'))

@section('content')
<div class="space-y-6">
    <h2 class="text-2xl font-bold text-white uppercase tracking-wide" style="font-family: 'Bebas Neue', sans-serif;">
        {{ __('app.dashboard.check_in') }}
    </h2>
    <p class="text-slate-400 text-sm">{{ __('app.dashboard.check_in_subtitle') }}</p>

    <div class="glass rounded-2xl p-6 border-t-4 border-t-blue-500">
        <button type="button" id="checkInTodayBtn"
                class="w-full py-3 rounded-xl bg-blue-500 hover:bg-blue-600 text-white font-semibold flex items-center justify-center gap-2 mb-4 transition-colors">
            <span class="material-symbols-outlined">event_available</span>
            {{ __('app.dashboard.check_in_for_today') }}
        </button>
        <p class="text-slate-500 text-xs mb-6">{{ __('app.dashboard.check_in_today_desc') }}</p>

        <p class="text-slate-400 text-sm font-semibold mb-2">{{ __('app.dashboard.scan_at_kiosk') }}</p>
        <button type="button" id="qrFullscreenBtn" class="block w-full text-left">
            <div class="p-4 rounded-xl bg-white flex justify-center">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=CATCH-{{ $user->id }}"
                     alt="Check-in QR code" class="w-40 h-40">
            </div>
            <p class="text-slate-500 text-xs mt-2">{{ __('app.dashboard.tap_qr_fullscreen') }}</p>
        </button>
    </div>
</div>

<!-- Fullscreen QR overlay (on this page) -->
<div id="qrFullscreenOverlay" class="hidden fixed inset-0 z-[10001] bg-black flex flex-col items-center justify-center p-4"
     style="position:fixed;top:0;left:0;right:0;bottom:0;"
     onclick="this.classList.add('hidden')">
    <p class="text-white text-sm mb-4">{{ __('app.dashboard.tap_qr_fullscreen') }}</p>
    <div class="p-6 rounded-2xl bg-white">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=320x320&data=CATCH-{{ $user->id }}"
             alt="Check-in QR code" class="w-64 h-64 sm:w-80 sm:h-80">
    </div>
    <p class="text-slate-500 text-xs mt-4">CATCH-{{ $user->id }}</p>
</div>

<script>
(function() {
    var checkinTodayUrl = '{{ route("checkin.today") }}';
    var csrfToken = document.querySelector('meta[name="csrf-token"]') && document.querySelector('meta[name="csrf-token"]').content;

    document.getElementById('qrFullscreenBtn').addEventListener('click', function() {
        document.getElementById('qrFullscreenOverlay').classList.remove('hidden');
    });

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
@endsection
