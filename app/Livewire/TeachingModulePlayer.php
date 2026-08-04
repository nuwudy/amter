<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\TeachingModule;

class TeachingModulePlayer extends Component
{
    public $game;
    public $gameId;
    public $currentIndex = 0;
    
    public function mount($gameId)
    {
        $this->gameId = $gameId;
        $this->loadGame();
    }

    public function loadGame()
    {
        $this->game = TeachingModule::findOrFail($this->gameId);
        $this->currentIndex = 0;
    }

    public function nextSlide()
    {
        if ($this->currentIndex < count($this->game->slides ?? []) - 1) {
            $this->currentIndex++;
        }
    }

    public function prevSlide()
    {
        if ($this->currentIndex > 0) {
            $this->currentIndex--;
        }
    }

    public function goToSlide($index)
    {
        if (isset(($this->game->slides ?? [])[$index])) {
            $this->currentIndex = $index;
        }
    }

    public function render()
    {
        return view('livewire.teaching-module-player', [
            'slides' => $this->game->slides ?? [],
            'currentSlide' => ($this->game->slides ?? [])[$this->currentIndex] ?? null,
            'totalSlides' => count($this->game->slides ?? []),
        ]);
    }
}
