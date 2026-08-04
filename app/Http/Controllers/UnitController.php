<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function show(Unit $unit)
    {
        $user = auth()->user();

        if (!$unit->isAccessibleBy($user)) {
            if (!$user) {
                return redirect()->route('login')->with('info', 'Please sign in to access this lesson.');
            }

            if (!$user->isPaid()) {
                return redirect()->route('pricing')->with('warning', 'This interactive lesson is exclusive to Premium Members. Upgrade your plan to unlock instant access!');
            }

            abort(403, 'You do not have access to this lesson.');
        }
        
        return view('student.units.show', [
            'unit' => $unit,
            'libraryId' => 569307, // Your Bunny Library ID (better to put this in config/services.php)
        ]);
    }

    public function complete(Unit $unit)
    {
        $user = auth()->user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Mark as complete (avoid duplicates)
        if (!$user->completedUnits()->where('unit_id', $unit->id)->exists()) {
            $user->completedUnits()->attach($unit->id, ['completed_at' => now()]);
            
            // Check for milestones immediately
            $milestoneService = new \App\Services\MilestoneService();
            $milestoneService->checkMilestones($user, $unit);
        }

        // Find next unit
        $nextUnit = $unit->nextUnit();

        if ($nextUnit) {
            return redirect()->route('student.units.show', $nextUnit)
                ->with('success', 'Lesson Mastered! Moving to next lesson.')
                ->with('lesson_mastered', true)
                ->with('xp_gained', 100);
        }

        return redirect()->route('student.units.show', $unit)
            ->with('success', 'Course Completed! Great job!')
            ->with('course_completed', true)
            ->with('xp_gained', 500); // Bonus for course completion
    }
}
