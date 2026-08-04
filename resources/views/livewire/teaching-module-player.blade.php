<div>
    @if ($game && $totalSlides > 0 && $currentSlide)
        <style>
            .tm-container {
                max-width: 100%;
                margin: 0 auto;
                background-color: #ffffff;
                border-radius: 2rem;
                overflow: hidden;
                box-shadow: 0 20px 50px rgba(0,0,0,0.1);
                font-family: ui-sans-serif, system-ui, sans-serif;
                border: 1px solid #f1f5f9;
            }
            .tm-header {
                padding: 1.5rem;
                background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);
                color: white;
                text-align: center;
                position: relative;
            }
            .tm-progress {
                height: 6px;
                background-color: rgba(255,255,255,0.2);
                border-radius: 3px;
                margin-top: 1rem;
                overflow: hidden;
            }
            .tm-progress-inner {
                height: 100%;
                background-color: white;
                transition: width 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .tm-content {
                padding: 2rem;
            }
            .tm-image-container {
                width: 100%;
                aspect-ratio: 16/9;
                background-color: #f8fafc;
                border-radius: 1.5rem;
                overflow: hidden;
                margin-bottom: 2rem;
                border: 2px solid #f1f5f9;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .tm-image-container img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            .tm-text-section {
                text-align: center;
                margin-bottom: 2rem;
            }
            .tm-malayalam {
                font-size: 1.5rem;
                font-weight: 700;
                color: #0f172a;
                margin-bottom: 0.75rem;
                line-height: 1.4;
            }
            .tm-english {
                font-size: 1.25rem;
                font-weight: 500;
                color: #64748b;
                font-style: italic;
            }
            .tm-controls {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
            }
            .tm-nav-btn {
                width: 50px;
                height: 50px;
                border-radius: 50%;
                border: none;
                background-color: #f1f5f9;
                color: #334155;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.2s;
            }
            .tm-nav-btn:hover:not(:disabled) {
                background-color: #e2e8f0;
                transform: scale(1.1);
            }
            .tm-nav-btn:disabled {
                opacity: 0.3;
                cursor: not-allowed;
            }
            .tm-audio-btn {
                background-color: #0ea5e9;
                color: white;
                padding: 1rem 2rem;
                border: none;
                border-radius: 1rem;
                font-weight: 700;
                display: flex;
                align-items: center;
                gap: 0.75rem;
                cursor: pointer;
                transition: all 0.2s;
                box-shadow: 0 10px 15px -3px rgba(14, 165, 233, 0.3);
            }
            .tm-audio-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 20px 25px -5px rgba(14, 165, 233, 0.4);
            }
            .tm-audio-btn:active {
                transform: translateY(0);
            }
            .tm-slide-indicator {
                position: absolute;
                top: 1.5rem;
                right: 1.5rem;
                background: rgba(255,255,255,0.2);
                padding: 0.25rem 0.75rem;
                border-radius: 9999px;
                font-size: 0.75rem;
                font-weight: 700;
            }
            [x-cloak] { display: none !important; }
        </style>

        <div class="tm-container">
            <div class="tm-header">
                <h3 style="font-size: 1.25rem; font-weight: 800; margin: 0;">{{ $game->name }}</h3>
                <div class="tm-slide-indicator">{{ $currentIndex + 1 }} / {{ $totalSlides }}</div>
                <div class="tm-progress">
                    <div class="tm-progress-inner" style="width: {{ (($currentIndex + 1) / $totalSlides) * 100 }}%"></div>
                </div>
            </div>

            <div class="tm-content">
                @php
                    $imageUrl = null;
                    if (!empty($currentSlide['image'])) {
                        $imageUrl = asset('storage/' . $currentSlide['image']);
                    }

                    $audioUrl = null;
                    if (!empty($currentSlide['audio'])) {
                        $audioUrl = asset('storage/' . $currentSlide['audio']);
                    }
                @endphp

                @if ($imageUrl)
                    <div class="tm-image-container">
                        <img src="{{ $imageUrl }}" alt="Teaching Image">
                    </div>
                @endif

                <div class="tm-text-section">
                    <p class="tm-malayalam">{{ $currentSlide['malayalam_text'] }}</p>
                    <p class="tm-english">"{{ $currentSlide['english_text'] }}"</p>
                </div>

                <div class="tm-controls">
                    <button wire:click="prevSlide" class="tm-nav-btn" {{ $currentIndex === 0 ? 'disabled' : '' }}>
                        <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </button>

                    <div x-data="{ 
                            audio: null, 
                            playing: false,
                            playAudio() {
                                if (this.audio) {
                                    this.audio.pause();
                                    this.audio.currentTime = 0;
                                }
                                this.audio = new Audio('{{ $audioUrl }}');
                                this.audio.onplay = () => this.playing = true;
                                this.audio.onended = () => this.playing = false;
                                this.audio.onpause = () => this.playing = false;
                                this.audio.play();
                            }
                        }" 
                        x-init="$watch('$wire.currentIndex', () => { if(audio) { audio.pause(); playing = false; } })">
                        
                        @if ($audioUrl)
                            <button @click="playAudio()" class="tm-audio-btn">
                                <template x-if="!playing">
                                    <svg style="width: 24px; height: 24px;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.707.707L4.586 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.586l3.707-3.707a1 1 0 011.09-.217zM14.657 2.929a1 1 0 011.414 0A9.972 9.972 0 0119 10a9.972 9.972 0 01-2.929 7.071 1 1 0 01-1.414-1.414A7.971 7.971 0 0017 10c0-2.21-.894-4.208-2.343-5.657a1 1 0 010-1.414zm-2.829 2.828a1 1 0 011.415 0A5.983 5.983 0 0115 10a5.984 5.984 0 01-1.757 4.243 1 1 0 01-1.415-1.415A3.984 3.984 0 0013 10a3.983 3.983 0 00-1.171-2.828 1 1 0 010-1.415z" clip-rule="evenodd"></path></svg>
                                </template>
                                <template x-if="playing">
                                    <svg class="animate-pulse" style="width: 24px; height: 24px;" fill="currentColor" viewBox="0 0 24 24"><path d="M11 5H8v14h3V5zm5 0h-3v14h3V5z"/></svg>
                                </template>
                                <span x-text="playing ? 'Now Playing...' : 'Listen Now'"></span>
                            </button>
                        @else
                           <div style="width: 150px;"></div> {{-- Spacer --}}
                        @endif
                    </div>

                    <button wire:click="nextSlide" class="tm-nav-btn" {{ $currentIndex === $totalSlides - 1 ? 'disabled' : '' }}>
                        <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </button>
                </div>

                <div style="margin-top: 1.5rem; display: flex; justify-content: center; gap: 0.5rem;">
                    @foreach ($game->slides as $idx => $slide)
                        <div wire:click="goToSlide({{ $idx }})" 
                             style="width: 8px; height: 8px; border-radius: 50%; cursor: pointer; transition: all 0.3s; background-color: {{ $currentIndex === $idx ? '#0ea5e9' : '#e2e8f0' }}; transform: {{ $currentIndex === $idx ? 'scale(1.5)' : 'scale(1)' }}"></div>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        <div style="padding: 2rem; background-color: #f8fafc; border-radius: 1rem; text-align: center; color: #64748b; font-weight: 600; border: 2px dashed #e2e8f0;">
            No teaching slides available.
        </div>
    @endif
</div>
