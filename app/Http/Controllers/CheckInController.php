<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckInController extends Controller
{
    /**
     * Show the check-in kiosk page (full-screen scan + welcome).
     * Public route – no auth required. Open in new tab for monitor.
     */
    public function show(): \Illuminate\View\View
    {
        return view('checkin');
    }

    /**
     * Look up a member by QR code for check-in kiosk.
     * Code can be numeric id (e.g. "1") or prefixed (e.g. "CATCH-1").
     * Public route - no auth required.
     */
    public function lookup(Request $request): JsonResponse
    {
        try {
            $code = $request->query('code', $request->input('code', ''));
            $code = trim((string) $code);
            $code = preg_replace('/^CATCH-?/i', '', $code);

            if ($code === '') {
                return response()->json(['message' => 'Missing code'], 422);
            }

            $id = filter_var($code, FILTER_VALIDATE_INT);
            if ($id === false) {
                return response()->json(['message' => 'Invalid code'], 422);
            }

            $user = User::find($id);
            if (!$user) {
                return response()->json(['message' => 'Member not found'], 404);
            }

            $avatarUrl = $user->avatar_url
                ? (str_starts_with($user->avatar_url, 'http')
                    ? $user->avatar_url
                    : asset('storage/' . $user->avatar_url))
                : null;

            $hoursThisYear = 0.0;
            if (method_exists($user, 'getHoursThisYearAttribute')) {
                $hoursThisYear = (float) $user->hours_this_year;
            }

            $classesThisMonth = 0;
            if (isset($user->monthly_classes_attended)) {
                $classesThisMonth = (int) $user->monthly_classes_attended;
            }

            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'rank' => $user->rank ?? 'White',
                'stripes' => (int) ($user->stripes ?? 0),
                'beltVariation' => $user->belt_variation,
                'avatarUrl' => $avatarUrl,
                'hoursThisYear' => $hoursThisYear,
                'classesThisMonth' => $classesThisMonth,
                'membershipExpiresAt' => $user->membership_expires_at
                    ? $user->membership_expires_at->format('Y-m-d')
                    : null,
                'isActive' => $user->hasActiveMembership(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Check-in lookup failed', ['code' => $request->query('code'), 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Server error'], 500);
        }
    }
}
