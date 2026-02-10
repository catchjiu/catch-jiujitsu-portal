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
                if (!window.liff.isLoggedIn()) {
                    window.liff.login();
                    return;
                }
                return window.liff.getIDToken();
            })
            .then(function(idToken) {
                if (!idToken) {
                    showErr('Could not get LINE identity. Ensure the LIFF app has "openid" scope in LINE Developers. Use the link below to log in with email.');
                    document.getElementById('fallback').style.display = 'inline';
                    return;
                }
                var form = new FormData();
                form.append('id_token', idToken);
                form.append('redirect', redirectPath.replace(/^\//, ''));
                form.append('_token', csrfToken);
                var controller = new AbortController();
                var timeoutId = setTimeout(function() { controller.abort(); }, 15000);
                return fetch(sessionUrl, {
                    method: 'POST',
                    body: form,
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    signal: controller.signal
                }).then(function(res) {
                    clearTimeout(timeoutId);
                    return res;
                }).catch(function(e) {
                    clearTimeout(timeoutId);
                    throw e;
                });
            })
            .then(function(res) {
                if (!res) return;
                var ct = res.headers.get('Content-Type') || '';
                if (!ct.includes('application/json')) {
                    showErr('Server returned an error. Try logging in with the link below.');
                    document.getElementById('fallback').style.display = 'inline';
                    return;
                }
                return res.json().then(function(data) {
                    if (res.ok && data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        showErr(data.message || data.error || 'Login failed.');
                        document.getElementById('fallback').style.display = 'inline';
                    }
                });
            })
            .catch(function(e) {
                if (e.name === 'AbortError') {
                    showErr('Request timed out. Try the link below to log in.');
                } else {
                    showErr(e.message || 'Something went wrong. Try the link below.');
                }
                document.getElementById('fallback').style.display = 'inline';
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
