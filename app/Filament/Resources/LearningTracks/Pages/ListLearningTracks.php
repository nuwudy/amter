<?php

namespace App\Filament\Resources\LearningTracks\Pages;

use App\Filament\Resources\LearningTracks\LearningTrackResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLearningTracks extends ListRecords
{
    protected static string $resource = LearningTrackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('New Master Track'),
        ];
    }
}
