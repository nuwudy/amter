<?php

namespace App\Filament\Student\Pages;

use App\Http\Controllers\HomeController;
use App\Models\LearningTrack;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class Dashboard extends Page
{
    protected static ?string $title = 'Dashboard';
    protected static string|\BackedEnum|null $navigationIcon = null;
    protected string $view = 'filament.student.pages.dashboard';

    public $search = '';

    public function mount()
    {
        $service = new \App\Services\MilestoneService();
        $service->checkRetroactiveMilestones(auth()->user());
    }

    public function getPrimaryTrack(): ?LearningTrack
    {
        return LearningTrack::getPrimaryTrack() ?? LearningTrack::where('is_active', true)->first();
    }

    public function getMovieLibraryData(): array
    {
        return HomeController::buildMovieLibraryData(request());
    }

    public function getResumeUrl(): ?string
    {
        $libraryData = $this->getMovieLibraryData();
        if (!empty($libraryData['heroSession']) && !empty($libraryData['heroSession']->target_url) && $libraryData['heroSession']->target_url !== '#') {
            return $libraryData['heroSession']->target_url;
        }

        return auth()->user()->getNextIncompleteTrackUnitUrl($this->getPrimaryTrack());
    }

    public function getTrackStats(): array
    {
        $track = $this->getPrimaryTrack();
        $user = auth()->user();

        if (!$track) {
            return [
                'track' => null,
                'totalSteps' => 0,
                'completedSteps' => 0,
                'percent' => 0,
                'currentStep' => null,
                'resumeUrl' => null,
            ];
        }

        $totalSteps = $track->trackUnits()->count();
        $completedSteps = $user->completedUnits()
            ->whereIn('unit_id', $track->trackUnits()->pluck('unit_id'))
            ->count();

        $percent = $totalSteps > 0 ? (int) min(100, round(($completedSteps / $totalSteps) * 100)) : 0;
        $currentStep = $user->getNextIncompleteTrackUnit($track);
        $resumeUrl = $this->getResumeUrl();

        return [
            'track' => $track,
            'totalSteps' => $totalSteps,
            'completedSteps' => $completedSteps,
            'percent' => $percent,
            'currentStep' => $currentStep,
            'resumeUrl' => $resumeUrl,
        ];
    }

    public function getSearchResults(): Collection
    {
        $keyword = trim($this->search);
        if (empty($keyword)) {
            return collect();
        }

        $track = $this->getPrimaryTrack();
        if (!$track) {
            return collect();
        }

        return $track->searchSteps($keyword);
    }

    public function clearSearch(): void
    {
        $this->search = '';
    }
}
