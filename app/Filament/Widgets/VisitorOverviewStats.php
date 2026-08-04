<?php

namespace App\Filament\Widgets;

use App\Models\Visit;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class VisitorOverviewStats extends BaseWidget
{
    protected static ?int $sort = -3; // Show near the top

    protected function getStats(): array
    {
        // Fail-safe if database tables aren't migrated yet
        try {
            $totalVisits = Visit::count();
            $uniqueVisitors = Visit::distinct('ip_address')->count('ip_address');
            $visitsToday = Visit::whereDate('created_at', Carbon::today())->count();

            // Sparkline data for last 7 days
            $visitsLast7Days = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $visitsLast7Days[] = Visit::whereDate('created_at', $date)->count();
            }
        } catch (\Exception $e) {
            return [];
        }

        return [
            Stat::make('Total Page Views', number_format($totalVisits))
                ->description('All public page hits')
                ->descriptionIcon('heroicon-m-eye')
                ->color('success')
                ->chart($visitsLast7Days),
            Stat::make('Unique Visitors', number_format($uniqueVisitors))
                ->description('Unique IP addresses')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
            Stat::make('Visits Today', number_format($visitsToday))
                ->description('Hits in the last 24 hours')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('warning'),
        ];
    }
}
