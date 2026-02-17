<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LiffAuthController extends Controller
{
    /** Allowed redirect paths after LIFF login (no leading slash). */
    protected const ALLOWED_PATHS = [
        'payments',
        'schedule',
        'dashboard',
        'shop',
        'goals',
        'settings',
        'leaderboard',
        'check-in',
        'family/dashboard',
        'family/settings',
    ];

    /**
     * Serve the LIFF entry page. When opened from LINE in-app browser, the script
     * gets an ID token and POSTs to /liff/session to log in, then redirects to the path.
     * Wrapped in try-catch so we never return 500; LINE shows "System error" when the page fails to load.
     */
    public function show(Request $request, ?string $path = null): View|RedirectResponse|\Illuminate\Http\Response
    {
        try {
            $liffId = config('services.liff.liff_id');
            $redirectPath = $this->normalizeRedirectPath($path);

            if (empty($liffId)) {
                return redirect()->guest(route('login').'?redirect='.urlencode($redirectPath));
            }

            $sessionUrl = url('/liff/session');
            $csrfToken = csrf_token();

            try {
                return view('liff.auth', [
                    'liffId' => $liffId,
                    'redirectPath' => $redirectPath,
                    'sessionUrl' => $sessionUrl,
                    'csrfToken' => $csrfToken,
                ]);
            } catch (\Throwable $viewError) {
                Log::error('LIFF view failed', [
                    'exception' => $viewError->getMessage(),
                    'trace' => $viewError->getTraceAsString(),
                ]);
                $baseUrl = rtrim(config('app.url', 'https://catchjiujitsu.com'), '/');
                $loginUrl = $baseUrl.'/login?redirect='.rawurlencode($redirectPath);
                return response(
                    '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Sign in</title></head><body style="margin:0;min-height:100vh;background:#0f172a;color:#e2e8f0;font-family:system-ui;display:flex;align-items:center;justify-content:center;flex-direction:column;padding:2rem;"><p>Loading failed. <a href="'.htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8').'" style="color:#fbbf24;">Log in here</a>.</p></body></html>',
                    200,
                    ['Content-Type' => 'text/html; charset=UTF-8']
                );
            }
        } catch (\Throwable $e) {
            Log::error('LIFF show failed', [
                'exception' => $e->getMessage(),
                'path' => $path,
                'trace' => $e->getTraceAsString(),
            ]);
            $baseUrl = config('app.url', 'https://catchjiujitsu.com');
            $baseUrl = rtrim($baseUrl, '/');
            $redirect = in_array(trim((string) $path, '/'), self::ALLOWED_PATHS, true) ? trim($path, '/') : 'dashboard';
            $loginUrl = $baseUrl.'/login?redirect='.rawurlencode('/'.$redirect);
            return response(
                '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Error</title></head><body style="margin:0;min-height:100vh;background:#0f172a;color:#e2e8f0;font-family:system-ui;display:flex;align-items:center;justify-content:center;flex-direction:column;padding:2rem;"><p>Something went wrong. Please try again or log in below.</p><a href="'.htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8').'" style="color:#fbbf24;">Log in in browser</a></body></html>',
                200,
                ['Content-Type' => 'text/html; charset=UTF-8']
            );
        }
    }

/**
 * Verify LINE ID token and log in the user by line_id; return redirect URL.
 * Uses a LINE Login channel (LIFF cannot be added to Messaging API channels).
 * Link that LINE Login channel to your Messaging API bot so ID token "sub" matches users.line_id.
 */
    public function session(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'id_token' => 'required|string',
                'redirect' => 'nullable|string|max:255',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Invalid request',
                'message' => $e->validator->errors()->first(),
            ], 422);
        }

        $channelId = config('services.liff.channel_id');
        if (empty($channelId)) {
            Log::warning('LIFF session: LINE_CHANNEL_ID not configured');
            return response()->json(['error' => 'Server configuration error', 'message' => 'LINE_CHANNEL_ID is not set.'])->setStatusCode(500);
        }

        try {
            $response = Http::asForm()->post('https://api.line.me/oauth2/v2.1/verify', [
                'id_token' => $request->input('id_token'),
                'client_id' => $channelId,
            ]);
        } catch (\Throwable $e) {
            Log::error('LIFF session: verify request failed', ['exception' => $e->getMessage()]);
            return response()->json(['error' => 'Server error', 'message' => 'Could not verify with LINE. Try again.'])->setStatusCode(500);
        }

        if (! $response->successful()) {
            Log::warning('LIFF verify ID token failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return response()->json([
                'error' => 'Invalid or expired token',
                'message' => 'LINE rejected the token. Check that LINE_CHANNEL_ID in .env matches the channel that owns the LIFF app.',
            ], 401);
        }

        $payload = $response->json();
        $lineUserId = $payload['sub'] ?? null;
        if (empty($lineUserId)) {
            return response()->json(['error' => 'No user in token'], 401);
        }

        $user = User::where('line_id', $lineUserId)->first();
        if (! $user) {
            return response()->json([
                'error' => 'Account not linked',
                'message' => 'Please link your LINE account in the portal Settings first, then try again.',
            ], 403);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        $redirectPath = $this->normalizeRedirectPath($request->input('redirect'));
        $redirectUrl = url($redirectPath);

        return response()->json(['redirect' => $redirectUrl]);
    }

    protected function normalizeRedirectPath(?string $path): string
    {
        $path = trim((string) $path, "/ \t\n\r");
        if ($path === '') {
            return '/dashboard';
        }
        if (! in_array($path, self::ALLOWED_PATHS, true)) {
            return '/dashboard';
        }
        return '/'.$path;
    }

}
