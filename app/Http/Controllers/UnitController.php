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
            if (request()->expectsJson()) {
                return response()->json([
                    'completed' => false,
                    'auth_required' => true,
                    'message' => 'Please sign in to save your progress.',
                ], 401);
            }
            return redirect()->route('register')->with('info', 'നിങ്ങളുടെ പുരോഗതി രേഖപ്പെടുത്താനും കൂടുതൽ ഫ്രീ ക്ലാസ്സുകൾ കാണാനും സൗജന്യമായി റജിസ്റ്റർ ചെയ്യൂ!');
        }

        $groupUnitIds = collect(explode(',', (string) request()->input('group_unit_ids', '')))
            ->map(fn ($id) => (int) trim($id))
            ->filter(fn ($id) => $id > 0)
            ->push((int) $unit->id)
            ->unique()
            ->values();

        $milestoneService = new \App\Services\MilestoneService();
        $unitsToComplete = Unit::whereIn('id', $groupUnitIds)->get();
        $newlyCompleted = false;

        foreach ($unitsToComplete as $targetUnit) {
            if (!$user->completedUnits()->where('unit_id', $targetUnit->id)->exists()) {
                $user->completedUnits()->attach($targetUnit->id, ['completed_at' => now()]);
                $milestoneService->checkMilestones($user, $targetUnit);
                $newlyCompleted = true;
            }
        }

        $trackId = request()->input('track_id') ?: request()->query('track_id');
        $currentTrack = $trackId ? \App\Models\LearningTrack::find((int) $trackId) : null;

        $groupData = app(\App\Services\LessonGroupResolver::class)->resolveForUnit($unit, $currentTrack);
        $nextUnit = $groupData['next_group_unit'] ?? null;

        // Fallback: advance past every unit already in this group
        if (!$nextUnit || $groupUnitIds->contains((int) $nextUnit->id)) {
            $cursor = $unit;
            $visited = [];
            $nextUnit = null;

            for ($i = 0; $i < 50 && $cursor; $i++) {
                $visited[$cursor->id] = true;
                $candidate = $trackId
                    ? $cursor->nextTrackUnit((int) $trackId)
                    : null;
                if (!$candidate) {
                    $candidate = $cursor->nextUnit();
                }
                if (!$candidate || isset($visited[$candidate->id])) {
                    break;
                }
                if (!$groupUnitIds->contains((int) $candidate->id)) {
                    $nextUnit = $candidate;
                    break;
                }
                $cursor = $candidate;
            }
        }

        if ($nextUnit) {
            session(['last_attended_unit_id' => $nextUnit->id]);
            Cookie::queue(cookie('amter_last_unit_id', (string) $nextUnit->id, 60 * 24 * 30));

            $redirectParams = ['unit' => $nextUnit];
            if ($trackId) {
                $redirectParams['track_id'] = $trackId;
            }

            if (request()->expectsJson()) {
                return response()->json([
                    'completed' => true,
                    'newly_completed' => $newlyCompleted,
                    'xp_gained' => $newlyCompleted ? 100 : 0,
                    'next_unit_id' => $nextUnit->id,
                    'next_unit_url' => route('student.units.show', $redirectParams),
                ]);
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

        if (request()->expectsJson()) {
            return response()->json([
                'completed' => true,
                'newly_completed' => $newlyCompleted,
                'course_completed' => true,
                'xp_gained' => $newlyCompleted ? 500 : 0,
                'next_unit_id' => null,
                'next_unit_url' => null,
            ]);
        }

        return redirect()->route('student.units.show', $redirectParams)
            ->with('success', $trackId ? 'Track Completed! Incredible job!' : 'Course Completed! Great job!')
            ->with('course_completed', true)
            ->with('xp_gained', 500);
    }
}
