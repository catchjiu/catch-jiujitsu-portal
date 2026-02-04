<?php

namespace App\Http\Controllers;

use App\Models\CoachAvailability;
use App\Models\PrivateClassBooking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PrivateClassController extends Controller
{
    /**
     * List coaches accepting private classes (for member booking modal).
     */
    public function coaches()
    {
        $coaches = User::where('is_coach', true)
            ->where('accepting_private_classes', true)
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'avatar_url', 'private_class_price']);

        return response()->json($coaches->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'avatar' => $c->avatar,
                'price' => $c->private_class_price ? (int) $c->private_class_price : null,
            ];
        }));
    }

    /**
     * Get available time slots for a coach (next 4 weeks).
     */
    public function availability($coachId)
    {
        $coach = User::where('id', $coachId)->where('is_coach', true)->where('accepting_private_classes', true)->firstOrFail();
        $slots = $this->getAvailableSlotsForCoach($coach, 4);

        return response()->json([
            'coach' => [
                'id' => $coach->id,
                'name' => $coach->name,
                'avatar' => $coach->avatar,
                'price' => $coach->private_class_price ? (int) $coach->private_class_price : null,
            ],
            'slots' => $slots,
        ]);
    }

    /**
     * Submit a private class request (member).
     */
    public function request(Request $request)
    {
        $member = User::currentFamilyMember();
        if ($member->is_admin) {
            return back()->with('error', 'Admins cannot request private classes.');
        }

        $validated = $request->validate([
            'coach_id' => 'required|exists:users,id',
            'scheduled_at' => 'required|date|after:now',
            'duration_minutes' => 'nullable|integer|min:30|max:180',
        ]);

        $coach = User::findOrFail($validated['coach_id']);
        if (!$coach->is_coach || !$coach->accepting_private_classes) {
            return back()->with('error', 'This coach is not accepting private classes.');
        }

        $duration = $validated['duration_minutes'] ?? 60;
        $scheduledAt = Carbon::parse($validated['scheduled_at']);

        // Verify slot is available
        $slots = $this->getAvailableSlotsForCoach($coach, 4);
        $slotKey = $scheduledAt->format('Y-m-d H:i');
        if (!isset($slots[$slotKey])) {
            return back()->with('error', 'That time slot is no longer available.');
        }

        PrivateClassBooking::create([
            'coach_id' => $coach->id,
            'member_id' => $member->id,
            'scheduled_at' => $scheduledAt,
            'duration_minutes' => $duration,
            'status' => 'pending',
            'price' => $coach->private_class_price,
            'requested_at' => now(),
        ]);

        return back()->with('success', 'Private class request sent. The coach will respond shortly.');
    }

    /**
     * Coach: list pending requests.
     */
    public function requests()
    {
        $coach = Auth::user();
        if (!$coach->is_coach) {
            return redirect()->route('dashboard');
        }

        $pending = PrivateClassBooking::with('member')
            ->where('coach_id', $coach->id)
            ->where('status', 'pending')
            ->orderBy('scheduled_at')
            ->get();

        return view('coach.private-requests', [
            'pendingRequests' => $pending,
        ]);
    }

    /**
     * Coach: accept a request.
     */
    public function acceptRequest($id)
    {
        $booking = PrivateClassBooking::where('coach_id', Auth::id())->where('status', 'pending')->findOrFail($id);
        $booking->update(['status' => 'accepted', 'responded_at' => now()]);
        return back()->with('success', 'Private class accepted.');
    }

    /**
     * Coach: decline a request.
     */
    public function declineRequest($id)
    {
        $booking = PrivateClassBooking::where('coach_id', Auth::id())->where('status', 'pending')->findOrFail($id);
        $booking->update(['status' => 'declined', 'responded_at' => now()]);
        return back()->with('success', 'Request declined.');
    }

    /**
     * Coach: availability + calendar page.
     */
    public function availabilityPage()
    {
        $coach = Auth::user();
        if (!$coach->is_coach) {
            return redirect()->route('dashboard');
        }

        $availability = $coach->coachAvailability()->orderBy('day_of_week')->orderBy('start_time')->get();
        $bookings = PrivateClassBooking::with('member')
            ->where('coach_id', $coach->id)
            ->whereIn('status', ['pending', 'accepted'])
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at')
            ->get();

        return view('coach.private-availability', [
            'availability' => $availability,
            'bookings' => $bookings,
            'dayNames' => CoachAvailability::dayNames(),
        ]);
    }

    /**
     * Coach: save availability slots.
     */
    public function saveAvailability(Request $request)
    {
        $coach = Auth::user();
        if (!$coach->is_coach) {
            return redirect()->route('dashboard');
        }

        $validated = $request->validate([
            'slots' => 'array',
            'slots.*.day_of_week' => 'required|integer|min:0|max:6',
            'slots.*.start_time' => 'required|date_format:H:i',
            'slots.*.end_time' => 'required|date_format:H:i|after:slots.*.start_time',
            'slots.*.slot_duration_minutes' => 'required|integer|min:30|max:180',
        ]);

        DB::transaction(function () use ($coach, $validated) {
            $coach->coachAvailability()->delete();
            foreach ($validated['slots'] ?? [] as $slot) {
                $coach->coachAvailability()->create([
                    'day_of_week' => (int) $slot['day_of_week'],
                    'start_time' => $slot['start_time'],
                    'end_time' => $slot['end_time'],
                    'slot_duration_minutes' => (int) $slot['slot_duration_minutes'],
                ]);
            }
        });

        return back()->with('success', 'Availability saved.');
    }

    /**
     * Generate available slot datetimes for a coach for the next N weeks.
     */
    private function getAvailableSlotsForCoach(User $coach, int $weeks = 4): array
    {
        $availability = $coach->coachAvailability()->get();
        if ($availability->isEmpty()) {
            return [];
        }

        $start = Carbon::today()->startOfDay();
        $end = $start->copy()->addWeeks($weeks);
        $slots = [];

        for ($date = $start->copy(); $date->lt($end); $date->addDay()) {
            $dayOfWeek = (int) $date->format('w'); // 0 = Sun, 6 = Sat
            $dayBlocks = $availability->where('day_of_week', $dayOfWeek);
            foreach ($dayBlocks as $block) {
                $slotStart = Carbon::parse($date->format('Y-m-d') . ' ' . $block->start_time);
                $endTime = Carbon::parse($date->format('Y-m-d') . ' ' . $block->end_time);
                $duration = (int) $block->slot_duration_minutes;
                while ($slotStart->copy()->addMinutes($duration)->lte($endTime)) {
                    if ($slotStart->isFuture()) {
                        $key = $slotStart->format('Y-m-d H:i');
                        $slots[$key] = [
                            'datetime' => $slotStart->toIso8601String(),
                            'label' => $slotStart->format('D, M j \a\t g:i A'),
                        ];
                    }
                    $slotStart->addMinutes($duration);
                }
            }
        }

        // Remove booked slots
        $booked = PrivateClassBooking::where('coach_id', $coach->id)
            ->whereIn('status', ['pending', 'accepted'])
            ->where('scheduled_at', '>=', now())
            ->get();
        foreach ($booked as $b) {
            $key = $b->scheduled_at->format('Y-m-d H:i');
            unset($slots[$key]);
        }

        ksort($slots);
        return $slots;
    }
}
