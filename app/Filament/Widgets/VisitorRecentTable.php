<?php

namespace App\Filament\Widgets;

use App\Models\Visit;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class VisitorRecentTable extends BaseWidget
{
    protected static ?int $sort = -1;
    protected static ?string $heading = 'Recent Website Visits';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Visit::query()->latest()->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Time')
                    ->dateTime('M d, H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP'),
                Tables\Columns\TextColumn::make('location')
                    ->label('Location')
                    ->getStateUsing(fn ($record) => $record->region && $record->region !== 'Unknown' 
                        ? $record->region . ', ' . $record->country 
                        : $record->country),
                Tables\Columns\TextColumn::make('url')
                    ->label('Landing Page')
                    ->getStateUsing(fn ($record) => parse_url($record->url, PHP_URL_PATH) ?: '/')
                    ->limit(25),
                Tables\Columns\TextColumn::make('referer')
                    ->label('Referer / Traffic Source')
                    ->getStateUsing(fn ($record) => $record->referer 
                        ? parse_url($record->referer, PHP_URL_HOST) ?? $record->referer 
                        : null)
                    ->placeholder('Direct / Organic')
                    ->limit(25),
            ]);
    }
}
