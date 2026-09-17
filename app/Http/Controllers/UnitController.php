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
        
        $trackId = request()->query('track_id');
        $currentTrack = $trackId ? \App\Models\LearningTrack::find($trackId) : null;
        
        return view('student.units.show', [
            'unit' => $unit,
            'currentTrack' => $currentTrack,
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

        $trackId = request()->input('track_id');
        $nextUnit = null;

        if ($trackId) {
            $nextUnit = $unit->nextTrackUnit((int) $trackId);
        }

        // Fallback to standard session/module progression if not in a track or end of track
        if (!$nextUnit && !$trackId) {
            $nextUnit = $unit->nextUnit();
        }

        if ($nextUnit) {
            $redirectParams = ['unit' => $nextUnit];
            if ($trackId) {
                $redirectParams['track_id'] = $trackId;
            }

            return redirect()->route('student.units.show', $redirectParams)
                ->with('success', 'Lesson Mastered! Moving to next lesson.')
                ->with('lesson_mastered', true)
                ->with('xp_gained', 100);
        }

        $redirectParams = ['unit' => $unit];
        if ($trackId) {
            $redirectParams['track_id'] = $trackId;
        }

        return redirect()->route('student.units.show', $redirectParams)
            ->with('success', $trackId ? 'Track Completed! Incredible job!' : 'Course Completed! Great job!')
            ->with('course_completed', true)
            ->with('xp_gained', 500); // Bonus for course completion
    }
}
