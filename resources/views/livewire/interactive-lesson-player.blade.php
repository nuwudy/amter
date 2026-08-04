<div class="il-outer-wrap">
    @if ($currentItem)
        @php
            $totalItemsCount = count($items);
        @endphp

        <style>
            @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');

            :root {
                --primary: #0ea5e9;
                --primary-soft: #f0f9ff;
                --success: #22c55e;
                --success-soft: #f0fdf4;
                --error: #ef4444;
                --error-soft: #fef2f2;
                --text-main: #0f172a;
                --text-sub: #64748b;
                --bg-card: #ffffff;
                --border: #f1f5f9;
                --radius: 1.5rem;
            }

            .il-outer-wrap {
                min-height: min-content;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 1rem;
                font-family: 'Outfit', sans-serif;
                pointer-events: auto !important;
            }

            .il-card { 
                width: 100%;
                max-width: 34rem; 
                background: var(--bg-card); 
                border-radius: 2rem; 
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02); 
                padding: 2.5rem; 
                position: relative; 
                border: 1px solid var(--border);
                overflow: visible;
                transition: transform 0.3s ease;
            }

            /* Progress Header */
            .il-top-meta { display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem; }
            .il-progress-pills { display: flex; gap: 0.5rem; flex-grow: 1; margin-right: 1.5rem; }
            .il-pill { height: 0.6rem; flex-grow: 1; background: #f1f5f9; border-radius: 999px; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); position: relative; overflow: hidden; }
            .il-pill.active { background: var(--primary); box-shadow: 0 0 15px rgba(14, 165, 233, 0.4); }
            .il-pill.completed { background: var(--success); }
            .il-counter { font-size: 0.875rem; font-weight: 800; color: #94a3b8; font-variant-numeric: tabular-nums; }

            /* Content Layout */
            .il-content-anim { animation: il-slide-up 0.6s cubic-bezier(0.16, 1, 0.3, 1); }
            @keyframes il-slide-up { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }

            .il-image-container { 
                width: 100%; 
                aspect-ratio: 16/10; 
                background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); 
                border-radius: var(--radius); 
                margin-bottom: 2rem; 
                overflow: hidden; 
                border: 1px solid var(--border);
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
            }
            .il-image-container img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease; }
            .il-image-container:hover img { transform: scale(1.03); }

            .il-text-center { text-align: center; margin-bottom: 2.5rem; }
            .il-malayalam-sub { font-size: 1.25rem; color: var(--text-sub); margin-bottom: 0.75rem; font-weight: 400; line-height: 1.4; }
            .il-english-main { font-size: 2.5rem; font-weight: 800; color: var(--text-main); line-height: 1.2; letter-spacing: -0.03em; }

            /* Audio Button */
            .il-audio-control { display: flex; flex-direction: column; align-items: center; gap: 0.75rem; margin-bottom: 2.5rem; }
            .il-play-btn {
                width: 4.5rem; height: 4.5rem; border-radius: 999px;
                background: var(--primary-soft); color: var(--primary);
                border: 2px solid #bae6fd; cursor: pointer;
                display: flex; align-items: center; justify-content: center;
                transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            }
            .il-play-btn:hover { background: var(--primary); color: white; border-color: var(--primary); transform: scale(1.1) rotate(5deg); box-shadow: 0 10px 15px -3px rgba(14, 165, 233, 0.3); }
            .il-play-btn:active { transform: scale(0.95); }
            .il-play-btn svg { width: 2rem; height: 2rem; }
            
            .il-audio-label { font-size: 0.875rem; font-weight: 700; color: var(--primary); letter-spacing: 0.05em; text-transform: uppercase; }

            /* Action Button */
            .il-action-btn {
                width: 100%; padding: 1.25rem; border-radius: 1.25rem;
                background: var(--primary); color: white;
                font-weight: 800; font-size: 1.25rem; border: none; cursor: pointer;
                display: flex; align-items: center; justify-content: center; gap: 0.75rem;
                box-shadow: 0 10px 20px -5px rgba(14, 165, 233, 0.4);
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                position: relative; z-index: 50;
                touch-action: manipulation;
                -webkit-tap-highlight-color: transparent;
            }
            .il-action-btn:hover { background: #0284c7; transform: translateY(-3px); box-shadow: 0 15px 25px -5px rgba(14, 165, 233, 0.5); }
            .il-action-btn:active { background: #0369a1; transform: scale(0.98) translateY(0); box-shadow: 0 5px 10px rgba(11, 131, 187, 0.3); }

            /* Test Grid */
            .il-test-grid { display: grid; grid-template-columns: 1fr; gap: 1rem; width: 100%; margin-top: 2rem; }
            .il-test-option {
                padding: 1.5rem; border-radius: 1.25rem; border: 2px solid var(--border);
                background: white; text-align: left; font-weight: 600; color: #475569;
                font-size: 1.25rem; cursor: pointer; transition: all 0.2s ease;
                display: flex; justify-content: space-between; align-items: center;
                position: relative; overflow: hidden;
            }
            .il-test-option:hover:not(:disabled) { border-color: var(--primary); background: var(--primary-soft); color: var(--primary); transform: translateX(5px); }
            .il-test-option.correct { border-color: var(--success); background: var(--success-soft); color: #166534; box-shadow: 0 4px 12px rgba(34, 197, 94, 0.2); }
            .il-test-option.wrong { border-color: var(--error); background: var(--error-soft); color: #991b1b; animation: il-shake 0.5s cubic-bezier(.36,.07,.19,.97) both; }

            @keyframes il-shake {
                10%, 90% { transform: translate3d(-1px, 0, 0); }
                20%, 80% { transform: translate3d(2px, 0, 0); }
                30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
                40%, 60% { transform: translate3d(4px, 0, 0); }
            }

            /* Feedback Footer - Now Relative and Flat */
            .il-feedback-footer { 
                margin: 2rem -2.5rem 0 -2.5rem;
                padding: 1.5rem 2.5rem; 
                background: var(--success-soft); 
                border-top: 1px solid rgba(34, 197, 94, 0.1);
                display: flex; 
                flex-direction: column;
                align-items: center; 
                z-index: 10; 
                animation: il-slide-in-up 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            }
            .il-feedback-footer.failed {
                background: var(--error-soft);
                border-top-color: rgba(239, 68, 68, 0.1);
            }
            @keyframes il-slide-in-up { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

            .il-wrong-msg { color: var(--error); font-weight: 800; font-size: 1.5rem; margin-bottom: 1.5rem; text-align: center; }
            .il-retry-btn { background: #64748b; margin-top: 1rem; }
            .il-retry-btn:hover { background: #475569; }

            /* Mic Section */
            .il-mic-wrap { margin: 3rem 0; text-align: center; }
            .il-mic-btn {
                width: 6rem; height: 6rem; border-radius: 999px; background: var(--primary);
                color: white; border: none; cursor: pointer; box-shadow: 0 10px 25px rgba(14, 165, 233, 0.4);
                transition: all 0.3s; display: flex; align-items: center; justify-content: center; margin: 0 auto;
                position: relative;
            }
            .il-mic-btn:hover:not(:disabled) { transform: scale(1.05); box-shadow: 0 15px 30px rgba(14, 165, 233, 0.5); }
            .il-mic-btn.listening { background: var(--error); animation: il-pulse 1.5s infinite; }
            .il-mic-btn svg { width: 2.5rem; height: 2.5rem; }

            @keyframes il-pulse { 0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); } 70% { box-shadow: 0 0 0 20px rgba(239, 68, 68, 0); } 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); } }

            @media (max-width: 640px) {
                .il-card { margin: 0; padding: 1.5rem 1.5rem 10rem 1.5rem; border-radius: 0; min-height: 100dvh; height: auto; display: flex; flex-direction: column; overflow: visible !important; }
                .il-english-main { font-size: 2rem; }
                .il-feedback-footer { position: fixed; bottom: 0; left: 0; right: 0; border-radius: 0; padding-bottom: 3.5rem; background: #fff !important; pointer-events: auto !important; }
                .il-nav-bar { position: fixed; bottom: 0; left: 0; right: 0; padding: 1rem; background: #fff; border-top: 1px solid var(--border); display: flex; gap: 0.75rem; z-index: 1000; }
            }

            .il-nav-btn {
                flex: 1; padding: 1rem; border-radius: 1rem; border: 1px solid var(--border);
                background: white; color: var(--text-main); font-weight: 700; font-size: 0.95rem;
                display: flex; align-items: center; justify-content: center; gap: 0.5rem;
                transition: all 0.2s; cursor: pointer;
            }
            .il-nav-btn:active { background: #f8fafc; transform: scale(0.98); }
            .il-nav-btn.primary { background: var(--primary); color: white; border-color: var(--primary); box-shadow: 0 4px 12px rgba(14, 165, 233, 0.2); }
            .il-nav-btn:disabled { opacity: 0.4; cursor: not-allowed; }
            .il-nav-bar { 
                position: sticky;
                bottom: 0;
                margin: 2rem -2.5rem -2.5rem -2.5rem;
                padding: 1.25rem 2.5rem;
                background: #f8fafc;
                border-top: 1px solid var(--border);
                border-radius: 0 0 2rem 2rem;
                display: flex;
                gap: 1rem;
                width: auto;
                z-index: 100;
            }
        </style>

        <div class="il-card" x-data="{ 
            playAudio(path) {
                let audio = new Audio(path);
                audio.play().catch(e => console.error('Audio playback failed', e));
            }
        }" x-on:play-audio.window="playAudio($event.detail.path)">
            <!-- Header Progress -->
            <div class="il-top-meta">
                <div class="il-progress-pills">
                    @for($i = 0; $i < $totalItemsCount; $i++)
                        <div class="il-pill {{ $i < $currentIndex ? 'completed' : ($i === $currentIndex ? 'active' : '') }}"></div>
                    @endfor
                </div>
                <div class="il-counter">{{ $currentIndex + 1 }} / {{ $totalItemsCount }}</div>
            </div>

            <!-- Content Area -->
            <div class="il-content-anim" wire:key="item-{{ $currentIndex }}-phase-{{ $currentPhase }}">
                
                <!-- PHASE: RESULT (Moved inside for consistency) -->
                @if ($currentPhase === 'result')
                    <div style="text-align: center; padding: 3rem 0;">
                        <div style="font-size: 6rem; margin-bottom: 2rem; filter: drop-shadow(0 10px 15px rgba(0,0,0,0.1));">🏆</div>
                        <h2 style="font-size: 3rem; font-weight: 800; color: var(--text-main); margin-bottom: 1.5rem; letter-spacing: -0.02em;">Unit Mastered!</h2>
                        <p style="font-size: 1.25rem; color: var(--text-sub); margin-bottom: 3.5rem; max-width: 25rem; margin-left: auto; margin-right: auto; line-height: 1.6;">Excellent job! You've successfully learned, tested, and spoken every sentence in this module.</p>
                        <button wire:click="resetGame" class="il-action-btn" style="max-width: 20rem; margin: 0 auto;">
                            <span>Practice Again</span>
                            <svg style="width:1.5rem;height:1.5rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        </button>
                    </div>

                <!-- PHASE 1: LEARN -->
                @elseif ($currentPhase === 'learn')
                    <div class="il-image-container">
                        <img src="{{ asset('storage/' . $currentItem['image']) }}" alt="Visual prompt">
                    </div>

                    <div class="il-text-center">
                        @if(!empty($currentItem['instruction']))
                            <p style="font-size: 0.875rem; font-weight: 700; color: var(--primary); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.75rem; opacity: 0.8;">{{ $currentItem['instruction'] }}</p>
                        @endif
                        <p class="il-malayalam-sub">{{ $currentItem['malayalam_text'] }}</p>
                        <h2 class="il-english-main">"{{ $currentItem['english_text'] }}"</h2>
                    </div>

                    <div class="il-audio-control" x-data="{ 
                        playing: false,
                        audio: null,
                        init() { this.audio = new Audio('{{ asset('storage/' . $currentItem['audio']) }}'); this.audio.onended = () => this.playing = false; },
                        play() { if(this.playing) { this.audio.pause(); this.audio.currentTime = 0; } this.audio.play(); this.playing = true; }
                    }">
                        <button @click="play()" class="il-play-btn" :aria-label="playing ? 'Pause' : 'Play'">
                            <svg x-show="!playing" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            <svg x-show="playing" fill="currentColor" viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                        </button>
                        <span class="il-audio-label">Listen to Pronunciation</span>
                    </div>
                    <!-- Removed old Check knowledge button -->
                @elseif ($currentPhase === 'test')
                    @if ($status !== 'success')
                        <div class="il-text-center">
                            <span style="font-weight: 800; text-transform: uppercase; letter-spacing: 0.15em; color: var(--primary); font-size: 0.75rem;">Phase 2: Quiz Time</span>
                            @if(!empty($currentItem['instruction']))
                                <p style="font-size: 0.875rem; font-weight: 700; color: var(--primary); text-transform: uppercase; letter-spacing: 0.1em; margin: 1rem 0 0 0; opacity: 0.8;">{{ $currentItem['instruction'] }}</p>
                            @endif
                            <p class="il-malayalam-sub" style="margin-top: 1rem; font-size: 1.4rem; color: var(--text-main); font-weight: 500;">Translate this sentence:</p>
                            <h3 style="font-size: 1.75rem; font-weight: 700; color: var(--text-sub);">"{{ $currentItem['malayalam_text'] }}"</h3>
                        </div>
                    @endif

                    @if ($status === 'success')
                        <div class="il-content-anim" style="text-align: center; margin-top: 0.5rem; margin-bottom: 1rem;">
                            <div style="background: var(--success-soft); display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.4rem 1rem; border-radius: 999px; color: var(--success); font-weight: 800; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 1rem; border: 1px solid rgba(34, 197, 94, 0.2);">
                                <span>✨</span> Quiz Complete <span>✨</span>
                            </div>
                            
                            <h3 style="font-size: 2rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.25rem; letter-spacing: -0.02em;">Excellent Choice!</h3>
                            <p style="color: var(--text-sub); margin-bottom: 1.5rem; font-size: 1rem; opacity: 0.8;">You correctly translated the sentence.</p>

                            <div style="background: white; padding: 1.75rem 1.5rem; border-radius: 1.5rem; border: 1px solid var(--border); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); position: relative; overflow: hidden; margin-bottom: 0.5rem;">
                                <div style="position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: var(--success);"></div>
                                <p style="font-size: 0.7rem; color: var(--success); font-weight: 800; text-transform: uppercase; letter-spacing: 0.15em; margin-bottom: 0.5rem; opacity: 0.8;">English Translation</p>
                                <h2 class="il-english-main" style="font-size: 2.25rem; margin: 0; line-height: 1.1;">{{ $currentItem['english_text'] }}</h2>
                            </div>
                        </div>
                    @else
                        <div class="il-test-grid">
                            @foreach ($options as $option)
                                <button 
                                    wire:click="checkTestChoice('{{ addslashes($option) }}')" 
                                    @disabled($status !== 'waiting')
                                    class="il-test-option {{ ($status === 'success' && $option === $currentItem['english_text']) ? 'correct' : '' }} {{ ($status === 'failed' && $userChoice === $option) ? 'wrong' : '' }}">
                                    <span>{{ $option }}</span>
                                    @if($status === 'success' && $option === $currentItem['english_text'])
                                        <svg style="width:1.75rem;height:1.75rem;color:var(--success)" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                    @endif
                                    @if($status === 'failed' && $userChoice === $option)
                                        <svg style="width:1.75rem;height:1.75rem;color:var(--error)" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    @endif

                    @if ($status === 'success')
                        <div class="il-feedback-footer">
                            <div style="color: var(--success); font-weight: 800; font-size: 1.5rem; margin-bottom: 0.25rem; display: flex; align-items: center; gap: 0.5rem;">
                                <span>✨ Fantastic!</span>
                            </div>
                            <p style="color: var(--text-sub); font-size: 0.9rem; margin: 0;">You correctly translated this sentence.</p>
                        </div>
                    @elseif ($status === 'failed')
                        <div class="il-feedback-footer failed">
                            <div class="il-wrong-msg" style="margin-bottom: 0; font-size: 1.25rem;">
                                ❌ That's not quite right.
                            </div>
                        </div>
                    @endif

                <!-- PHASE 3: SPEAK -->
                @elseif ($currentPhase === 'speak')
                    <div class="il-text-center">
                        <span style="font-weight: 800; text-transform: uppercase; letter-spacing: 0.15em; color: var(--primary); font-size: 0.75rem;">Phase 3: Voice Training</span>
                        @if(!empty($currentItem['instruction']))
                            <p style="font-size: 0.875rem; font-weight: 700; color: var(--primary); text-transform: uppercase; letter-spacing: 0.1em; margin: 1rem 0 0 0; opacity: 0.8;">{{ $currentItem['instruction'] }}</p>
                        @endif
                        <p class="il-malayalam-sub" style="margin-top: 1rem;">Now, speak this sentence clearly:</p>
                        <h2 class="il-english-main">"{{ $currentItem['english_text'] }}"</h2>
                        @if(!empty($currentItem['voice_variations']))
                            <div style="font-size: 1.1rem; color: var(--text-sub); margin-top: 1rem; opacity: 0.8; font-weight: 500;">
                                Or you can say: 
                                <span style="font-style: italic; color: var(--primary); font-weight: 700;">
                                    "{{ implode('" or "', (array)$currentItem['voice_variations']) }}"
                                </span>
                            </div>
                        @endif
                    </div>

                    <div class="il-mic-wrap" 
                        x-data="{
                            status: @entangle('status'),
                            speechStatus: 'idle',
                            recognizedText: '',
                            recognition: null,
                            init() {
                                window.SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
                                if (window.SpeechRecognition) {
                                    this.recognition = new SpeechRecognition();
                                    this.recognition.continuous = false;
                                    this.recognition.lang = 'en-US';
                                    this.recognition.onstart = () => { this.speechStatus = 'listening'; };
                                    this.recognition.onresult = (event) => {
                                        this.recognizedText = event.results[0][0].transcript;
                                        this.speechStatus = 'processing';
                                        @this.evaluateSpeech(this.recognizedText);
                                    };
                                    this.recognition.onend = () => { if(this.speechStatus === 'listening') this.speechStatus = 'idle'; };
                                } else { this.speechStatus = 'unsupported'; }
                            },
                            start() { if(this.recognition) this.recognition.start(); }
                        }"
                        x-init="init()">
                        
                        <button @click="start()" :disabled="status === 'success' || speechStatus === 'listening'" class="il-mic-btn" :class="{ 'listening': speechStatus === 'listening' }">
                            <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7 4a3 3 0 016 0v4a3 3 0 11-6 0V4zm4 10.93A7.001 7.001 0 0017 8a1 1 0 10-2 0A5 5 0 015 8a1 1 0 00-2 0 7.001 7.001 0 006 6.93V17H6a1 1 0 100 2h8a1 1 0 100-2h-3v-2.07z" clip-rule="evenodd"></path></svg>
                        </button>
                        
                        <p style="margin-top: 2rem; font-weight: 700; color: var(--text-sub); font-size: 1.1rem;" x-show="speechStatus === 'idle'">Tap the mic to start speaking</p>
                        <p style="margin-top: 2rem; font-weight: 700; color: var(--error); font-size: 1.1rem;" x-show="speechStatus === 'listening'">Listening... Speak now</p>
                        <p style="margin-top: 2rem; font-weight: 700; color: var(--primary); font-size: 1.1rem;" x-show="speechStatus === 'processing'">Analyzing your voice...</p>
                    </div>

                    @if ($status === 'success')
                        <div class="il-feedback-footer">
                            @if ($matchPercentage > 0)
                                <div style="display: inline-flex; align-items: center; justify-content: center; margin-bottom: 0.75rem;">
                                    <span style="padding: 0.4rem 1.2rem; border-radius: 9999px; font-size: 0.85rem; font-weight: 900; color: #fff; background: {{ $matchPercentage === 100 ? 'linear-gradient(135deg, #10b981 0%, #059669 100%)' : 'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)' }}; box-shadow: 0 4px 12px rgba(0,0,0,0.1); letter-spacing: 0.05em; text-transform: uppercase;">
                                        {{ $matchPercentage }}% ACCURATE
                                    </span>
                                </div>
                            @endif
                            <div style="color: var(--primary); font-weight: 800; font-size: 1.5rem; margin-bottom: 0.25rem;">
                                {{ $matchPercentage === 100 ? '🎉 Perfectly Spoken!' : '🎉 Great Pronunciation!' }}
                            </div>
                            <p style="color: var(--text-sub); font-weight: 600; margin: 0; font-size: 1rem;">"{{ $spokenText }}"</p>
                        </div>
                    @elseif ($status === 'failed' && $spokenText)
                        <div class="il-feedback-footer failed">
                            @if ($matchPercentage > 0)
                                <div style="display: inline-flex; align-items: center; justify-content: center; margin-bottom: 0.75rem;">
                                    <span style="padding: 0.4rem 1.2rem; border-radius: 9999px; font-size: 0.85rem; font-weight: 900; color: #fff; background: {{ $matchPercentage >= 50 ? 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)' : 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)' }}; box-shadow: 0 4px 12px rgba(0,0,0,0.1); letter-spacing: 0.05em; text-transform: uppercase;">
                                        {{ $matchPercentage }}% ACCURATE
                                    </span>
                                </div>
                            @endif
                            <div class="il-wrong-msg" style="margin-bottom: 0.25rem; font-size: 1.25rem;">Try again!</div>
                            <p style="color: var(--error); font-weight: 600; margin: 0; font-size: 0.9rem;">We heard: "{{ $spokenText }}"</p>
                        </div>
                    @endif
                @endif
            </div>

            <!-- PERSISTENT NAVIGATION BAR - AT THE ABSOLUTE BOTTOM OF CARD -->
            @if ($currentPhase !== 'result')
                <div class="il-nav-bar">
                    <button 
                        wire:click="prev" 
                        class="il-nav-btn" 
                        @disabled($currentIndex === 0 && $currentPhase === 'learn')>
                        <svg style="width:1.25rem;height:1.25rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg>
                        <span>Prev</span>
                    </button>

                    @if ($currentPhase === 'learn')
                        <button wire:click="next" class="il-nav-btn primary">
                            <span>Get Started</span>
                            <svg style="width:1.25rem;height:1.25rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                        </button>
                    @elseif ($currentPhase === 'test')
                        @if ($status === 'success')
                            <button wire:click="next" class="il-nav-btn primary">
                                <span>{{ (isset($currentItem['enable_voice_match']) && $currentItem['enable_voice_match'] === false && $currentIndex >= $totalItemsCount - 1) ? 'Finish' : 'Next Step' }}</span>
                                <svg style="width:1.25rem;height:1.25rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                            </button>
                        @elseif ($status === 'failed')
                            <button wire:click="retry" class="il-nav-btn primary" style="background: var(--error);">
                                <span>Try Again</span>
                                <svg style="width:1.25rem;height:1.25rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            </button>
                        @else
                            <button class="il-nav-btn" disabled>
                                <span>Choose Answer</span>
                            </button>
                        @endif
                    @elseif ($currentPhase === 'speak')
                        @if ($status === 'success')
                            <button wire:click="next" class="il-nav-btn primary">
                                <span>{{ ($currentIndex >= $totalItemsCount - 1) ? 'Finish' : 'Next' }}</span>
                                <svg style="width:1.25rem;height:1.25rem" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                            </button>
                        @elseif ($status === 'failed')
                            <button wire:click="retry" class="il-nav-btn primary" style="background: var(--error);">
                                <span>Retry Voice</span>
                                <svg style="width:1.25rem;height:1.25rem" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7 4a3 3 0 016 0v4a3 3 0 11-6 0V4zm4 10.93A7.001 7.001 0 0017 8a1 1 0 10-2 0A5 5 0 015 8a1 1 0 00-2 0 7.001 7.001 0 006 6.93V17H6a1 1 0 100 2h8a1 1 0 100-2h-3v-2.07z" clip-rule="evenodd"></path></svg>
                            </button>
                        @else
                            <button class="il-nav-btn" disabled>
                                <span>Waiting for voice...</span>
                            </button>
                        @endif
                    @endif
                </div>
            @endif
            </div>
        </div>
    @endif
</div>
