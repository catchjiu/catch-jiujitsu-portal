<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GoalsController extends Controller
{
    /**
     * Display goals and settings page.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Calculate current progress
        $classesAttended = $user->monthly_classes_attended;
        $hoursTrainded = $user->monthly_hours_trained;
        
        // Calculate progress percentages
        $classProgress = $user->monthly_class_goal > 0 
            ? min(100, ($classesAttended / $user->monthly_class_goal) * 100) 
            : 0;
        $hoursProgress = $user->monthly_hours_goal > 0 
            ? min(100, ($hoursTrainded / $user->monthly_hours_goal) * 100) 
            : 0;

        // Calculate remaining
        $classesRemaining = max(0, $user->monthly_class_goal - $classesAttended);
        $hoursRemaining = max(0, $user->monthly_hours_goal - $hoursTrainded);

        return view('goals', [
            'user' => $user,
            'classesAttended' => $classesAttended,
            'hoursTrained' => $hoursTrainded,
            'classProgress' => $classProgress,
            'hoursProgress' => $hoursProgress,
            'classesRemaining' => $classesRemaining,
            'hoursRemaining' => $hoursRemaining,
            'currentMonth' => now()->format('F'),
        ]);
    }

    /**
     * Update goals and settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'monthly_class_goal' => 'required|integer|min:1|max:50',
            'monthly_hours_goal' => 'required|integer|min:1|max:100',
            'reminders_enabled' => 'boolean',
            'public_profile' => 'boolean',
        ]);

        $user = Auth::user();
        $user->update([
            'monthly_class_goal' => $validated['monthly_class_goal'],
            'monthly_hours_goal' => $validated['monthly_hours_goal'],
            'reminders_enabled' => $request->boolean('reminders_enabled'),
            'public_profile' => $request->boolean('public_profile'),
        ]);

        return back()->with('success', 'Settings saved successfully.');
    }
}
