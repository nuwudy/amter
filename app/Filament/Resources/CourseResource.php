<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\Pages;
use App\Models\Course;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\Action;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\TextInput::make('title')->required(),
                \Filament\Forms\Components\TextInput::make('slug')->required(),
                \Filament\Forms\Components\Textarea::make('description'),
                \Filament\Forms\Components\FileUpload::make('thumbnail')->disk('public')->directory('courses'),
                \Filament\Forms\Components\Toggle::make('is_published')->default(false),
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
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\ImageColumn::make('thumbnail')
                        ->height('200px')
                        ->width('100%')
                        ->disk('public'),
                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make('title')
                            ->weight('bold')
                            ->size('lg'),
                        Tables\Columns\TextColumn::make('units_count')
                            ->label('Lessons')
                            ->counts('units')
                            ->formatStateUsing(fn ($state) => $state . ' Clips Available')
                            ->color('gray'),
                    ])->space(1),
                ])->space(3),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                Action::make('broadcast')
                    ->label('Broadcast Update')
                    ->icon('heroicon-o-megaphone')
                    ->color('warning')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('subject')
                            ->required()
                            ->default('New content added to your course!'),
                        \Filament\Forms\Components\Textarea::make('message')
                            ->required()
                            ->rows(5)
                            ->placeholder('Tell them what is new...'),
                    ])
                    ->action(function (Course $record, array $data) {
                        // 1. Get all enrolled students
                        $students = $record->enrolledUsers; 

                        // 2. Send Database Notification (App Alert)
                        if ($students->count() > 0) {
                             \Filament\Notifications\Notification::make()
                                ->title($data['subject'])
                                ->body($data['message'])
                                ->sendToDatabase($students);

                            // 3. Send Emails (Queued for performance)
                            foreach ($students as $student) {
                                \Illuminate\Support\Facades\Mail::to($student->email)->queue(new \App\Mail\CourseUpdate($data, $student));
                            }
                        }

                        \Filament\Notifications\Notification::make()->title('Broadcast sent to ' . $students->count() . ' students!')->success()->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }
}
