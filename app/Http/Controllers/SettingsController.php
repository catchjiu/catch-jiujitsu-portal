<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Intervention\Image\Laravel\Facades\Image;

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
     * Update avatar/profile picture.
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // Allow up to 10MB upload, we'll compress it
        ]);

        $user = $request->user();

        // Delete old avatar if exists
        if ($user->avatar_url && Storage::disk('public')->exists($user->avatar_url)) {
            Storage::disk('public')->delete($user->avatar_url);
        }

        // Process and compress image
        $file = $request->file('avatar');
        $filename = 'avatars/' . uniqid() . '_' . time() . '.jpg';
        
        // Read image and resize/compress
        $image = Image::read($file->getPathname());
        
        // Resize to max 500x500 while maintaining aspect ratio
        $image->scaleDown(500, 500);
        
        // Start with quality 90 and reduce until under 1MB
        $quality = 90;
        $maxSize = 1024 * 1024; // 1MB in bytes
        
        do {
            $encoded = $image->toJpeg($quality);
            $size = strlen($encoded);
            
            if ($size > $maxSize && $quality > 20) {
                $quality -= 10;
            } else {
                break;
            }
        } while ($quality > 20);
        
        // Save to storage
        Storage::disk('public')->put($filename, $encoded);

        $user->update([
            'avatar_url' => $filename,
        ]);

        return back()->with('success', 'Profile picture updated successfully.');
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
