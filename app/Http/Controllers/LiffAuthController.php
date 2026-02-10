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
     */
    public function show(Request $request, ?string $path = null): View|RedirectResponse
    {
        $liffId = config('services.liff.liff_id');
        $redirectPath = $this->normalizeRedirectPath($path);

        if (empty($liffId)) {
            return redirect()->guest(route('login').'?redirect='.urlencode($redirectPath));
        }

        return view('liff.auth', [
            'liffId' => $liffId,
            'redirectPath' => $redirectPath,
            'sessionUrl' => url('/liff/session'),
            'csrfToken' => csrf_token(),
        ]);
    }

/**
 * Verify LINE ID token and log in the user by line_id; return redirect URL.
 * Uses a LINE Login channel (LIFF cannot be added to Messaging API channels).
 * Link that LINE Login channel to your Messaging API bot so ID token "sub" matches users.line_id.
 */
    public function session(Request $request): JsonResponse
    {
        $request->validate([
            'id_token' => 'required|string',
            'redirect' => 'nullable|string|max:255',
        ]);

        $channelId = config('services.liff.channel_id');
        if (empty($channelId)) {
            Log::warning('LIFF session: LINE_CHANNEL_ID not configured');
            return response()->json(['error' => 'Server configuration error'], 500);
        }

        $response = Http::asForm()->post('https://api.line.me/oauth2/v2.1/verify', [
            'id_token' => $request->input('id_token'),
            'client_id' => $channelId,
        ]);

        if (! $response->successful()) {
            Log::warning('LIFF verify ID token failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return response()->json(['error' => 'Invalid or expired token'], 401);
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
