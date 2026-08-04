<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\SpeakingGame;

class SpeakingGamePlayer extends Component
{
    public $game;
    public $gameId;
    
    public $currentQuestionIndex = 0;
    public $score = 0;
    
    public $status = 'playing';
    
    public $isAnswerCorrect = false;
    public $spokenText = null;
    public $matchPercentage = 0;

    public function mount($gameId)
    {
        $this->gameId = $gameId;
        $this->loadGame();
    }

    public function loadGame()
    {
        $this->game = SpeakingGame::findOrFail($this->gameId);
        $this->resetGame();
    }

    public $scoredQuestions = [];

    public function resetGame()
    {
        $this->currentQuestionIndex = 0;
        $this->score = 0;
        $this->status = 'playing';
        $this->isAnswerCorrect = false;
        $this->spokenText = null;
        $this->scoredQuestions = [];
        $this->matchPercentage = 0;
    }

    private function getWordMatchPercentage($targetStr, $spokenStr)
    {
        $cleanWord = function($w) {
            $word = strtolower(trim($w));
            $word = preg_replace('/[?.,\/#!$%\^&\*;:{}=\-_`~()\']/u', '', $word);
            if ($word === 'whats' || $word === 'whatis') return 'was';
            if ($word === 'where') return 'were';
            if ($word === 'their' || $word === 'theyre' || $word === 'theyare') return 'there';
            return $word;
        };

        $targetWords = array_filter(array_map($cleanWord, preg_split('/\s+/', $targetStr)));
        $spokenWords = array_filter(array_map($cleanWord, preg_split('/\s+/', $spokenStr)));

        if (empty($targetWords)) return 0;

        $targetCount = count($targetWords);
        $spokenCount = count($spokenWords);

        $dp = array_fill(0, $targetCount + 1, array_fill(0, $spokenCount + 1, 0));

        for ($i = 0; $i <= $targetCount; $i++) $dp[$i][0] = $i;
        for ($j = 0; $j <= $spokenCount; $j++) $dp[0][$j] = $j;

        for ($i = 1; $i <= $targetCount; $i++) {
            for ($j = 1; $j <= $spokenCount; $j++) {
                if ($targetWords[$i - 1] === $spokenWords[$j - 1]) {
                    $dp[$i][$j] = $dp[$i - 1][$j - 1];
                } else {
                    $dp[$i][$j] = min(
                        $dp[$i - 1][$j] + 1,
                        $dp[$i][$j - 1] + 1,
                        $dp[$i - 1][$j - 1] + 1
                    );
                }
            }
        }

        $editDistance = $dp[$targetCount][$spokenCount];
        $maxLen = max($targetCount, $spokenCount);

        $percentage = round((($maxLen - $editDistance) / $maxLen) * 100);
        return max(0, $percentage);
    }

    public function evaluateSpeech($text)
    {
        if ($this->status !== 'playing') {
            return;
        }

        $this->spokenText = trim($text);
        
        $questions = $this->game->questions;
        $currentQuestion = $questions[$this->currentQuestionIndex];
        
        $targets = collect($currentQuestion['accepted_answers'] ?? [])
            ->pluck('text')
            ->toArray();

        // Add the main sentence as a target if not already there
        if (!empty($currentQuestion['sentence']) && !in_array($currentQuestion['sentence'], $targets)) {
            $targets[] = $currentQuestion['sentence'];
        }

        // 1. Precise Normalization
        $normalize = function($str) {
            $s = strtolower(trim($str));
            // Basic contraction expansion for better matching
            $s = str_replace(["'s", "'m", "'re", "n't", "'ll", "'ve", "'d"], [" is", " am", " are", " not", " will", " have", " would"], $s);
            // Remove all non-alphanumeric characters for base comparison
            $s = preg_replace('/[^a-z0-9]/', '', $s);
            // Specific fix for common STT misrecognitions
            $s = str_replace(["whats", "whatis"], "was", $s);
            $s = str_replace("where", "were", $s);
            $s = str_replace(["their", "theyre", "theyare"], "there", $s);
            return $s;
        };

        $normalizedSpoken = $normalize($this->spokenText);
        $isMatch = false;

        $highestScore = 0;
        foreach ($targets as $target) {
            $score = $this->getWordMatchPercentage($target, $this->spokenText);
            if ($score > $highestScore) {
                $highestScore = $score;
            }

            $normalizedTarget = $normalize($target);
            
            // A. Exact Match After Normalization
            if ($normalizedTarget === $normalizedSpoken) {
                $isMatch = true;
            }

            // B. Fuzzy Match (handle slight audio recognition artifacts)
            // Allow 1 character difference for every 8 characters in length
            $maxLength = max(strlen($normalizedTarget), strlen($normalizedSpoken));
            if ($maxLength > 0) {
                $maxDistance = max(1, floor($maxLength / 8));
                if (levenshtein($normalizedTarget, $normalizedSpoken) <= $maxDistance) {
                    $isMatch = true;
                }
            }

            // C. Phonetic Match (handle homophones or slight audio artifacts)
            if (metaphone($normalizedTarget) === metaphone($normalizedSpoken)) {
                $isMatch = true;
            }
        }
        
        $this->matchPercentage = $highestScore;

        if ($isMatch && $this->matchPercentage < 100) {
            $this->matchPercentage = 100;
        }

        if ($isMatch || $this->matchPercentage >= 85) {
            $this->isAnswerCorrect = true;
        } else {
            $this->isAnswerCorrect = false;
        }
        
        if ($this->isAnswerCorrect && !in_array($this->currentQuestionIndex, $this->scoredQuestions)) {
            $this->score++;
            $this->scoredQuestions[] = $this->currentQuestionIndex;
        }
        
        $this->status = 'feedback';
    }

    public function retryQuestion()
    {
        $this->status = 'playing';
        $this->spokenText = null;
        $this->isAnswerCorrect = false;
        $this->matchPercentage = 0;
    }

    public function nextQuestion()
    {
        $questions = $this->game->questions;
        
        if ($this->currentQuestionIndex < count($questions) - 1) {
            $this->currentQuestionIndex++;
            $this->status = 'playing';
            $this->spokenText = null;
            $this->isAnswerCorrect = false;
            $this->matchPercentage = 0;
        } else {
            $this->status = 'finished';
        }
    }

    public function prevQuestion()
    {
        if ($this->currentQuestionIndex > 0) {
            $this->currentQuestionIndex--;
            $this->status = 'playing';
            $this->spokenText = null;
            $this->isAnswerCorrect = false;
            $this->matchPercentage = 0;
        }
    }

    public function playAgain()
    {
        $this->resetGame();
    }

    public function render()
    {
        return view('livewire.speaking-game-player');
    }
}
