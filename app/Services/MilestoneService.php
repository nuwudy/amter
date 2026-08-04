<?php

namespace App\Services;

use App\Models\User;
use App\Models\Unit;
use App\Models\Milestone;
use App\Models\CourseSession;
use Filament\Notifications\Notification;

class MilestoneService
{
    public function checkMilestones(User $user, Unit $completedUnit)
    {
        $this->checkUnitCountMilestone($user);
        $this->checkTitleCompletionMilestone($user, $completedUnit);
        $this->checkRetroactiveMilestones($user);
        // $this->checkModuleCompletionMilestone($user, $completedUnit); // Future Implementation
    }

    protected function checkUnitCountMilestone(User $user)
    {
        $completedCount = $user->completedUnits()->count();

        if ($completedCount > 0 && $completedCount % 10 === 0) {
            $this->awardMilestone(
                $user,
                '10_units',
                null,
                "Decade Dedication: {$completedCount} Units!",
                "You have mastered {$completedCount} native clips. Outstanding consistency!",
                'heroicon-o-fire'
            );
        }
    }

    protected function checkTitleCompletionMilestone(User $user, Unit $unit)
    {
        $courseSession = $unit->courseSession;
        if (!$courseSession) return;

        // Count total units in this session
        $totalUnits = $courseSession->units()->where('is_published', true)->count();
        
        // Count completed units in this session for this user
        $completedFromSession = $user->completedUnits()
            ->where('course_session_id', $courseSession->id)
            ->count();

        if ($totalUnits > 0 && $completedFromSession >= $totalUnits) {
            $this->awardMilestone(
                $user,
                'title_complete',
                $courseSession->id,
                "Title Master: {$courseSession->title}",
                "You have mastered every lesson in '{$courseSession->title}'. Keep taking your life to the next level!",
                'heroicon-o-academic-cap'
            );
        }
    }

    protected function awardMilestone(User $user, string $type, ?int $relatedId, string $title, string $message, string $icon)
    {
        // Prevent duplicate awards for the same achievement
        $exists = Milestone::where('user_id', $user->id)
            ->where('achievement_type', $type)
            ->where(function($q) use ($relatedId, $type) {
                if ($type === '10_units') {
                    // For 10_units, we allow multiple, but let's check if we already awarded for THIS count? 
                    // To keep it simple, we allow multiple '10_units' types generally, 
                    // but we should probably store the specific count in related_entity_id or title to track uniqueness.
                    // For now, let's just create it. 
                    // Refinement: Ideally we check if a milestone with this specific title exists.
                } else {
                    $q->where('related_entity_id', $relatedId);
                }
            })
            ->when($type === '10_units', function($q) use ($title) {
                 return $q->where('title', $title);
            })
            ->exists();

        if (!$exists) {
            Milestone::create([
                'user_id' => $user->id,
                'achievement_type' => $type,
                'related_entity_id' => $relatedId,
                'title' => $title,
                'message' => $message,
                'icon' => $icon,
            ]);

            Notification::make()
                ->title('New Milestone Unlocked!')
                ->body($title)
                ->success()
                ->duration(10000)
                ->sendToDatabase($user);

            // Flash to session for Confetti UI
            session()->flash('milestone_awarded', [
                'title' => $title,
                'message' => $message,
                'icon' => $icon
            ]);
        }
    }

    public function checkRetroactiveMilestones(User $user)
    {
        // 1. Check 10 Units (One-time check for now, can be expanded)
        $count = $user->completedUnits()->count();
        if ($count >= 10) {
            $exists = Milestone::where('user_id', $user->id)
                ->where('achievement_type', '10_units')
                ->exists();
            
            if (!$exists) {
                // Award it retroactively
                $this->awardMilestone(
                    $user,
                    '10_units',
                    null,
                    "Decade Dedication: {$count} Units!",
                    "You have mastered {$count} native clips. Outstanding consistency!",
                    'heroicon-o-fire'
                );
            }
        }
    }
}
