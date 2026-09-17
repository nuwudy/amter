<?php

namespace App\Filament\Resources\LearningTracks;

use App\Filament\Resources\LearningTracks\Pages;
use App\Filament\Resources\LearningTracks\RelationManagers\TrackUnitsRelationManager;
use App\Models\LearningTrack;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class LearningTrackResource extends Resource
{
    protected static ?string $model = LearningTrack::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-map';
    protected static ?string $navigationLabel = 'Master Tracks';
    protected static ?string $modelLabel = 'Master Track';
    protected static ?string $pluralModelLabel = 'Master Tracks';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Track Details')
                    ->description('Set up the overarching continuous course name and visibility.')
                    ->schema([
                        TextInput::make('title')
                            ->label('Track Title')
                            ->placeholder('e.g., Amter Spoken Master Path')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),

                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255),

                        Textarea::make('description')
                            ->label('Description / Learning Outcomes')
                            ->placeholder('Describe the learning journey for students...')
                            ->rows(3)
                            ->columnSpanFull(),

                        FileUpload::make('thumbnail_path')
                            ->label('Cover Image (Optional)')
                            ->image()
                            ->directory('track-thumbnails')
                            ->disk('public')
                            ->columnSpanFull(),

                        Toggle::make('is_active')
                            ->label('Active / Visible to Students')
                            ->helperText('Keep OFF while adding units; turn ON when you are ready to present this path to students!')
                            ->default(false),

                        Toggle::make('is_default')
                            ->label('Primary Master Path')
                            ->helperText('If enabled, this track will be used as the default continuous learning path.')
                            ->default(false),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order', 'asc')
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail_path')
                    ->label('Cover')
                    ->disk('public')
                    ->rounded()
                    ->size(60),

                Tables\Columns\TextColumn::make('title')
                    ->label('Track Name')
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\TextColumn::make('track_units_count')
                    ->label('Total Steps')
                    ->counts('trackUnits')
                    ->badge()
                    ->color('primary')
                    ->formatStateUsing(fn ($state) => "{$state} Steps"),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_default')
                    ->label('Primary')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->date()
                    ->color('gray'),
            ])
            ->actions([
                Action::make('preview')
                    ->label('Play ▶')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->url(function ($record) {
                        $firstStep = $record->trackUnits()->orderBy('step_number', 'asc')->first();
                        return $firstStep ? route('student.units.show', ['unit' => $firstStep->unit_id, 'track_id' => $record->id]) : '#';
                    })
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => $record->trackUnits()->exists()),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            TrackUnitsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLearningTracks::route('/'),
            'create' => Pages\CreateLearningTrack::route('/create'),
            'edit' => Pages\EditLearningTrack::route('/{record}/edit'),
        ];
    }
}
