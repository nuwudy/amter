<?php

namespace App\Filament\Student\Resources\Units\Pages;

use App\Filament\Student\Resources\Units\UnitResource;
use App\Models\Unit;
use Filament\Resources\Pages\ViewRecord;

class ViewUnit extends ViewRecord
{
    protected static string $resource = UnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No student preview button needed here since we are ALREADY in the student panel
        ];
    }

    public function mount(int | string $record): void
    {
        parent::mount($record);

        if (! $this->record->isAccessibleBy(auth()->user())) {
            redirect()->route('pricing')->send();
        }
    }

    public function getBreadcrumbs(): array
    {
        return [
            '/student' => 'Dashboard',
            UnitResource::getUrl('index') => 'Course Content',
            '#' => $this->record->title,
        ];
    }

    public function infolist(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return UnitResource::infolist($schema->record($this->record));
    }

    public function markAsComplete()
    {
        $user = auth()->user();
        
        // 1. Save Progress
        $user->completedUnits()->syncWithoutDetaching([$this->record->id]);

        // 1.5 Check for Milestones
        $milestoneService = new \App\Services\MilestoneService();
        $milestoneService->checkMilestones($user, $this->record);

        // 2. Find the Next Unit (same module, next ID)
        $nextUnit = Unit::whereHas('courseSession', function ($query) {
                $query->where('module_id', $this->record->courseSession->module_id);
            })
            ->where('id', '>', $this->record->id)
            ->where('is_published', true)
            ->orderBy('id', 'asc')
            ->first();

        if ($nextUnit) {
            \Filament\Notifications\Notification::make()
                ->title('Mastered!')
                ->body('Moving to: ' . $nextUnit->title)
                ->success()
                ->send();

            // Redirect to the next unit's view page
            return redirect(route('student.units.show', ['unit' => $nextUnit->id]));
        }

        // 3. If no next unit, go back to student dashboard
        \Filament\Notifications\Notification::make()
            ->title('Module Completed!')
            ->body('You have finished all clips in this section.')
            ->success()
            ->send();

        return redirect('/student');
    }
}
