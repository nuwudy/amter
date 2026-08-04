<?php

namespace App\Filament\Student\Pages;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;

class Profile extends BaseEditProfile
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user';

    protected static ?string $slug = 'profile';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                
                Section::make('Subscription Details')
                    ->schema([
                        Placeholder::make('plan_name')
                            ->label('Current Plan')
                            ->content(fn () => auth()->user()->refresh()->plan?->name ?? 'No Active Plan'),
                            
                        Placeholder::make('subscription_expires_at')
                            ->label('Subscription Expires At')
                            ->content(fn () => auth()->user()->subscription_expires_at?->format('d-m-Y') ?? 'N/A'),
                    ])
                    ->columns(2),
            ]);
    }
    public function getCancelFormAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('cancel')
            ->label('Back to Dashboard')
            ->url(route('filament.student.pages.dashboard'))
            ->color('gray');
    }
}
