<?php

namespace App\Filament\Resources\UnitResource\Pages;

use App\Filament\Resources\UnitResource;
use App\Models\Unit;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewUnit extends ViewRecord
{
    protected static string $resource = UnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Fixes the Edit button
            Actions\EditAction::make(),

            Actions\Action::make('insert_after')
                ->label('Insert Next Unit')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->url(fn () => UnitResource::getUrl('create') . '?session_id=' . $this->record->course_session_id . '&insert_order=' . ((int) $this->record->sort_order + 1)),

            // Add New Unit Button
            Actions\CreateAction::make()
                ->label('Add New Unit')
                ->icon('heroicon-o-plus')
                ->url(fn () => UnitResource::getUrl('create') . '?session_id=' . $this->record->course_session_id),

        ];
    }

    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [
            '/admin' => 'Dashboard',
            UnitResource::getUrl('index') => 'All Courses',
        ];

        if ($this->record && $this->record->courseSession) {
            $sessionUrl = UnitResource::getUrl('index') . '?tableFilters[course_session_id][value]=' . $this->record->course_session_id;
            $breadcrumbs[$sessionUrl] = $this->record->courseSession->title;
        }

        $breadcrumbs['#'] = $this->record->title ?? 'View';

        return $breadcrumbs;
    }

    public function markAsComplete()
    {
        $user = auth()->user();
        
        // 1. Save Progress
        $user->completedUnits()->syncWithoutDetaching([$this->record->id]);

        // 2. Find the Next Unit by sort_order
        $nextUnit = $this->record->nextUnit();

        if ($nextUnit) {
            \Filament\Notifications\Notification::make()
                ->title('Mastered!')
                ->body('Moving to: ' . $nextUnit->title)
                ->success()
                ->send();

            // Redirect to the next unit's view page
            return redirect(UnitResource::getUrl('view', ['record' => $nextUnit->id]));
        }

        // 3. If no next unit, go back to dashboard
        \Filament\Notifications\Notification::make()
            ->title('Module Completed!')
            ->body('You have finished all clips in this section.')
            ->success()
            ->send();

        return redirect('/admin');
    }
}
