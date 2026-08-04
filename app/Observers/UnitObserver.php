<?php

namespace App\Observers;

use App\Models\Unit;
use Filament\Notifications\Notification;

class UnitObserver
{
    /**
     * Handle the Unit "updated" event.
     */
    public function updated(Unit $unit): void
    {
        if ($unit->wasChanged('is_published') && $unit->is_published) {
            // Use Filament's database notifications to alert all enrolled students
            $students = $unit->courseSession->module->course->enrolledUsers ?? collect(); 
            // Note: Relationship path: Unit -> CourseSession -> Module -> Course -> enrolledUsers
            // Assuming this chain exists. Let's verify relationships or simplify if possible.
            // User provided: $unit->course->enrolledUsers. 
            // My models: Unit belongsTo CourseSession.
            // Let's implement robustly. If $unit->course exists (via getter or relation), good.
            // I'll stick to what the user provided pattern implies: $unit->course
            // But wait, Unit belongs to CourseSession.
            
            // Let's check if Unit has a 'course' relationship or if we need to traverse.
            // Based on previous files, Unit belongsTo CourseSession, CourseSession belongsTo Module, Module belongsTo Course.
            // I will assume for now we need to traverse:
            
            $course = $unit->courseSession->module->course;
            
            if ($course) {
                 Notification::make()
                    ->title('New Native Clip Uploaded!')
                    ->body("A new lesson is ready: {$unit->title}")
                    ->sendToDatabase($course->enrolledUsers);
            }
        }
    }
}
