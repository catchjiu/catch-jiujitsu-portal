<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ app()->getLocale() === 'zh-TW' ? '登入中…' : 'Signing in…' }} - Catch Jiu Jitsu</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <style>
        body { margin: 0; min-height: 100vh; background: #0f172a; color: #e2e8f0; font-family: system-ui, sans-serif; display: flex; align-items: center; justify-content: center; }
        .box { text-align: center; padding: 2rem; }
        .spinner { width: 40px; height: 40px; border: 3px solid rgba(251,191,36,.3); border-top-color: #fbbf24; border-radius: 50%; animation: spin .8s linear infinite; margin: 0 auto 1rem; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .err { color: #f87171; margin: 1rem 0; }
        a { color: #fbbf24; }
    </style>
</head>
<body>
    <div class="box" id="app">
        <div class="spinner" id="spinner"></div>
        <p id="msg">{{ app()->getLocale() === 'zh-TW' ? '登入中…' : 'Signing in…' }}</p>
        <p class="err" id="err" style="display:none;"></p>
        <a id="fallback" href="{{ url('/login') }}?redirect={{ urlencode($redirectPath) }}" style="display:none;">{{ app()->getLocale() === 'zh-TW' ? '在瀏覽器登入' : 'Log in in browser' }}</a>
    </div>

    <script>
(function() {
    var liffId = @json($liffId);
    var sessionUrl = @json($sessionUrl);
    var redirectPath = @json($redirectPath);
    var csrfToken = @json($csrfToken);

    function showErr(msg) {
        document.getElementById('spinner').style.display = 'none';
        document.getElementById('msg').style.display = 'none';
        var err = document.getElementById('err');
        err.textContent = msg;
        err.style.display = 'block';
        document.getElementById('fallback').style.display = 'inline';
    }

    function goLogin() {
        window.location.href = '{{ url("/login") }}?redirect=' + encodeURIComponent(redirectPath);
    }

    if (!liffId) {
        goLogin();
        return;
    }

    var script = document.createElement('script');
    script.src = 'https://static.line-scdn.net/liff/edge/2/sdk.js';
    script.onload = function() {
        if (!window.liff) {
            showErr('LINE SDK failed to load.');
            return;
        }
        window.liff.init({ liffId: liffId })
            .then(function() {
                if (!window.liff.isInClient()) {
                    goLogin();
                    return;
                }
                return window.liff.getIDToken();
            })
            .then(function(idToken) {
                if (!idToken) return;
                var form = new FormData();
                form.append('id_token', idToken);
                form.append('redirect', redirectPath.replace(/^\//, ''));
                form.append('_token', csrfToken);
                return fetch(sessionUrl, {
                    method: 'POST',
                    body: form,
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
            })
            .then(function(res) {
                if (!res) return;
                return res.json().then(function(data) {
                    if (res.ok && data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        showErr(data.message || data.error || 'Login failed.');
                    }
                });
            })
            .catch(function(e) {
                showErr(e.message || 'Something went wrong.');
            });
    };
    script.onerror = function() {
        showErr('LINE SDK failed to load.');
        document.getElementById('fallback').style.display = 'inline';
    };
    document.body.appendChild(script);
})();
    </script>
</body>
</html>
