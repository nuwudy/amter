<div>
    @if ($game && count($game['questions'] ?? []) > 0)
        @php
            $totalQuestions = count($game['questions']);
            $progressPercent = $status === 'finished' ? 100 : ($currentQuestionIndex / $totalQuestions) * 100;
        @endphp

        <style>
            .sg-container { max-width: 36rem; margin: 2rem auto; background-color: #ffffff; border-radius: 1.5rem; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1); padding: 2rem; border: 1px solid #f1f5f9; font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; position: relative; z-index: 10; }
            .sg-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; font-size: 0.875rem; font-weight: 600; color: #64748b; }
            .sg-progress-track { width: 100%; background-color: #e2e8f0; border-radius: 9999px; height: 0.5rem; overflow: hidden; margin-bottom: 1.5rem; }
            .sg-progress-bar { background-color: #0ea5e9; height: 100%; transition: width 0.4s ease; border-radius: 9999px; }
            .sg-instruction { color: #0284c7; font-weight: 800; font-size: 1.125rem; margin-bottom: 0.25rem; }
            .sg-malayalam { color: #64748b; font-size: 0.875rem; margin-bottom: 1rem; }
            .sg-sentence { font-size: 1.75rem; font-weight: 800; color: #0f172a; text-align: center; margin: 1.5rem 0; padding: 1.5rem; background-color: #f8fafc; border-radius: 1rem; border: 1px solid #f1f5f9; line-height: 1.6; }
            .sg-finished-box { text-align: center; padding: 2rem 0; }
            .sg-finished-icon { font-size: 4rem; animation: bounce 1s infinite alternate; }
            .sg-btn-primary { padding: 1rem 2rem; background-color: #0ea5e9; color: #ffffff; border-radius: 0.75rem; font-weight: 700; font-size: 1.125rem; text-transform: uppercase; letter-spacing: 0.05em; border: none; cursor: pointer; width: 100%; box-shadow: 0 4px 6px -1px rgba(14, 165, 233, 0.2); transition: background-color 0.2s; }
            .sg-btn-primary:hover { background-color: #0284c7; }
            .sg-feedback-card { padding: 1.5rem; border-radius: 1rem; border-width: 2px; border-style: solid; margin-bottom: 2rem; background-color: #ffffff; text-align: center; }
            .sg-feedback-card.correct { border-color: #bbf7d0; box-shadow: 0 10px 15px -3px rgba(74, 222, 128, 0.2); }
            .sg-feedback-card.incorrect { border-color: #fecaca; box-shadow: 0 10px 15px -3px rgba(248, 113, 113, 0.2); }
            .sg-mic-btn { width: 5rem; height: 5rem; border-radius: 9999px; background-color: #0ea5e9; color: white; display: flex; align-items: center; justify-content: center; margin: 0 auto; border: none; cursor: pointer; box-shadow: 0 10px 15px -3px rgba(14, 165, 233, 0.4); transition: all 0.2s; }
            .sg-mic-btn:hover { background-color: #0284c7; transform: scale(1.05); }
            .sg-mic-btn.listening { background-color: #ef4444; animation: pulse 1.5s infinite; box-shadow: 0 10px 15px -3px rgba(239, 68, 68, 0.4); }
            .sg-mic-btn:disabled { background-color: #cbd5e1; cursor: not-allowed; transform: none; box-shadow: none; }
            @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.1); } 100% { transform: scale(1); } }
            .sg-audio-bars { display: flex; gap: 4px; justify-content: center; align-items: flex-end; height: 24px; margin-top: 1rem; }
            .sg-audio-bar { width: 6px; background-color: #ef4444; border-radius: 9999px; animation: sound 0.5s infinite alternate; }
            @keyframes sound { 0% { height: 4px; } 100% { height: 24px; } }
            
            .sg-nav-bar { 
                margin: 2rem -2rem -2rem -2rem; 
                padding: 1.25rem 2rem; 
                background: #f8fafc; 
                border-top: 1px solid #f1f5f9; 
                border-radius: 0 0 1.5rem 1.5rem; 
                display: flex; 
                gap: 1rem; 
            }
            .sg-btn-nav { flex: 1; padding: 0.75rem; border-radius: 0.75rem; border: 1px solid #e2e8f0; background: white; color: #64748b; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem; transition: all 0.2s; }
            .sg-btn-nav:hover:not(:disabled) { background: #f8fafc; border-color: #cbd5e1; color: #334155; }
            .sg-btn-nav:disabled { opacity: 0.5; cursor: not-allowed; }
            .sg-btn-nav.primary { background: #0ea5e9; color: white; border-color: #0ea5e9; }
            .sg-btn-nav.primary:hover:not(:disabled) { background: #0284c7; }
        </style>

        <div class="sg-container">
            <div>
                <div class="sg-header">
                    <span>Score: {{ $score }} / {{ $totalQuestions }}</span>
                    <span>
                        @if ($status === 'finished')
                            Completed!
                        @else
                            Sentence {{ $currentQuestionIndex + 1 }} of {{ $totalQuestions }}
                        @endif
                    </span>
                </div>
                <div class="sg-progress-track">
                    <div class="sg-progress-bar" style="width: {{ $progressPercent }}%"></div>
                </div>
            </div>

            @if ($status === 'finished')
                <div class="sg-finished-box">
                    <div class="sg-finished-icon">🏅</div>
                    <h2 style="font-size: 2rem; font-weight: 800; color: #0f172a; margin-top: 1rem; margin-bottom: 0.5rem;">Well Done!</h2>
                    <p style="font-size: 1.25rem; color: #475569; margin-bottom: 2rem;">You pronounced {{ $score }} out of {{ $totalQuestions }} correctly.</p>
                    <button wire:click="playAgain" class="sg-btn-primary">
                        Practice Again
                    </button>
                </div>
            @else
                @php
                    $question = $game['questions'][$currentQuestionIndex];
                @endphp

                <!-- Play/Feedback Area -->
                <div class="sg-play-area">
                    @if(!empty($question['instruction']))
                        <div class="sg-instruction">{{ $question['instruction'] }}</div>
                    @endif
                    
                    @if(!empty($question['malayalam_sentence']))
                        <div class="sg-malayalam">{{ $question['malayalam_sentence'] }}</div>
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
                            <img src="{{ $imageUrl }}" style="width: 100%; height: 100%; object-fit: contain;" alt="Visual Context">
                        </div>
                    @endif

                    @if ($status === 'playing')
                        <h2 class="sg-sentence">
                            "{{ $question['sentence'] }}"
                        </h2>
                        
                        <div style="text-align: center; margin-top: 3rem;" 
                            x-data="{
                                speechStatus: 'idle',
                                recognizedText: '',
                                recognition: null,
                                init() {
                                    window.SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
                                    if (window.SpeechRecognition) {
                                        this.recognition = new SpeechRecognition();
                                        this.recognition.continuous = false;
                                        this.recognition.interimResults = false;
                                        this.recognition.lang = 'en-US';
                                        
                                        this.recognition.onstart = () => {
                                            this.speechStatus = 'listening';
                                        };
                                        
                                        this.recognition.onresult = (event) => {
                                            this.recognizedText = event.results[0][0].transcript;
                                            this.speechStatus = 'processing';
                                            $wire.evaluateSpeech(this.recognizedText).then(() => {
                                                this.speechStatus = 'idle';
                                            });
                                        };
                                        
                                        this.recognition.onerror = (event) => {
                                            this.speechStatus = 'idle';
                                        };
                                        
                                        this.recognition.onend = () => {
                                            if(this.speechStatus === 'listening') {
                                                this.speechStatus = 'idle';
                                            }
                                        };
                                    } else {
                                        this.speechStatus = 'unsupported';
                                    }
                                },
                                startListening() {
                                    if(this.recognition && this.speechStatus === 'idle') {
                                        this.recognizedText = '';
                                        this.recognition.start();
                                    }
                                }
                            }">
                            
                            <template x-if="speechStatus === 'unsupported'">
                                <div style="color: #dc2626; font-weight: 600; padding: 1rem; background: #fee2e2; border-radius: 0.5rem;">
                                    Voice recognition is not supported in your browser. Please use Chrome, Edge, or Safari.
                                </div>
                            </template>

                            <template x-if="speechStatus !== 'unsupported'">
                                <div>
                                    <button @click="startListening" :disabled="speechStatus !== 'idle'" class="sg-mic-btn" :class="{ 'listening': speechStatus === 'listening' }">
                                        <template x-if="speechStatus === 'idle' || speechStatus === 'processing'">
                                            <svg style="width:36px;height:36px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path></svg>
                                        </template>
                                        <template x-if="speechStatus === 'listening'">
                                            <svg style="width:36px;height:36px;" fill="currentColor" viewBox="0 0 20 20"><path d="M10 3a3 3 0 00-3 3v4a3 3 0 006 0V6a3 3 0 00-3-3z"/><path fill-rule="evenodd" d="M10 16a7 7 0 01-7-7h2a5 5 0 1010 0h2a7 7 0 01-7 7v2h-2v-2z" clip-rule="evenodd"/></svg>
                                        </template>
                                    </button>
                                    
                                    <p style="margin-top: 1.5rem; font-weight: 700; color: #0284c7;" x-show="speechStatus === 'idle'">Tap the mic and speak the sentence</p>
                                    <p style="margin-top: 1.5rem; font-weight: 700; color: #ef4444;" x-show="speechStatus === 'listening'">Listening...</p>
                                    <p style="margin-top: 1.5rem; font-weight: 700; color: #64748b;" x-show="speechStatus === 'processing'">Evaluating...</p>
                                    
                                    <div class="sg-audio-bars" x-show="speechStatus === 'listening'">
                                        <div class="sg-audio-bar" style="animation-delay: 0s;"></div>
                                        <div class="sg-audio-bar" style="animation-delay: 0.1s;"></div>
                                        <div class="sg-audio-bar" style="animation-delay: 0.2s;"></div>
                                        <div class="sg-audio-bar" style="animation-delay: 0.3s;"></div>
                                        <div class="sg-audio-bar" style="animation-delay: 0.1s;"></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    @elseif ($status === 'feedback')
                        <div style="text-align: center; padding: 1rem 0;">
                            @if ($isAnswerCorrect)
                                <div style="font-size: 3rem; margin-bottom: 0.5rem; animation: bounce 1s alternate;">🌟</div>
                                <h2 style="font-size: 1.75rem; font-weight: 800; color: #16a34a; margin-bottom: 1rem;">
                                    {{ $matchPercentage === 100 ? 'Perfect!' : 'Great Pronunciation!' }}
                                </h2>
                            @else
                                <div style="font-size: 3rem; margin-bottom: 0.5rem;">🤔</div>
                                <h2 style="font-size: 1.75rem; font-weight: 800; color: #dc2626; margin-bottom: 1rem;">Not quite right</h2>
                            @endif

                            @if ($matchPercentage > 0)
                                <div style="display: inline-flex; align-items: center; justify-content: center; margin-bottom: 0.75rem;">
                                    <span style="padding: 0.4rem 1.2rem; border-radius: 9999px; font-size: 0.85rem; font-weight: 900; color: #fff; background: {{ $matchPercentage === 100 ? 'linear-gradient(135deg, #10b981 0%, #059669 100%)' : ($matchPercentage >= 85 ? 'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)' : ($matchPercentage >= 50 ? 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)' : 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)')) }}; box-shadow: 0 4px 12px rgba(0,0,0,0.1); letter-spacing: 0.05em; text-transform: uppercase;">
                                        {{ $matchPercentage }}% ACCURATE
                                    </span>
                                </div>
                            @endif
                            
                            <div style="margin: 1.5rem -2rem 0 -2rem; padding: 1.5rem 2rem; background: {{ $isAnswerCorrect ? '#f0fdf4' : '#fef2f2' }}; border-top: 1px solid {{ $isAnswerCorrect ? '#bbf7d0' : '#fecaca' }};">
                                <p style="font-size: 1rem; font-weight: 600; color: #64748b; margin-bottom: 0.5rem;">You said:</p>
                                <p style="font-size: 1.5rem; font-weight: 800; color: {{ $isAnswerCorrect ? '#166534' : '#991b1b' }}; margin-bottom: 1rem;">"{{ $spokenText }}"</p>
                                
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
                                        <p style="font-size: 0.75rem; color: #64748b; margin-bottom: 0.5rem; font-weight: 700; text-transform: uppercase;">Listen to correct pronunciation</p>
                                        <audio controls autoplay style="width: 100%; height: 32px;">
                                            <source src="{{ $audioUrl }}" type="audio/mpeg">
                                        </audio>
                                    </div>
                                @endif
                                
                                @if (!$isAnswerCorrect)
                                    <div style="margin-top: 1rem; padding: 0.75rem; background: #ffffff; border-radius: 0.75rem; border: 1px solid #fecaca;">
                                        <p style="font-size: 0.75rem; color: #dc2626; font-weight: 800; text-transform: uppercase; margin-bottom: 0.25rem;">Try saying:</p>
                                        <p style="font-size: 1.125rem; color: #991b1b; font-weight: 700;">{{ $question['sentence'] }}</p>
                                    </div>

                                    <button wire:click="retryQuestion" class="sg-btn-nav" style="background: #ef4444; color: white; width: 100%; border: none; margin-top: 1rem; padding: 1rem;">
                                        <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                        <span>Try Again</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endif

                </div>

                <!-- NAVIGATION BAR -->
                <div class="sg-nav-bar">
                    <button wire:click="prevQuestion" class="sg-btn-nav" @disabled($currentQuestionIndex === 0)>
                        <svg style="width:1.25rem;height:1.25rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
                        <span>Prev</span>
                    </button>

                    @if ($status === 'feedback')
                        <button wire:click="nextQuestion" class="sg-btn-nav primary">
                            <span>{{ $currentQuestionIndex < $totalQuestions - 1 ? 'Next' : 'Finish' }}</span>
                            <svg style="width:1.25rem;height:1.25rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                        </button>
                    @else
                        <button class="sg-btn-nav" disabled>
                            <span>Next</span>
                            <svg style="width:1.25rem;height:1.25rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                        </button>
                    @endif
                </div>
            @endif
        </div>
    @else
        <div style="background-color:#fef3c7; color:#166534; padding:1.5rem; border-radius:1rem; text-align:center; font-weight:600;">
            Speaking Game Not Found or No Sentences Configured.
        </div>
    @endif
</div>
