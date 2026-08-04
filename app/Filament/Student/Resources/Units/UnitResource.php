<?php

namespace App\Filament\Student\Resources\Units;

use App\Filament\Student\Resources\Units\Pages;
use App\Models\Unit;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Actions\Action;
use Filament\Infolists\Infolist;
use Filament\Infolists;

class UnitResource extends Resource
{
    protected static ?string $model = Unit::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Grid::make([
                    'default' => 1,
                    'lg' => 4,
                ])
                    ->schema([
                        // Main Lesson Content (3/4 of screen)
                        \Filament\Schemas\Components\Group::make([
                            \Filament\Schemas\Components\Section::make()
                                ->schema([
                                    ViewEntry::make('content_blocks')
                                        ->view('filament.components.unit-renderer-student')
                                        ->viewData(fn ($record) => [
                                            'blocks' => $record->content_blocks,
                                            'unit' => $record,
                                        ])
                                        ->hiddenLabel(),
                                ]),
                        ])->columnSpan(3),

                        // Sidebar Navigation (1/4 of screen)
                        \Filament\Schemas\Components\Group::make([
                            \Filament\Schemas\Components\Section::make('Course Content')
                                ->schema([
                                    ViewEntry::make('navigation')
                                        ->view('filament.components.course-sidebar')
                                        ->hiddenLabel(),
                                ]),
                        ])->columnSpan(1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order', 'asc')
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    // The Thumbnail Image
                    Tables\Columns\ImageColumn::make('thumbnail')
                        ->height('200px')
                        ->width('100%')
                        ->extraAttributes(['class' => 'object-cover rounded-t-xl']),

                    Tables\Columns\Layout\Stack::make([
                        // The Lesson Title
                        Tables\Columns\TextColumn::make('title')
                            ->weight('bold')
                            ->size('lg'),

                        // The Module Name (Personality/Track)
                        Tables\Columns\TextColumn::make('module.title')
                            ->color('gray')
                            ->size('sm'),
                    ])->space(1)->extraAttributes(['class' => 'p-4']),
                ]),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make()
                    ->label(fn (Unit $record) => $record->isAccessibleBy(auth()->user()) ? 'Start Lesson' : 'Locked')
                    ->icon(fn (Unit $record) => $record->isAccessibleBy(auth()->user()) ? 'heroicon-m-play' : 'heroicon-m-lock-closed')
                    ->button()
                    ->color(fn (Unit $record) => $record->isAccessibleBy(auth()->user()) ? 'primary' : 'gray')
                    ->url(fn (Unit $record) => $record->isAccessibleBy(auth()->user()) 
                        ? route('student.units.show', ['unit' => $record->id]) 
                        : route('pricing')
                    ),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUnits::route('/'),
            'view' => Pages\ViewUnit::route('/{record}'),
        ];
    }

    // Helper Logic for finding adjacent units
    protected static function getAdjacentUnit($record, $direction)
    {
        $query = \App\Models\Unit::where('course_session_id', $record->course_session_id)
            ->where('is_published', true);

        if ($direction === 'next') {
            return $query->where('sort_order', '>', $record->sort_order)->orderBy('sort_order', 'asc')->first();
        }
        
        return $query->where('sort_order', '<', $record->sort_order)->orderBy('sort_order', 'desc')->first();
    }

    protected static function getAdjacentUnitUrl($record, $direction)
    {
        $unit = self::getAdjacentUnit($record, $direction);
        return $unit ? route('student.units.show', ['unit' => $unit->id]) : null;
    }
}
