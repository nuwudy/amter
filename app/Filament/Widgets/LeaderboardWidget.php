<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LeaderboardWidget extends BaseWidget
{
    protected static ?string $heading = 'Top Learners (English Champions)';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'half';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->where('is_public_on_leaderboard', true)
                    ->withCount('completedUnits')
                    ->orderBy('completed_units_count', 'desc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('rank')
                    ->label('#')
                    ->state(static function (Tables\Contracts\HasTable $livewire, $record) {
                        return array_search($record->id, $livewire->getTableRecords()->pluck('id')->toArray()) + 1;
                    }),
                Tables\Columns\ImageColumn::make('avatar_url')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name)),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('completed_units_count')
                    ->label('Clips Mastered')
                    ->badge()
                    ->color('success'),
            ]);
    }
}
