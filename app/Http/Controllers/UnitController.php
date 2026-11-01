<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class UnitController extends Controller
{
    public function show(Unit $unit)
    {
        $user = auth()->user();

        if (!$unit->isAccessibleBy($user)) {
            if (!$user) {
                if ($unit->is_registered_only) {
                    return redirect()->route('register')->with('info', 'കൂടുതൽ ഫ്രീ ക്ലാസ്സുകൾക്കായി സൗജന്യമായി റജിസ്റ്റർ ചെയ്യൂ (Register for more free classes)');
                }
                return redirect()->route('login')->with('info', 'Please sign in to access this lesson.');
            }

            if (!$user->isPaid()) {
                return redirect()->route('pricing')->with('warning', 'This interactive lesson is exclusive to Premium Members. Upgrade your plan to unlock instant access!');
            }

            abort(403, 'You do not have access to this lesson.');
        }

        $trackId = request()->query('track_id');
        $currentTrack = $trackId ? \App\Models\LearningTrack::find($trackId) : null;

        // Remember last attended unit for both logged-in students and guests (30 days)
        session(['last_attended_unit_id' => $unit->id]);
        Cookie::queue(cookie('amter_last_unit_id', (string) $unit->id, 60 * 24 * 30));

        return view('student.units.show', [
            'unit' => $unit,
            'currentTrack' => $currentTrack,
            'libraryId' => 569307,
        ]);
    }

    public function complete(Unit $unit)
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('register')->with('info', 'നിങ്ങളുടെ പുരോഗതി രേഖപ്പെടുത്താനും കൂടുതൽ ഫ്രീ ക്ലാസ്സുകൾ കാണാനും സൗജന്യമായി റജിസ്റ്റർ ചെയ്യൂ!');
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
        if (!$nextUnit) {
            $nextUnit = $unit->nextUnit();
        }

        if ($nextUnit) {
            session(['last_attended_unit_id' => $nextUnit->id]);
            Cookie::queue(cookie('amter_last_unit_id', (string) $nextUnit->id, 60 * 24 * 30));

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
            ->with('xp_gained', 500);
    }
}
