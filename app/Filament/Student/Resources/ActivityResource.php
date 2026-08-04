<?php

namespace App\Filament\Student\Resources;

use App\Filament\Student\Resources\ActivityResource\Pages;
use App\Models\Activity;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Actions\ViewAction;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-book-open';
    protected static bool $shouldRegisterNavigation = false;

    // protected static ?string $navigationLabel = 'The Library';

    protected static ?string $pluralModelLabel = 'The Library';

    protected static ?string $modelLabel = 'Library Item';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('unit.course.title')
                    ->label('Course')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit.title')
                    ->label('Lesson')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'video_clip' => 'success',
                        'vocab_card' => 'warning',
                        'speaking_drill' => 'info',
                        'quiz' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Library Item Details')
                    ->schema([
                        TextEntry::make('unit.title')
                            ->label('Lesson'),
                        TextEntry::make('type')
                            ->badge(),
                        ViewEntry::make('content')
                            ->label('Preview')
                            ->view('filament.student.resources.activity.preview')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivities::route('/'),
            'view' => Pages\ViewActivity::route('/{record}'),
        ];
    }
}
