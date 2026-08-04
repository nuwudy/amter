<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\DB;

class StuckStudents extends TableWidget
{
    protected static ?string $heading = 'Struggling Students (High Views, No Completion)';
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                \App\Models\VideoViewLog::query()
                    ->join('users', 'users.id', '=', 'video_views_log.user_id')
                    ->join('units', 'units.id', '=', 'video_views_log.unit_id')
                    ->leftJoin('completed_units', function ($join) {
                        $join->on('completed_units.unit_id', '=', 'video_views_log.unit_id')
                             ->on('completed_units.user_id', '=', 'video_views_log.user_id');
                    })
                    ->whereNull('completed_units.id') // Ensure not completed
                    ->where('video_views_log.view_count', '>', 5) // High view count threshold
                    ->select(
                        'video_views_log.id', // Keep the original ID for Filament record key
                        'users.name as user_name', 
                        'users.email as user_email',
                        'units.title as unit_title', 
                        'video_views_log.view_count',
                        'video_views_log.updated_at' // Last view time
                    )
            )
            ->columns([
                Tables\Columns\TextColumn::make('user_name')->label('Student')
                    ->description(fn ($record) => $record->user_email),
                Tables\Columns\TextColumn::make('unit_title')->label('Stuck On Lesson'),
                Tables\Columns\TextColumn::make('view_count')->label('View Count')
                    ->badge()
                    ->color('danger'),
                Tables\Columns\TextColumn::make('updated_at')->label('Last Viewed')->since(),
            ])
            ->actions([
                \Filament\Actions\Action::make('assist')
                    ->label('Send Help Email')
                    ->icon('heroicon-o-lifebuoy')
                    ->form([
                        \Filament\Forms\Components\Textarea::make('message')
                            ->default('I noticed you viewed this lesson several times. Do you have any questions?')
                            ->required()
                    ])
                    ->action(function ($record, array $data) {
                         // Send email logic here (simplified for now)
                         \Filament\Notifications\Notification::make()->title('Help email sent to ' . $record->user_name)->success()->send();
                    })
            ]);
    }
}
