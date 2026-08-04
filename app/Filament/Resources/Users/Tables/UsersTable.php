<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Notifications\Notification;
use App\Models\User;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('role')
                    ->badge()
                    ->color(fn ($state) => match (strtolower($state)) {
                        'admin' => 'danger',
                        'student' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('membership_type')
                    ->label('Membership')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'Admin' => 'danger',
                        'Paid' => 'success',
                        'Registered' => 'warning',
                        default => 'gray',
                    }),
                \Filament\Tables\Columns\IconColumn::make('is_suspended')
                    ->boolean()
                    ->label('Suspended')
                    ->trueColor('danger')
                    ->falseColor('success'),
                TextColumn::make('subscription_expires_at')
                    ->label('Sub Expires')
                    ->date()
                    ->sortable()
                    ->color(fn (User $record) => $record->isPaid() ? 'success' : 'gray'),
                TextColumn::make('created_at')
                    ->label('Registered At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'student' => 'Student',
                        'admin' => 'Admin',
                    ]),
                \Filament\Tables\Filters\TernaryFilter::make('is_suspended')
                    ->label('Suspended Users'),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make(),
                EditAction::make(),
                Action::make('grant_subscription')
                    ->label('Grant Sub')
                    ->icon('heroicon-o-gift')
                    ->color('success')
                    ->form([
                        Forms\Components\Select::make('plan_id')
                            ->label('Select Plan')
                            ->options(\App\Models\Plan::where('is_active', true)->pluck('name', 'id'))
                            ->required(),
                    ])
                    ->action(function (User $record, array $data) {
                        $plan = \App\Models\Plan::find($data['plan_id']);
                        
                        // Logic to extend or start new
                        if ($record->subscription_expires_at && $record->subscription_expires_at->isFuture()) {
                             $newExpiry = $record->subscription_expires_at->addDays($plan->duration_days);
                        } else {
                             $newExpiry = now()->addDays($plan->duration_days);
                        }
                        
                        $record->update(['subscription_expires_at' => $newExpiry]);
                        
                        Notification::make()->title("Subscription Granted! Valid until " . $newExpiry->toFormattedDateString())->success()->send();
                    }),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
