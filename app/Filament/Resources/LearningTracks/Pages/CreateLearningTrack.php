<?php

namespace App\Filament\Resources\LearningTracks\Pages;

use App\Filament\Resources\LearningTracks\LearningTrackResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLearningTrack extends CreateRecord
{
    protected static string $resource = LearningTrackResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
