<?php

namespace App\Filament\Widgets;

use App\Models\Visit;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class VisitorGeoChart extends ChartWidget
{
    protected ?string $heading = 'Top Visitor Regions';
    protected static ?int $sort = -2;

    protected function getData(): array
    {
        try {
            // Group by region & country, count unique IPs to ignore repeated page views by the same person
            $data = Visit::select(
                DB::raw("CONCAT(region, ' (', country, ')') as label"), 
                DB::raw('count(distinct ip_address) as unique_count')
            )
            ->whereNotNull('region')
            ->where('region', '!=', 'Unknown')
            ->where('region', '!=', 'Local Network')
            ->groupBy('region', 'country')
            ->orderBy('unique_count', 'desc')
            ->limit(10)
            ->get();
        } catch (\Exception $e) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Unique Visitors',
                    'data' => $data->pluck('unique_count')->toArray(),
                    'backgroundColor' => '#f59e0b', // Amber/orange
                ],
            ],
            'labels' => $data->pluck('label')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
