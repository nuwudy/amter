<?php

namespace App\Filament\Student\Pages;

use Filament\Pages\Page;

class Milestones extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = null;
    
    protected string $view = 'filament.student.pages.milestones';

    public $milestones;

    public function mount()
    {
        $this->milestones = auth()->user()->milestones()
            ->latest('awarded_at')
            ->get();
    }
}
