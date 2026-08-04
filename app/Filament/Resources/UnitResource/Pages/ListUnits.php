<?php

namespace App\Filament\Resources\UnitResource\Pages;

use App\Filament\Resources\UnitResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Livewire\Attributes\Url;

class ListUnits extends ListRecords
{
    protected static string $resource = UnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->url(function () {
                    $sessionId = $this->tableFilters['course_session_id']['value'] ?? request()->query('session_id');
                    return $sessionId 
                        ? static::getResource()::getUrl('create') . '?session_id=' . $sessionId 
                        : static::getResource()::getUrl('create');
                }),
        ];
    }
}
