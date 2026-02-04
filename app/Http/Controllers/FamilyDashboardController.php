<?php

namespace App\Http\Controllers;

use App\Models\ClassSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FamilyDashboardController extends Controller
{
    public function index()
    {
        $me = Auth::user();
        if (!$me->isInFamily()) {
            return redirect()->route('dashboard');
        }
        if (!session()->has('viewing_family_user_id')) {
            session(['viewing_family_user_id' => $me->id]);
        }
        $user = User::currentFamilyMember();
        $user->load('membershipPackage');

        $nextClass = $user->nextBookedClass();
        $nextBooking = null;
        if ($nextClass) {
            $nextBooking = $user->bookings()->where('class_id', $nextClass->id)->first();
        }

        $classesThisMonth = $user->bookings()
            ->whereHas('classSession', fn ($q) => $q->whereMonth('start_time', now()->month)->whereYear('start_time', now()->year))
            ->count();

        $previousClasses = $user->bookedClasses()
            ->with('instructor')
            ->where('start_time', '<', now())
            ->orderBy('start_time', 'desc')
            ->take(5)
            ->get();

        $familyMembers = $me->familyMembersWithSelf();

        return view('dashboard', [
            'user' => $user,
            'nextClass' => $nextClass,
            'nextBooking' => $nextBooking,
            'classesThisMonth' => $classesThisMonth,
            'previousClasses' => $previousClasses,
            'familyBar' => true,
            'familyMembers' => $familyMembers,
        ]);
    }

    public function settings()
    {
        $me = Auth::user();
        if (!$me->isInFamily()) {
            return redirect()->route('settings');
        }
        $familyMembers = $me->familyMembersWithSelf();
        return view('family.settings', ['familyMembers' => $familyMembers]);
    }

    public function switchMember(Request $request)
    {
        $me = Auth::user();
        if (!$me->isInFamily()) {
            return redirect()->route('dashboard');
        }
        $userId = (int) $request->input('user_id');
        $member = User::find($userId);
        if (!$member || !$member->familyMember || $member->familyMember->family_id !== $me->familyMember->family_id) {
            return back()->with('error', 'Invalid family member.');
        }
        session(['viewing_family_user_id' => $userId]);
        return redirect()->route('family.dashboard')->with('success', 'Switched to ' . $member->name . '.');
    }
}
