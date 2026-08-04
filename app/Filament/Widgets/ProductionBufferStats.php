<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class ProductionBufferStats extends BaseWidget
{
    protected function getStats(): array
    {
        $lastPublishedUnitId = Unit::where('is_published', true)->max('id') ?? 0;
        $leadStudentUnitId = DB::table('completed_units')->max('unit_id') ?? 0;
        
        $buffer = $lastPublishedUnitId - $leadStudentUnitId;

        return [
            Stat::make('Production Buffer', $buffer . ' Units')
                ->description($buffer <= 2 ? 'Filming Required ASAP!' : 'Safe for now')
                ->descriptionIcon($buffer <= 2 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($buffer <= 2 ? 'danger' : 'success'),
        ];
    }
}
