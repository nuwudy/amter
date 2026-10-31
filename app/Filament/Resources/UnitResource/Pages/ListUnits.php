<?php

namespace App\Filament\Resources\UnitResource\Pages;

use App\Filament\Resources\UnitResource;
use App\Models\Unit;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

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

    public function reorderTable(array $order, int | string | null $draggedRecordKey = null): void
    {
        parent::reorderTable($order, $draggedRecordKey);

        $sessionIds = Unit::whereIn('id', $order)
            ->pluck('course_session_id')
            ->filter()
            ->unique();

        foreach ($sessionIds as $sessionId) {
            Unit::resequenceSession((int) $sessionId);
        }
    }
}
