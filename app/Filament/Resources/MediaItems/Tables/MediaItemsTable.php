<?php

namespace App\Filament\Resources\MediaItems\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MediaItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                \Filament\Tables\Columns\ImageColumn::make('path')
                    ->label('Preview')
                    ->square()
                    ->width(80)
                    ->height(80)
                    ->disk('public')
                    ->checkFileExistence(false)
                    ->getStateUsing(fn ($record) => ($record && $record->type === 'image') ? asset('storage/' . $record->path) : null),
                \Filament\Tables\Columns\IconColumn::make('type_icon')
                    ->label('Type')
                    ->icon(fn (string $state): string => match ($state) {
                        'video' => 'heroicon-o-film',
                        'audio' => 'heroicon-o-musical-note',
                        default => 'heroicon-o-document',
                    })
                    ->getStateUsing(fn ($record) => ($record && $record->type !== 'image') ? $record->type : null)
                    ->color('gray'),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('audio_player')
                    ->label('Listen')
                    ->html()
                    ->getStateUsing(function ($record) {
                        if ($record && $record->type === 'audio') {
                            return '<audio controls preload="none" style="height: 35px; width: 200px;" src="'.url('storage/' . $record->path).'"></audio>';
                        }
                        return '';
                    }),
                TextColumn::make('type')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'image' => 'success',
                        'video' => 'warning',
                        'audio' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('url_display')
                    ->label('Copy URL')
                    ->getStateUsing(fn ($record) => $record ? url('storage/' . $record->path) : null)
                    ->copyable()
                    ->copyableState(fn ($state) => $state)
                    ->copyMessage('URL copied')
                    ->limit(30)
                    ->tooltip(fn ($state) => $state),
                TextColumn::make('mime_type')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
