<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\LineMessagingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index(LineMessagingService $lineMessaging)
    {
        $user = auth()->user();
        if ($user->hasLineNotify()) {
            Session::forget('line_connect_code');
        }

        return view('settings', [
            'user' => $user,
            'line_configured' => $lineMessaging->isConfigured(),
            'line_add_friend_url' => $lineMessaging->getAddFriendUrl(),
        ]);
    }

    /**
     * Update user locale/language preference.
     */
    public function updateLocale(Request $request)
    {
        $validated = $request->validate([
            'locale' => 'required|in:en,zh-TW',
        ]);

        $locale = $validated['locale'];

        // Store in session
        Session::put('locale', $locale);

        // Store in user record if logged in
        if (auth()->check()) {
            auth()->user()->update(['locale' => $locale]);
        }

        // Set cookie for 1 year
        return back()
            ->with('success', __('app.messages.saved_successfully'))
            ->cookie('locale', $locale, 60 * 24 * 365);
    }

    /**
     * Update avatar/profile picture. Accepts either file upload or base64 (from cropper, max 1MB).
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'avatar_data' => 'nullable|string',
        ]);

        if (! $request->hasFile('avatar') && empty($request->input('avatar_data'))) {
            return back()->with('error', 'Please select a photo.');
        }

        $user = $request->user();

        if ($user->avatar_url && Storage::disk('public')->exists($user->avatar_url)) {
            Storage::disk('public')->delete($user->avatar_url);
        }

        $filename = null;
        if (! empty($request->input('avatar_data')) && preg_match('/^data:image\/(\w+);base64,/', $request->input('avatar_data'))) {
            $filename = $this->processAvatarBase64($request->input('avatar_data'));
        } elseif ($request->hasFile('avatar')) {
            $filename = $this->processAvatarFile($request->file('avatar'));
        }

        if (! $filename) {
            return back()->with('error', 'Unable to process image. Max 1MB.');
        }

        $user->update(['avatar_url' => $filename]);

        return back()->with('success', 'Profile picture updated successfully.');
    }

    /**
     * Process base64 avatar (from cropper). Resize and compress to under 1MB. Returns path or null.
     */
    private function processAvatarBase64(string $dataUrl): ?string
    {
        if (! preg_match('/^data:image\/(\w+);base64,(.+)$/', $dataUrl, $m)) {
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
        $sourceImage = $this->createImageFromFile($tmp, $mime);
        unlink($tmp);
        if (! $sourceImage) {
            return null;
        }
        return $this->resizeAndSaveAvatar($sourceImage);
    }

    /**
     * Process uploaded file. Resize and compress to under 1MB. Returns path or null.
     */
    private function processAvatarFile($file): ?string
    {
        $sourceImage = $this->createImageFromFile($file->getPathname(), $file->getMimeType());
        if (! $sourceImage) {
            return null;
        }
        return $this->resizeAndSaveAvatar($sourceImage);
    }

    /**
     * Resize to max 500px and compress to under 1MB. Returns storage path or null.
     */
    private function resizeAndSaveAvatar($sourceImage): ?string
    {
        $maxBytes = 1024 * 1024;
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
        if (! $resized) {
            imagedestroy($sourceImage);
            return null;
        }
        imagecopyresampled($resized, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
        imagedestroy($sourceImage);

        $filename = 'avatars/' . uniqid() . '_' . time() . '.jpg';
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
    
    /**
     * Create GD image resource from file.
     */
    private function createImageFromFile($path, $mimeType)
    {
        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                return imagecreatefromjpeg($path);
            case 'image/png':
                return imagecreatefrompng($path);
            case 'image/gif':
                return imagecreatefromgif($path);
            case 'image/webp':
                return imagecreatefromwebp($path);
            default:
                return null;
        }
    }

    /**
     * Remove avatar.
     */
    public function removeAvatar(Request $request)
    {
        $user = $request->user();

        if ($user->avatar_url && Storage::disk('public')->exists($user->avatar_url)) {
            Storage::disk('public')->delete($user->avatar_url);
        }

        $user->update([
            'avatar_url' => null,
        ]);

        return back()->with('success', 'Profile picture removed.');
    }

    /**
     * Update password.
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.current_password' => 'The current password is incorrect.',
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password updated successfully.');
    }

    /**
     * Update profile settings.
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'dob' => 'nullable|date',
            'public_profile' => 'boolean',
            'reminders_enabled' => 'boolean',
        ]);

        $user->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'dob' => !empty($validated['dob']) ? $validated['dob'] : null,
            'public_profile' => $request->boolean('public_profile'),
            'reminders_enabled' => $request->boolean('reminders_enabled'),
        ]);

        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Update coach private class settings (accepting + price).
     */
    public function updatePrivateClass(Request $request)
    {
        $user = $request->user();
        if (!$user->is_coach) {
            return back()->with('error', 'Not authorized.');
        }

        $validated = $request->validate([
            'accepting_private_classes' => 'boolean',
            'private_class_price' => 'nullable|numeric|min:0|max:99999',
        ]);

        $user->update([
            'accepting_private_classes' => $request->boolean('accepting_private_classes'),
            'private_class_price' => $validated['private_class_price'] ?? null,
        ]);

        return back()->with('success', 'Private class settings updated.');
    }

    /**
     * Start LINE connect flow: generate 6-digit code, store in cache, redirect to settings to show code.
     * User adds the bot and replies in LINE with this code; webhook links their line_id to this user.
     */
    public function lineConnect(LineMessagingService $lineMessaging)
    {
        if (! $lineMessaging->isConfigured()) {
            return redirect()->route('settings')->with('error', 'LINE is not configured.');
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        Cache::put('line_link:' . $code, auth()->id(), now()->addMinutes(5));

        return redirect()->route('settings')->with('line_connect_code', $code);
    }

    /**
     * Disconnect LINE (clear line_id so they no longer receive reminders via LINE).
     */
    public function lineDisconnect()
    {
        auth()->user()->update([
            'line_id' => null,
            'line_notify_token' => null,
        ]);

        return back()->with('success', app()->getLocale() === 'zh-TW' ? '已取消連結 LINE。' : 'LINE disconnected.');
    }
}
