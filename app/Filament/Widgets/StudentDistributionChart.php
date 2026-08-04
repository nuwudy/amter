<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class StudentDistributionChart extends ChartWidget
{
    public function getHeading(): ?string
    {
        return 'Where are the Students?';
    }


    protected function getData(): array
    {
        // Query to find the 'last completed unit' for every student
        // This logic aggregates by unit_id in the completed_units table
        $distribution = DB::table('completed_units')
            ->select('unit_id', DB::raw('count(user_id) as student_count'))
            ->groupBy('unit_id')
            ->orderBy('unit_id', 'asc')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Students at this Stage',
                    'data' => $distribution->pluck('student_count')->toArray(),
                    'backgroundColor' => '#36A2EB',
                ],
            ],
            'labels' => $distribution->pluck('unit_id')->map(fn($id) => "Unit $id")->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
