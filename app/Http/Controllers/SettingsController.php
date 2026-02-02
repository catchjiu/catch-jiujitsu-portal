<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
    public function index()
    {
        return view('settings', [
            'user' => auth()->user(),
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
     * Update avatar/profile picture.
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // 2MB max (nginx default limit)
        ]);

        $user = $request->user();

        // Delete old avatar if exists
        if ($user->avatar_url && Storage::disk('public')->exists($user->avatar_url)) {
            Storage::disk('public')->delete($user->avatar_url);
        }

        // Process and compress image using GD library
        $file = $request->file('avatar');
        $filename = 'avatars/' . uniqid() . '_' . time() . '.jpg';
        
        // Create image from uploaded file
        $sourceImage = $this->createImageFromFile($file->getPathname(), $file->getMimeType());
        
        if (!$sourceImage) {
            return back()->with('error', 'Unable to process image.');
        }
        
        // Get original dimensions
        $origWidth = imagesx($sourceImage);
        $origHeight = imagesy($sourceImage);
        
        // Calculate new dimensions (max 500x500, maintain aspect ratio)
        $maxSize = 500;
        if ($origWidth > $maxSize || $origHeight > $maxSize) {
            if ($origWidth > $origHeight) {
                $newWidth = $maxSize;
                $newHeight = intval($origHeight * ($maxSize / $origWidth));
            } else {
                $newHeight = $maxSize;
                $newWidth = intval($origWidth * ($maxSize / $origHeight));
            }
        } else {
            $newWidth = $origWidth;
            $newHeight = $origHeight;
        }
        
        // Create resized image
        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($resizedImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
        
        // Save to temp file with compression
        $tempFile = tempnam(sys_get_temp_dir(), 'avatar_');
        $quality = 85;
        imagejpeg($resizedImage, $tempFile, $quality);
        
        // Clean up
        imagedestroy($sourceImage);
        imagedestroy($resizedImage);
        
        // Move to storage
        Storage::disk('public')->put($filename, file_get_contents($tempFile));
        unlink($tempFile);

        $user->update([
            'avatar_url' => $filename,
        ]);

        return back()->with('success', 'Profile picture updated successfully.');
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
            'public_profile' => 'boolean',
            'reminders_enabled' => 'boolean',
        ]);

        $user->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'public_profile' => $request->boolean('public_profile'),
            'reminders_enabled' => $request->boolean('reminders_enabled'),
        ]);

        return back()->with('success', 'Profile updated successfully.');
    }
}
