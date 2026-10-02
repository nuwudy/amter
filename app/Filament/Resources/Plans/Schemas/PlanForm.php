<?php

namespace App\Filament\Resources\Plans\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('₹'),
                TextInput::make('duration_days')
                    ->label('Duration (Days)')
                    ->required()
                    ->numeric()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        if ($state && empty($get('price'))) {
                            $set('price', \App\Models\Plan::calculateProgressivePrice((int) $state));
                        }
                    })
                    ->helperText(fn ($state) => $state ? 'Progressive suggestion at ₹10/day: ₹' . \App\Models\Plan::calculateProgressivePrice((int) $state) . ' (' . round(\App\Models\Plan::getDiscountPercentageForDays((int) $state)) . '% volume discount)' : 'Presets: 1 (1 Day), 7 (1 Week), 30 (1 Month), 90 (3 Months), 180 (6 Months)'),
                Toggle::make('is_active')
                    ->required()
                    ->default(true),
                Toggle::make('is_best_value')
                    ->label('Is Best Value')
                    ->required()
                    ->default(false),
            ]);
    }
}
