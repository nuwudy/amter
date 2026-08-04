<div>
    @if ($game && count($game['questions'] ?? []) > 0)
        @php
            $totalQuestions = count($game['questions']);
            $progressPercent = $status === 'finished' ? 100 : ($currentQuestionIndex / $totalQuestions) * 100;
        @endphp

        <style>
            .wg-container { max-width: 32rem; margin: 2rem auto; background-color: #ffffff; border-radius: 1.5rem; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1); padding: 2rem; border: 1px solid #f1f5f9; font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; position: relative; z-index: 10; }
            .wg-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; font-size: 0.875rem; font-weight: 600; color: #64748b; }
            .wg-progress-track { width: 100%; background-color: #e2e8f0; border-radius: 9999px; height: 0.5rem; overflow: hidden; margin-bottom: 1.5rem; }
            .wg-progress-bar { background-color: #14b8a6; height: 100%; transition: width 0.4s ease; border-radius: 9999px; }
            .wg-instruction { color: #0d9488; font-weight: 800; font-size: 1.125rem; margin-bottom: 0.25rem; }
            .wg-malayalam { color: #64748b; font-size: 0.875rem; margin-bottom: 1rem; }
            .wg-sentence { font-size: 1.5rem; font-weight: 700; color: #1e293b; text-align: center; margin: 1.5rem 0; padding: 1.5rem; background-color: #f8fafc; border-radius: 1rem; border: 1px solid #f1f5f9; line-height: 1.8; }
            .wg-options { display: flex; flex-direction: column; gap: 0.75rem; margin-top: 2rem; }
            .wg-option-btn { width: 100%; padding: 1rem 1.25rem; background-color: #ffffff; border: 2px solid #e2e8f0; color: #334155; border-radius: 0.75rem; font-weight: 600; font-size: 1.125rem; text-align: left; cursor: pointer; transition: all 0.2s ease; display: flex; align-items: center; justify-content: space-between; }
            .wg-option-btn:hover { border-color: #14b8a6; background-color: #f0fdfa; color: #0d9488; }
            .wg-finished-box { text-align: center; padding: 2rem 0; }
            .wg-finished-icon { font-size: 4rem; animation: bounce 1s infinite alternate; }
            .wg-finished-title { font-size: 2rem; font-weight: 800; color: #1e293b; margin-top: 1rem; margin-bottom: 0.5rem; }
            .wg-finished-text { font-size: 1.25rem; color: #475569; margin-bottom: 2rem; }
            .wg-btn-primary { padding: 1rem 2rem; background-color: #14b8a6; color: #ffffff; border-radius: 0.75rem; font-weight: 700; font-size: 1.125rem; text-transform: uppercase; letter-spacing: 0.05em; border: none; cursor: pointer; width: 100%; box-shadow: 0 4px 6px -1px rgba(20, 184, 166, 0.2); transition: background-color 0.2s; }
            .wg-btn-primary:hover { background-color: #0d9488; }
            .wg-feedback-box { text-align: center; padding: 1rem 0; }
            .wg-feedback-icon { font-size: 4rem; margin-bottom: 1rem; animation: bounce 1s alternate; }
            .wg-feedback-title { font-size: 2rem; font-weight: 800; margin-bottom: 1.5rem; }
            .wg-feedback-title.correct { color: #16a34a; }
            .wg-feedback-title.incorrect { color: #dc2626; }
            .wg-feedback-card { padding: 1.5rem; border-radius: 1rem; border-width: 2px; border-style: solid; margin-bottom: 2rem; }
            .wg-feedback-card.correct { border-color: #bbf7d0; background-color: #f0fdf4; }
            .wg-feedback-card.incorrect { border-color: #fecaca; background-color: #fef2f2; }
            .wg-feedback-choice-label { font-size: 1.125rem; font-weight: 600; color: #334155; margin-bottom: 0.5rem; }
            .wg-feedback-choice.correct { color: #16a34a; }
            .wg-feedback-choice.incorrect { color: #dc2626; }
            .wg-audio-box { margin-top: 1.5rem; padding: 1rem; background-color: #ffffff; border-radius: 0.75rem; box-shadow: inset 0 2px 4px 0 rgba(0,0,0,0.06); }
            @keyframes bounce { 0% { transform: translateY(0); } 100% { transform: translateY(-10px); } }

            .wg-nav-bar { 
                margin: 2rem -2rem -2rem -2rem; 
                padding: 1.25rem 2rem; 
                background: #f8fafc; 
                border-top: 1px solid #f1f5f9; 
                border-radius: 0 0 1.5rem 1.5rem; 
                display: flex; 
                gap: 1rem; 
            }
            .wg-btn-nav { flex: 1; padding: 0.75rem; border-radius: 0.75rem; border: 1px solid #e2e8f0; background: white; color: #64748b; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem; transition: all 0.2s; }
            .wg-btn-nav:hover:not(:disabled) { background: #f8fafc; border-color: #cbd5e1; color: #334155; }
            .wg-btn-nav:disabled { opacity: 0.5; cursor: not-allowed; }
            .wg-btn-nav.primary { background: #14b8a6; color: white; border-color: #14b8a6; }
            .wg-btn-nav.primary:hover:not(:disabled) { background: #0d9488; }
        </style>

        <div class="wg-container">
            <!-- Progress Bar -->
            <div>
                <div class="wg-header">
                    <span>Score: {{ $score }} / {{ $totalQuestions }}</span>
                    <span>
                        @if ($status === 'finished')
                            Completed!
                        @else
                            Question {{ $currentQuestionIndex + 1 }} of {{ $totalQuestions }}
                        @endif
                    </span>
                </div>
                <div class="wg-progress-track">
                    <div class="wg-progress-bar" style="width: {{ $progressPercent }}%"></div>
                </div>
            </div>

            @if ($status === 'finished')
                <!-- Finished State -->
                <div class="wg-finished-box">
                    <div class="wg-finished-icon">🏆</div>
                    <h2 class="wg-finished-title">Great Job!</h2>
                    <p class="wg-finished-text">You scored {{ $score }} out of {{ $totalQuestions }}.</p>
                    <button wire:click="playAgain" class="wg-btn-primary">
                        Play Again
                    </button>
                </div>
            @else
                @php
                    $question = $game['questions'][$currentQuestionIndex];
                @endphp

                <!-- Playing/Feedback State -->
                <div class="wg-play-area">
                    @if(!empty($question['instruction']))
                        <div class="wg-instruction">{{ $question['instruction'] }}</div>
                    @endif
                    
                    @if(!empty($question['malayalam_sentence']))
                        <div class="wg-malayalam">{{ $question['malayalam_sentence'] }}</div>
                    @endif

                    @php
                        $imageUrl = null;
                        if (!empty($question['image'])) {
                            $imageUrl = asset('storage/' . $question['image']);
                        } elseif (!empty($question['custom_image_url'])) {
                            $imageUrl = str_starts_with($question['custom_image_url'], 'http') 
                                ? $question['custom_image_url'] 
                                : asset('storage/' . $question['custom_image_url']);
                        }
                    @endphp

                    @if($imageUrl)
                        <div style="margin: 1rem 0 1.5rem 0; border-radius: 1rem; overflow: hidden; max-height: 250px; display: flex; justify-content: center; align-items: center; background-color: #f8fafc; border: 1px solid #e2e8f0;">
                            <img src="{{ $imageUrl }}" style="width: 100%; height: 100%; object-fit: contain;" alt="Question Image">
                        </div>
                    @endif

                    @if ($status === 'playing')
                        <h2 class="wg-sentence">
                            {{ $question['sentence'] }}
                        </h2>

                        <div class="wg-options">
                            @foreach ($question['options'] as $index => $option)
                                <button wire:click="selectOption({{ $index }})" class="wg-option-btn">
                                    <span>{{ $option['text'] }}</span>
                                    <svg style="width:24px;height:24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                </button>
                            @endforeach
                        </div>
                    @elseif ($status === 'feedback')
                    @elseif ($status === 'feedback')
                        <div class="wg-feedback-box">
                            @if ($isAnswerCorrect)
                                <div class="wg-feedback-icon" style="font-size: 3rem; margin-bottom: 0.5rem;">✨🎉✨</div>
                                <h2 class="wg-feedback-title correct" style="font-size: 1.75rem; margin-bottom: 1rem;">Super!</h2>
                            @else
                                <div class="wg-feedback-icon" style="font-size: 3rem; margin-bottom: 0.5rem;">🙈</div>
                                <h2 class="wg-feedback-title incorrect" style="font-size: 1.75rem; margin-bottom: 1rem;">Incorrect</h2>
                            @endif
                            
                            <div style="margin: 1.5rem -2rem 0 -2rem; padding: 1.5rem 2rem; background: {{ $isAnswerCorrect ? '#f0fdfa' : '#fef2f2' }}; border-top: 1px solid {{ $isAnswerCorrect ? '#bbf7d0' : '#fecaca' }};">
                                <p style="font-size: 1rem; font-weight: 600; color: #64748b; margin-bottom: 0.5rem;">You chose: <span style="font-weight: 800; color: {{ $isAnswerCorrect ? '#16a34a' : '#dc2626' }};">{{ $selectedOption['text'] }}</span></p>
                                
                                @php
                                    $audioUrl = null;
                                    if (!empty($question['audio'])) {
                                        $audioUrl = asset('storage/' . $question['audio']);
                                    } elseif (!empty($question['custom_audio_url'])) {
                                        $audioUrl = str_starts_with($question['custom_audio_url'], 'http') 
                                            ? $question['custom_audio_url'] 
                                            : asset('storage/' . $question['custom_audio_url']);
                                    }
                                @endphp
                                
                                @if ($audioUrl)
                                    <div style="margin-top: 1rem; padding: 0.75rem; background: rgba(255,255,255,0.5); border-radius: 0.75rem;">
                                        <p style="font-size:0.75rem; color:#64748b; margin-bottom:0.5rem; font-weight:700; text-transform:uppercase;">Listen to the correct sentence</p>
                                        <audio controls autoplay style="width:100%; height:32px;">
                                            <source src="{{ $audioUrl }}" type="audio/mpeg">
                                        </audio>
                                    </div>
                                @endif
                                
                                @if (!$isAnswerCorrect)
                                    @php
                                        $correctOption = collect($question['options'])->firstWhere('is_correct', true);
                                    @endphp
                                    <div style="margin-top:1rem; padding:0.75rem; background:#ffffff; border-radius:0.75rem; border:1px solid #fecaca;">
                                        <p style="font-size:0.75rem; color:#dc2626; font-weight:800; text-transform:uppercase; margin-bottom:0.25rem;">The Correct Answer Is</p>
                                        <p style="font-size:1.125rem; color:#991b1b; font-weight:700;">{{ $correctOption['text'] ?? '' }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                </div>

                <!-- NAVIGATION BAR -->
                <div class="wg-nav-bar">
                    <button wire:click="prevQuestion" class="wg-btn-nav" @disabled($currentQuestionIndex === 0)>
                        <svg style="width:1.25rem;height:1.25rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
                        <span>Prev</span>
                    </button>

                    @if ($status === 'feedback')
                        <button wire:click="nextQuestion" class="wg-btn-nav primary">
                            <span>{{ $currentQuestionIndex < $totalQuestions - 1 ? 'Next' : 'Finish' }}</span>
                            <svg style="width:1.25rem;height:1.25rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                        </button>
                    @else
                        <button class="wg-btn-nav" disabled>
                            <span>Next</span>
                            <svg style="width:1.25rem;height:1.25rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                        </button>
                    @endif
                </div>
            @endif
        </div>
    @else
        <div style="background-color:#fef3c7; color:#166534; padding:1.5rem; border-radius:1rem; text-align:center; font-weight:600;">
            Game Not Found or No Questions Configured.
        </div>
    @endif
</div>
