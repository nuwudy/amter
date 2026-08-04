<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class MasteryStats extends BaseWidget
{
    protected function getStats(): array
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $streak = $user->getMasteryStreak();

        return [
            Stat::make('Current Streak', "{$streak} Days")
                ->description('Keep practicing daily to grow your streak!')
                ->color($streak > 0 ? 'warning' : 'gray')
                ->chart([$streak, $streak + 1, $streak + 2]), // Visual flair

            Stat::make('Total Lessons Mastered', $user->completedUnits()->count())
                ->description('New clips added every week')
                ->color('success'),
        ];
    }
}
