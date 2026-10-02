<?php

namespace App\Filament\Resources\Plans\Pages;

use App\Filament\Resources\Plans\PlanResource;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListPlans extends ListRecords
{
    protected static string $resource = PlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('progressivePricing')
                ->label('⚡ Progressive Pricing Strategy')
                ->color('success')
                ->icon(\Filament\Support\Icons\Heroicon::OutlinedBolt)
                ->modalHeading('Progressive Fee Reduction Strategy')
                ->modalDescription('Enter a baseline daily rate (e.g. ₹5, ₹7, or ₹10). The system will automatically calculate the 1 Day, 1 Week, 1 Month, 3 Months, and 6 Months plans with volume-based progressive discounts.')
                ->schema([
                    TextInput::make('base_daily_fee')
                        ->label('Base Daily Fee')
                        ->numeric()
                        ->default(10)
                        ->prefix('₹')
                        ->required()
                        ->helperText('Default is ₹10.00/day. Try ₹5, ₹7, or ₹10 to recalculate all tiers.'),
                    Toggle::make('include_one_year')
                        ->label('Include 1 Year Tier (365 days)?')
                        ->default(false),
                ])
                ->action(function (array $data): void {
                    $base = (float) ($data['base_daily_fee'] ?? 10.0);
                    $includeYear = (bool) ($data['include_one_year'] ?? false);

                    \App\Models\Plan::syncProgressivePlans($base, $includeYear);

                    Notification::make()
                        ->title('Progressive Plans Updated!')
                        ->body("Fee structure updated with Base Daily Rate = ₹{$base}/day.")
                        ->success()
                        ->send();
                }),
            CreateAction::make(),
        ];
    }
}
