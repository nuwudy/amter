<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\InteractiveLesson;

class InteractiveLessonPlayer extends Component
{
    public $game;
    public $gameId;
    public $items = [];
    public $currentIndex = 0;
    public $currentPhase = 'learn';
    public $status = 'waiting';
    public $userChoice = null;
    public $spokenText = null;
    public $options = [];
    public $matchPercentage = 0;

    public function mount($gameId)
    {
        $this->gameId = $gameId;
        $this->loadGame();
    }

    public function loadGame()
    {
        $this->game = InteractiveLesson::findOrFail($this->gameId);
        $this->items = $this->game->items ?? [];
        $this->currentIndex = 0;
        $this->currentPhase = 'learn';
        $this->matchPercentage = 0;
    }

    // PHASE TRANSITIONS
    public function startTest()
    {
        $this->currentPhase = 'test';
        $this->status = 'waiting';
        $this->userChoice = null;
        $this->matchPercentage = 0;
        
        $currentItem = $this->items[$this->currentIndex] ?? null;
        if ($currentItem) {
            $this->options = collect($currentItem['distractors'] ?? [])
                ->pluck('text')
                ->push($currentItem['english_text'])
                ->shuffle()
                ->toArray();
        }
    }

    public function checkTestChoice($choice)
    {
        $this->userChoice = $choice;
        $currentItem = $this->items[$this->currentIndex];
        $correctText = $currentItem['english_text'];

        if (trim($choice) === trim($correctText)) {
            $this->status = 'success';
            
            // Dispatch event to play audio on correct answer
            if (!empty($currentItem['audio'])) {
                $this->dispatch('play-audio', path: asset('storage/' . $currentItem['audio']));
            }
        } else {
            $this->status = 'failed';
        }
    }

    public function retry()
    {
        $this->status = 'waiting';
        $this->userChoice = null;
        $this->spokenText = null;
        $this->matchPercentage = 0;
    }

    public function startSpeak()
    {
        $currentItem = $this->items[$this->currentIndex];
        
        // Skip speak phase if disabled or missing voice match toggle
        if (isset($currentItem['enable_voice_match']) && $currentItem['enable_voice_match'] === false) {
            $this->nextItem();
            return;
        }

        $this->currentPhase = 'speak';
        $this->status = 'waiting';
        $this->spokenText = null;
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
        if ($this->currentPhase !== 'speak') return;

        $this->spokenText = trim($text);
        $currentItem = $this->items[$this->currentIndex];
        
        $targets = array_merge(
            [$currentItem['english_text']],
            $currentItem['voice_variations'] ?? []
        );

        // 1. Precise Normalization
        $normalize = function($str) {
            $s = strtolower(trim($str));
            // Basic contraction expansion for better matching
            $s = str_replace(["'s", "'m", "'re", "n't", "'ll", "'ve", "'d"], [" is", " am", " are", " not", " will", " have", " would"], $s);
            // Remove all non-alphanumeric characters
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
            $maxDistance = max(1, floor($maxLength / 8));
            if (levenshtein($normalizedTarget, $normalizedSpoken) <= $maxDistance) {
                $isMatch = true;
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
            $this->status = 'success';
        } else {
            $this->status = 'failed';
        }
    }

    public function next()
    {
        if ($this->currentPhase === 'learn') {
            $this->startTest();
        } elseif ($this->currentPhase === 'test' && $this->status === 'success') {
            $currentItem = $this->items[$this->currentIndex] ?? null;
            if ($currentItem && isset($currentItem['enable_voice_match']) && $currentItem['enable_voice_match'] === false) {
                $this->nextItem();
            } else {
                $this->startSpeak();
            }
        } elseif ($this->currentPhase === 'speak' && $this->status === 'success') {
            $this->nextItem();
        }
    }

    public function prev()
    {
        $this->matchPercentage = 0;
        if ($this->currentPhase === 'speak') {
            $this->currentPhase = 'test';
            $this->status = 'waiting';
        } elseif ($this->currentPhase === 'test') {
            $this->currentPhase = 'learn';
            $this->status = 'waiting';
        } elseif ($this->currentIndex > 0) {
            $this->currentIndex--;
            
            $currentItem = $this->items[$this->currentIndex] ?? null;
            if ($currentItem && isset($currentItem['enable_voice_match']) && $currentItem['enable_voice_match'] === false) {
                $this->currentPhase = 'test';
            } else {
                $this->currentPhase = 'speak';
            }
            $this->status = 'waiting';
        }
    }

    public function nextItem()
    {
        $this->matchPercentage = 0;
        if ($this->currentIndex < count($this->items) - 1) {
            $this->currentIndex++;
            $this->currentPhase = 'learn';
            $this->status = 'waiting';
        } else {
            $this->currentPhase = 'result';
        }
    }

    public function prevItem()
    {
        $this->prev();
    }

    public function resetGame()
    {
        $this->currentIndex = 0;
        $this->currentPhase = 'learn';
        $this->status = 'waiting';
        $this->matchPercentage = 0;
    }

    public function render()
    {
        return view('livewire.interactive-lesson-player', [
            'currentItem' => $this->items[$this->currentIndex] ?? null,
            'totalItems' => count($this->items),
        ]);
    }
}
