<?php

namespace App\Filament\Widgets;

use Filament\Schemas\Schema;
use Filament\Widgets\Widget;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use App\Filament\Resources\UnitResource;

class ResumeLearning extends Widget implements HasForms, HasSchemas
{
    use InteractsWithForms, InteractsWithSchemas {
        InteractsWithSchemas::getCachedSchemas insteadof InteractsWithForms;
    }

    protected string $view = 'filament.widgets.resume-learning';
    
    protected static ?int $sort = 1; // Keep it at the top
    protected int | string | array $columnSpan = 'full';

    public function schema(Schema $schema): Schema
    {
        $nextUnit = auth()->user()->getNextIncompleteUnit();

        return $schema
            ->state(['unit' => $nextUnit])
            ->schema([
                Section::make('Continue Your Journey')
                    ->description($nextUnit ? "Ready for your next native clip?" : "You've finished everything!")
                    ->schema([
                        TextEntry::make('unit.title')
                            ->label('Current Lesson')
                            ->weight('bold')
                            ->size('lg')
                            ->placeholder('No units available'),
                        
                        \Filament\Schemas\Components\Actions::make([
                            \Filament\Actions\Action::make('resume')
                                ->label('Resume Learning')
                                ->color('primary')
                                ->icon('heroicon-m-play-circle')
                                ->url(fn () => $nextUnit ? route('student.units.show', ['unit' => $nextUnit->id]) : '#')
                                ->visible(fn () => $nextUnit !== null),
                        ]),
                    ])->columns(2),
            ]);
    }
}
