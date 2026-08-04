<?php

namespace App\Filament\Student\Resources\Units\Pages;

use App\Filament\Student\Resources\Units\UnitResource;
use Filament\Resources\Pages\ListRecords;

class ListUnits extends ListRecords
{
    protected static string $resource = UnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
