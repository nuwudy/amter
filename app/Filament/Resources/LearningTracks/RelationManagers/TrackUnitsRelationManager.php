<?php

namespace App\Filament\Resources\LearningTracks\RelationManagers;

use App\Models\CourseSession;
use App\Models\Unit;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class TrackUnitsRelationManager extends RelationManager
{
    protected static string $relationship = 'trackUnits';

    protected static ?string $title = 'Curated Steps (Continuous Order)';
    protected static ?string $modelLabel = 'Step';
    protected static ?string $pluralModelLabel = 'Steps';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('step_number')
                    ->label('Step #')
                    ->numeric()
                    ->required(),

                TextInput::make('title_override')
                    ->label('Custom Step Title (Optional)')
                    ->placeholder('Leave blank to use unit title'),

                TextInput::make('notes')
                    ->label('Step Guidance / Notes (Optional)')
                    ->placeholder('e.g., Focus on voice clarity'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('step_number', 'asc')
            ->reorderable('step_number')
            ->columns([
                Tables\Columns\TextColumn::make('step_number')
                    ->label('Step')
                    ->sortable()
                    ->weight('black')
                    ->formatStateUsing(fn ($state) => "Step {$state}"),

                Tables\Columns\ImageColumn::make('unit.thumbnail')
                    ->label('Preview')
                    ->disk('public')
                    ->rounded()
                    ->size(50),

                Tables\Columns\TextColumn::make('unit.title')
                    ->label('Lesson Title')
                    ->searchable()
                    ->weight('semibold')
                    ->description(fn ($record) => $record->title_override ? "Custom: {$record->title_override}" : null),

                Tables\Columns\TextColumn::make('unit.courseSession.title')
                    ->label('Source Title')
                    ->badge()
                    ->color('gray')
                    ->searchable(),

                Tables\Columns\TextColumn::make('notes')
                    ->label('Notes')
                    ->placeholder('—')
                    ->limit(30),
            ])
            ->headerActions([
                // 1. Bulk Add Range from Title
                Action::make('bulk_add_range')
                    ->label('Bulk Add from Title')
                    ->icon('heroicon-o-queue-list')
                    ->color('primary')
                    ->modalHeading('Bulk Add Unit Range from Title')
                    ->modalDescription('Select a Title and a range of unit numbers to append to this track.')
                    ->form([
                        Select::make('course_session_id')
                            ->label('Source Title')
                            ->options(fn () => CourseSession::orderBy('title')->pluck('title', 'id'))
                            ->searchable()
                            ->required()
                            ->helperText('Select which Title card to pull units from.'),

                        TextInput::make('from_order')
                            ->label('From Unit #')
                            ->numeric()
                            ->default(1)
                            ->required(),

                        TextInput::make('to_order')
                            ->label('To Unit #')
                            ->numeric()
                            ->default(5)
                            ->required(),

                        TextInput::make('notes')
                            ->label('Optional Note for these Steps')
                            ->placeholder('e.g., Core Conversation Basics'),
                    ])
                    ->action(function (array $data) {
                        $track = $this->getOwnerRecord();
                        $added = $track->appendRangeFromSession(
                            (int) $data['course_session_id'],
                            (int) $data['from_order'],
                            (int) $data['to_order'],
                            $data['notes'] ?? null
                        );

                        Notification::make()
                            ->title("Successfully added {$added} steps to track!")
                            ->success()
                            ->send();
                    }),

                // 2. Pick Specific Units
                Action::make('pick_units')
                    ->label('Pick Specific Units')
                    ->icon('heroicon-o-plus-circle')
                    ->color('gray')
                    ->modalHeading('Add Specific Units')
                    ->form([
                        Select::make('course_session_id')
                            ->label('Filter by Title')
                            ->options(fn () => CourseSession::orderBy('title')->pluck('title', 'id'))
                            ->searchable()
                            ->live(),

                        Select::make('unit_ids')
                            ->label('Select Units')
                            ->multiple()
                            ->searchable()
                            ->required()
                            ->options(fn ($get) => Unit::when(
                                $get('course_session_id'),
                                fn ($q, $id) => $q->where('course_session_id', $id)
                            )->orderBy('sort_order')->pluck('title', 'id')),

                        TextInput::make('notes')
                            ->label('Optional Note')
                            ->placeholder('e.g., Extra practice'),
                    ])
                    ->action(function (array $data) {
                        $track = $this->getOwnerRecord();
                        $added = $track->appendUnits($data['unit_ids'], $data['notes'] ?? null);

                        Notification::make()
                            ->title("Successfully added {$added} steps to track!")
                            ->success()
                            ->send();
                    }),

                // 3. Renumber Steps Cleanly
                Action::make('renumber_steps')
                    ->label('Renumber Cleanly')
                    ->icon('heroicon-o-arrows-up-down')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading('Renumber All Steps')
                    ->modalDescription('This will clean up any number gaps and index all steps strictly as 1, 2, 3...')
                    ->action(function () {
                        $this->getOwnerRecord()->renumberSteps();
                        Notification::make()
                            ->title('All steps renumbered cleanly!')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Action::make('preview')
                    ->label('Play')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->url(fn ($record) => route('student.units.show', ['unit' => $record->unit_id, 'track_id' => $record->learning_track_id]))
                    ->openUrlInNewTab(),
                EditAction::make(),
                DeleteAction::make()
                    ->after(fn () => $this->getOwnerRecord()->renumberSteps()),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->after(fn () => $this->getOwnerRecord()->renumberSteps()),
                ]),
            ]);
    }
}
