<?php

namespace App\Filament\Student\Pages;

use Filament\Pages\Page;

class Dashboard extends Page
{
    protected static ?string $title = 'Dashboard';
    protected static string|\BackedEnum|null $navigationIcon = null;
    protected string $view = 'filament.student.pages.dashboard';

    public function mount()
    {
        // Sync milestones in case they were missed
        $service = new \App\Services\MilestoneService();
        $service->checkRetroactiveMilestones(auth()->user());
    }

    public $search = '';

    public function getCourses()
    {
        return \App\Models\Course::where('is_published', true)
            ->when($this->search, function ($query) {
                $query->where('title', 'like', '%' . $this->search . '%');
            })
            ->get();
    }

    public function getResumeUrl(): ?string
    {
        $nextUnit = auth()->user()->getNextIncompleteUnit();
        return $nextUnit ? route('student.units.show', ['unit' => $nextUnit->id]) : null;
    }
}
