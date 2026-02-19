<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    /**
     * Show the login form.
     * Accepts ?redirect=/path for post-login redirect (e.g. from LIFF fallback).
     */
    public function showLogin(Request $request)
    {
        $redirect = $request->query('redirect');
        if (is_string($redirect) && str_starts_with($redirect, '/') && ! str_starts_with($redirect, '//')) {
            $request->session()->put('url.intended', $redirect);
        }

        return view('auth.login');
    }

    /**
     * Handle login request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Some deployments end up with NULL/empty passwords (or wrong column types) for existing users.
        // That can trigger a runtime 500 inside the hasher when an email exists. Fail gracefully instead.
        $runtimeDebug = (bool) env('APP_RUNTIME_DEBUG', false);
        try {
            $user = User::query()->where('email', $credentials['email'])->first();
            if ($user !== null) {
                $hash = $user->getAuthPassword();
                if (!is_string($hash) || $hash === '') {
                    Log::warning('Login blocked: user password hash missing/invalid', [
                        'email' => $credentials['email'],
                        'password_type' => get_debug_type($hash),
                    ]);

                    return back()->withErrors([
                        'email' => $runtimeDebug
                            ? 'This account has no password hash set (password is NULL/empty).'
                            : 'The provided credentials do not match our records.',
                    ])->onlyInput('email');
                }
            }
        } catch (\Throwable $e) {
            // Best-effort only. Login can still proceed; failure will be handled below.
            Log::warning('Pre-login password sanity check failed', [
                'email' => $credentials['email'] ?? null,
                'error' => $e->getMessage(),
            ]);
        }

        try {
            if (Auth::attempt($credentials, $request->boolean('remember'))) {
                $request->session()->regenerate();

                // Redirect based on user role
                if (Auth::user()->isAdmin()) {
                    return redirect()->intended('/admin');
                }

                return redirect()->intended('/dashboard');
            }
        } catch (\Throwable $e) {
            Log::error('Login failed with runtime error', [
                'email' => $request->input('email'),
                'error' => $e->getMessage(),
            ]);

            // Ensure the error is visible via /debug/runtime even if global handlers don't capture it.
            try {
                @file_put_contents(
                    storage_path('app/runtime-last-exception.json'),
                    json_encode([
                        'time' => date('c'),
                        'type' => $e::class,
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'url' => $request->getRequestUri(),
                        'method' => $request->method(),
                        'email' => (string) $request->input('email', ''),
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
                );
            } catch (\Throwable) {
                // Best-effort only.
            }

            return response()->view('auth.login', [
                'runtimeError' => app()->getLocale() === 'zh-TW'
                    ? '目前無法登入，請稍後再試。'
                    : 'Login is temporarily unavailable. Please try again in a moment.',
            ], 503);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Show the registration form.
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Handle registration request.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:50',
            'dob' => 'nullable|date',
            'line_id' => 'nullable|string|max:255',
            'password' => 'required|string|min:8|confirmed',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'avatar_data' => 'nullable|string',
            'waiver_accepted' => 'required|accepted',
            'g-recaptcha-response' => 'required',
        ], [
            'g-recaptcha-response.required' => 'Please complete the CAPTCHA verification.',
            'waiver_accepted.required' => __('app.auth.waiver_required'),
            'waiver_accepted.accepted' => __('app.auth.waiver_required'),
        ]);

        // Verify reCAPTCHA
        $recaptchaResponse = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => config('services.recaptcha.secret_key'),
            'response' => $request->input('g-recaptcha-response'),
            'remoteip' => $request->ip(),
        ]);

        if (!$recaptchaResponse->json('success')) {
            return back()->withErrors(['g-recaptcha-response' => 'CAPTCHA verification failed. Please try again.'])->withInput();
        }

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'dob' => isset($validated['dob']) ? $validated['dob'] : null,
            'line_id' => $validated['line_id'] ?? null,
            'password' => Hash::make($validated['password']),
        ]);

        // Process profile picture (from file upload or cropped base64)
        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $this->processAvatarUpload($request->file('avatar'));
        } elseif (!empty($validated['avatar_data']) && preg_match('/^data:image\/(\w+);base64,/', $validated['avatar_data'], $m)) {
            $avatarPath = $this->processAvatarBase64($validated['avatar_data']);
        }
        if ($avatarPath) {
            $user->update(['avatar_url' => $avatarPath]);
        }

        Auth::login($user);

        return redirect('/dashboard');
    }

    /**
     * Handle logout request.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Process uploaded avatar: resize and compress to under 1MB. Returns storage path or null.
     */
    private function processAvatarUpload($file): ?string
    {
        $sourceImage = $this->createImageFromPath($file->getPathname(), $file->getMimeType());
        if (!$sourceImage) {
            return null;
        }
        return $this->resizeAndSaveAvatar($sourceImage);
    }

    /**
     * Process base64 avatar (e.g. from cropper). Returns storage path or null.
     */
    private function processAvatarBase64(string $dataUrl): ?string
    {
        if (!preg_match('/^data:image\/(\w+);base64,(.+)$/', $dataUrl, $m)) {
            return null;
        }
        $ext = strtolower($m[1]);
        $blob = base64_decode($m[2], true);
        if ($blob === false) {
            return null;
        }
        $tmp = tempnam(sys_get_temp_dir(), 'avatar_');
        file_put_contents($tmp, $blob);
        $mime = 'image/' . ($ext === 'jpeg' ? 'jpeg' : $ext);
        $sourceImage = $this->createImageFromPath($tmp, $mime);
        unlink($tmp);
        if (!$sourceImage) {
            return null;
        }
        return $this->resizeAndSaveAvatar($sourceImage);
    }

    private function createImageFromPath(string $path, string $mimeType)
    {
        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                return @imagecreatefromjpeg($path);
            case 'image/png':
                return @imagecreatefrompng($path);
            case 'image/gif':
                return @imagecreatefromgif($path);
            case 'image/webp':
                return @imagecreatefromwebp($path);
            default:
                return null;
        }
    }

    /**
     * Resize image to max 500px and compress to under 1MB. Returns storage path.
     */
    private function resizeAndSaveAvatar($sourceImage): ?string
    {
        $maxBytes = 1024 * 1024; // 1MB
        $maxSize = 500;
        $origWidth = imagesx($sourceImage);
        $origHeight = imagesy($sourceImage);
        if ($origWidth > $maxSize || $origHeight > $maxSize) {
            if ($origWidth > $origHeight) {
                $newWidth = $maxSize;
                $newHeight = (int) round($origHeight * ($maxSize / $origWidth));
            } else {
                $newHeight = $maxSize;
                $newWidth = (int) round($origWidth * ($maxSize / $origHeight));
            }
        } else {
            $newWidth = $origWidth;
            $newHeight = $origHeight;
        }
        $resized = imagecreatetruecolor($newWidth, $newHeight);
        if (!$resized) {
            imagedestroy($sourceImage);
            return null;
        }
        imagecopyresampled($resized, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
        imagedestroy($sourceImage);

        $filename = 'avatars/' . uniqid('reg_', true) . '.jpg';
        $quality = 85;
        do {
            $tempFile = tempnam(sys_get_temp_dir(), 'avatar_');
            imagejpeg($resized, $tempFile, $quality);
            $size = filesize($tempFile);
            if ($size <= $maxBytes) {
                Storage::disk('public')->put($filename, file_get_contents($tempFile));
                unlink($tempFile);
                imagedestroy($resized);
                return $filename;
            }
            unlink($tempFile);
            $quality -= 10;
        } while ($quality >= 20);
        imagedestroy($resized);
        return null;
    }
}
