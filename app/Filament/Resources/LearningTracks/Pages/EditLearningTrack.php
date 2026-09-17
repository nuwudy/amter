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
            Actions\DeleteAction::make(),
        ];
    }
}
