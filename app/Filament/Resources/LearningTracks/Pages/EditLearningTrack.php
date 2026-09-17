<?php

namespace App\Filament\Resources\LearningTracks\Pages;

use App\Filament\Resources\LearningTracks\LearningTrackResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLearningTrack extends EditRecord
{
    protected static string $resource = LearningTrackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview_course')
                ->label('Preview Course ▶')
                ->icon('heroicon-o-play-circle')
                ->color('success')
                ->url(function () {
                    $firstStep = $this->getRecord()->trackUnits()->orderBy('step_number', 'asc')->first();
                    return $firstStep ? route('student.units.show', ['unit' => $firstStep->unit_id, 'track_id' => $this->getRecord()->id]) : '#';
                })
                ->openUrlInNewTab()
                ->visible(fn () => $this->getRecord()->trackUnits()->exists()),

            Actions\DeleteAction::make(),
        ];
    }
}
