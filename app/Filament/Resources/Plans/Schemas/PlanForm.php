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
                    ->required()
                    ->numeric(),
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
