<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ModuleResource\Pages;
use App\Models\Module;
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

class ModuleResource extends Resource
{
    protected static ?string $model = Module::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationLabel = 'Modules';
    protected static ?string $pluralModelLabel = 'Library Modules';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // 1. Name Field (Auto-generates the slug when you type)
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),

                // 2. Slug Field (Auto-filled, but you can edit it)
                TextInput::make('slug')
                    ->required()
                    ->maxLength(255),

                // 3. Thumbnail (New)
                \Filament\Forms\Components\FileUpload::make('thumbnail')
                    ->image()
                    ->imageEditor()
                    ->directory('module-thumbnails')
                    ->disk('public')
                    ->helperText('Upload a high-quality portrait for the Library card.'),

                // 4. Description (New)
                \Filament\Forms\Components\Textarea::make('description'),

                // 5. Sort Order (Renumbered)
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->columns([
                Tables\Columns\ViewColumn::make('module_card')
                    ->view('filament.tables.modules.card')
                    ->extraAttributes(['class' => 'h-full']),
            ])
            ->filters([
                //
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListModules::route('/'),
            'create' => Pages\CreateModule::route('/create'),
            'edit' => Pages\EditModule::route('/{record}/edit'),
        ];
    }
}