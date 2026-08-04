<?php

namespace App\Filament\Student\Pages;

use Filament\Pages\Page;
use App\Models\CourseSession;

class Library extends Page
{
    // protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-library';
    protected static string | \BackedEnum | null $navigationIcon = null;
    protected string $view = 'filament.student.pages.library';
    protected static ?string $navigationLabel = 'The Library';
    protected static ?string $slug = 'library';
    protected static ?string $title = 'My Course Library';

    // Fetch data for the view
    public $search = '';

    public function getSessions()
    {
        return CourseSession::with('module')
            ->withCount('units')
            ->orderBy('sort_order')
            ->when($this->search, function ($query) {
                $query->where('title', 'like', '%' . $this->search . '%')
                      ->orWhereHas('module', function ($q) {
                          $q->where('name', 'like', '%' . $this->search . '%');
                      });
            })
            ->get();
    }

    public function getResumeUrl(): ?string
    {
        $nextUnit = auth()->user()->getNextIncompleteUnit();
        return $nextUnit ? route('student.units.show', ['unit' => $nextUnit->id]) : null;
    }

    protected function getViewData(): array
    {
        return [
            // Keeping this for compatibility if the view uses $sessions, 
            // but effectively we'll switch to $this->getSessions() in the view.
            'sessions' => $this->getSessions(),
        ];
    }
}
