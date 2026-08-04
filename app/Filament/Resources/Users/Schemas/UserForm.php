<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                \Filament\Forms\Components\Placeholder::make('membership_status')
                    ->label('Current Membership')
                    ->content(fn (?User $record): string => $record?->membership_type ?? 'New User')
                    ->extraAttributes(['class' => 'font-bold text-primary-600']),
                DateTimePicker::make('email_verified_at'),
                \Filament\Forms\Components\Select::make('role')
                    ->options([
                        'student' => 'Student',
                        'admin' => 'Admin',
                    ])
                    ->default('student')
                    ->required()
                    ->helperText('Students can be either Registered (Free) or Paid (Premium) depending on their expiry date.'),
                DateTimePicker::make('subscription_expires_at')
                    ->label('Subscription Access Until')
                    ->helperText('Leave empty for "Registered Only" access. Set a future date for "Paid" access.'),
                \Filament\Forms\Components\Toggle::make('is_suspended')
                    ->label('Suspend User')
                    ->onColor('danger')
                    ->offColor('success'),
                \Filament\Forms\Components\Toggle::make('is_public_on_leaderboard')
                    ->label('Show on Leaderboard')
                    ->default(true),
                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->required(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord)
                    ->dehydrated(fn ($state) => filled($state)),
            ]);
    }
}
