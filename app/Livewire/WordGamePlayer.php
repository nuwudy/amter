<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\WordGame;

class WordGamePlayer extends Component
{
    public $game;
    public $gameId;
    
    public $currentQuestionIndex = 0;
    public $score = 0;
    
    // Status tracking: 'playing', 'feedback', 'finished'
    public $status = 'playing';
    
    // Feedback data
    public $isAnswerCorrect = false;
    public $selectedOption = null;

    public function mount($gameId)
    {
        $this->gameId = $gameId;
        $this->loadGame();
    }

    public function loadGame()
    {
        $this->game = WordGame::findOrFail($this->gameId);
        $this->resetGame();
    }

    public function resetGame()
    {
        $this->currentQuestionIndex = 0;
        $this->score = 0;
        $this->status = 'playing';
        $this->isAnswerCorrect = false;
        $this->selectedOption = null;
    }

    public function selectOption($optionIndex)
    {
        if ($this->status !== 'playing') {
            return;
        }

        $questions = $this->game->questions;
        $currentQuestion = $questions[$this->currentQuestionIndex];
        
        $option = $currentQuestion['options'][$optionIndex];
        $this->selectedOption = $option;
        
        $this->isAnswerCorrect = $option['is_correct'] ?? false;
        
        if ($this->isAnswerCorrect) {
            $this->score++;
        }
        
        $this->status = 'feedback';
    }

    public function nextQuestion()
    {
        $questions = $this->game->questions;
        
        if ($this->currentQuestionIndex < count($questions) - 1) {
            $this->currentQuestionIndex++;
            $this->status = 'playing';
            $this->selectedOption = null;
            $this->isAnswerCorrect = false;
        } else {
            $this->status = 'finished';
        }
    }

    public function prevQuestion()
    {
        if ($this->currentQuestionIndex > 0) {
            $this->currentQuestionIndex--;
            $this->status = 'playing';
            $this->selectedOption = null;
            $this->isAnswerCorrect = false;
        }
    }

    public function playAgain()
    {
        $this->resetGame();
    }

    public function render()
    {
        return view('livewire.word-game-player');
    }
}
