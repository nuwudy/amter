<?php

namespace App\Filament\Resources\CourseSessions;

use App\Filament\Resources\CourseSessions\Pages;
use App\Models\CourseSession;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Illuminate\Support\Str;

class CourseSessionResource extends Resource
{
    protected static ?string $model = CourseSession::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationLabel = 'The Library';
    protected static ?string $modelLabel = 'Title';
    protected static ?string $pluralModelLabel = 'Titles';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // 1. Select the Module
                Select::make('module_id')
                    ->relationship('module', 'name')
                    ->required()
                    ->label('Module'),

                // 2. Title (Auto-generates slug)
                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),

                // 3. Slug
                TextInput::make('slug')
                    ->required()
                    ->maxLength(255),

                // 4. Description
                Textarea::make('description')
                    ->columnSpanFull(),

                // 5. Thumbnail
                \Filament\Forms\Components\FileUpload::make('thumbnail_path')
                    ->label('Thumbnail (Upload)')
                    ->image()
                    ->directory('course-session-thumbnails')
                    ->disk('public')
                    ->columnSpanFull(),

                \Filament\Forms\Components\Select::make('media_item_selection')
                    ->label('OR Select from Media Library')
                    ->options(fn () => \App\Models\MediaItem::where('type', 'image')->latest()->pluck('title', 'path'))
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $set('thumbnail_path', $state);
                        }
                    })
                    ->dehydrated(false)
                    ->columnSpanFull(),
                // 6. Sort Order
                TextInput::make('sort_order')
                    ->label('Order')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order', 'asc')
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\ViewColumn::make('card')
                        ->view('filament.tables.course-sessions.card')
                        ->extraAttributes(['class' => 'h-full w-full']),
                ])->space(3),
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

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourseSessions::route('/'),
            'create' => Pages\CreateCourseSession::route('/create'),
            'edit' => Pages\EditCourseSession::route('/{record}/edit'),
        ];
    }
}