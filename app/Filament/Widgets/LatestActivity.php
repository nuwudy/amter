<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\DB;

class LatestActivity extends TableWidget
{
    // Make widget full width or at least prominent
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Use the User model to start an Eloquent query
                \App\Models\User::query()
                    ->join('completed_units', 'users.id', '=', 'completed_units.user_id')
                    ->join('units', 'units.id', '=', 'completed_units.unit_id')
                    ->select(
                        'completed_units.id',
                        'users.name as user_name', 
                        'units.title as unit_title', 
                        'completed_units.completed_at'
                    )
                    ->latest('completed_units.completed_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('user_name')
                    ->label('Student')
                    ->searchable(),
                Tables\Columns\TextColumn::make('unit_title')
                    ->label('Finished Lesson'),
                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Time')
                    ->dateTime()
                    ->since()
                    ->description(fn ($record) => $record->completed_at),
            ]);
    }
}
