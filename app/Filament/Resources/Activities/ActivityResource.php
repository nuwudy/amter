<?php

namespace App\Filament\Resources\Activities;

use App\Filament\Resources\Activities\Pages;
use App\Models\Activity;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Illuminate\Support\HtmlString;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationLabel = 'Activities';

    protected static ?string $pluralModelLabel = 'Activities';

    protected static ?string $modelLabel = 'Activity';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // 1. Link to Clip
                Select::make('unit_id')
                    ->relationship('unit', 'title')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('Belongs to Clip (Unit)'),

                // 2. Type of Activity
                Select::make('type')
                    ->options([
                        'video_clip' => 'Video Clip (Bunny.net)',
                        'vocab_card' => 'Vocabulary Card',
                        'speaking_drill' => 'Speaking Drill',
                        'quiz' => 'Quiz / Puzzle',
                    ])
                    ->required()
                    ->default('video_clip'),

                // 3. The Content URL
                Textarea::make('content')
                    ->label('Content / URL')
                    ->helperText('Paste Bunny.net URL here')
                    ->required()
                    ->columnSpanFull(),

                // 4. Sort Order
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(1),

                // 5. Video Preview
                Placeholder::make('video_preview')
                    ->label('Video Preview')
                    ->content(new HtmlString('
                        <div style="position:relative;padding-top:56.25%;">
                            <iframe src="https://iframe.mediadelivery.net/embed/569307/7050e546-b547-46fb-b0fe-44ae705e43ab?autoplay=false&loop=false&muted=false" 
                            loading="lazy" 
                            style="border:0;position:absolute;top:0;height:100%;width:100%;" 
                            allow="accelerometer;gyroscope;autoplay;encrypted-media;picture-in-picture;" 
                            allowfullscreen="true"></iframe>
                        </div>
                    '))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('unit.title')
                    ->label('Clip Name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageActivities::route('/'),
        ];
    }
}