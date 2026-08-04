@php
    $contentBlocks = $blocks ?? ($getRecord ? $getRecord()->content_blocks : []);

    // Navigation Logic
    $unit = $unit ?? $record ?? ($getRecord ? $getRecord() : null);
    if ($unit) {
        $navNext = $unit->nextUnit();
        $navPrev = $unit->previousUnit();
    } else {
        $navNext = null;
        $navPrev = null;
    }
    
    $isPublic = $isPublic ?? false;
    $isStudentPanel = request()->is('student/*') || request()->routeIs('filament.student.*');
    $libraryRoute = $isPublic 
        ? route('public.library') 
        : (\Illuminate\Support\Facades\Route::has('filament.student.pages.library') 
            ? route('filament.student.pages.library') 
            : route('filament.student.pages.dashboard'));
@endphp

<div class="premium-lesson-wrapper font-sans" style="display: flex; flex-direction: column; align-items: center; width: 100%;">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap');

        :root {
            --premium-shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
            --premium-glow: 0 0 20px rgba(79, 70, 229, 0.15);
        }

        .font-sans { font-family: 'Outfit', sans-serif !important; }

        .premium-lesson-wrapper {
            position: relative;
            width: 100% !important;
            min-height: 100vh;
            background-color: #0f172a !important;
            background-image: 
                radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), 
                radial-gradient(at 50% 0%, hsla(225,39%,30%,1) 0, transparent 50%), 
                radial-gradient(at 100% 0%, hsla(339,49%,30%,1) 0, transparent 50%) !important;
            background-attachment: scroll !important;
            background-size: cover;
            margin: 0 auto;
            padding: 3rem 1rem 8rem 1rem;
            overflow: visible;
            display: flex;
            flex-direction: column;
            align-items: center; 
            z-index: 0;
        }

        {{-- Typography Refinement --}}
        .prose em { font-style: italic !important; }
        .prose strong { font-weight: 800 !important; }
        .prose u { text-decoration: underline !important; }
        
        .prose h1, .prose h2, .prose h3, .prose h4, .prose h5, .prose h6 { color: inherit !important; font-weight: 950 !important; letter-spacing: -0.04em !important; }
        .prose h1 { font-size: 1.85rem !important; }
        .prose h2 { font-size: 1.5rem !important; }
        .prose h3 { font-size: 1.25rem !important; }
        .prose p { line-height: 1.6 !important; font-weight: 500 !important; font-size: 1.1rem !important; }

        .text-indigo { color: #6366f1 !important; }
        .text-emerald { color: #10b981 !important; }
        .text-rose { color: #f43f5e !important; }
        .text-blue { color: #3b82f6 !important; }
        .text-slate { color: #64748b !important; }

        {{-- Premium Card System --}}
        .lesson-master-card { 
            max-width: 52rem !important; 
            width: 95% !important;
            margin: 0 auto 1.5rem auto !important; 
            background: rgba(255, 255, 255, 0.7) !important;
            backdrop-filter: blur(40px) saturate(180%) !important;
            -webkit-backdrop-filter: blur(40px) saturate(180%) !important;
            border-radius: 3.5rem !important; 
            box-shadow: 
                0 0 0 1px rgba(255,255,255,0.4) inset,
                0 40px 100px -20px rgba(0,0,0,0.1) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            overflow: visible !important;
            transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1) !important;
            position: relative;
            z-index: 10;
            padding-bottom: 2rem;
        }

        .iridescent-border {
            position: absolute;
            inset: 0;
            border-radius: inherit;
            pointer-events: none;
            border: 1.5px solid transparent;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.2), rgba(244, 63, 94, 0.2), rgba(16, 185, 129, 0.2), rgba(99, 102, 241, 0.2)) border-box;
            -webkit-mask: linear-gradient(#fff 0 0) padding-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            opacity: 0.4;
        }

        .block-separator {
            height: 1px;
            width: 20%;
            margin: 0.25rem auto;
            background: linear-gradient(90deg, transparent, rgba(99, 102, 241, 0.06) 50%, transparent);
            opacity: 0.3;
        }

        {{-- Lego Block System --}}
        .lego-block, .interactive-pill {
            width: 95%;
            max-width: 550px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.4) !important;
            backdrop-filter: blur(12px) !important;
            -webkit-backdrop-filter: blur(12px) !important;
            border: 1px solid rgba(255, 255, 255, 0.5) !important;
            border-radius: 2.25rem !important;
            box-shadow: 0 10px 30px -5px rgba(0,0,0,0.03) !important;
            padding: 1.25rem 1.25rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
        }

        .lego-block:hover, .interactive-pill:hover {
            transform: translateY(-2px);
            background: rgba(255, 255, 255, 0.5) !important;
            box-shadow: 0 15px 40px -10px rgba(0,0,0,0.05) !important;
        }

        {{-- Navigation Bar Re-Design --}}
        .nav-pill-wrapper {
            position: fixed;
            bottom: 2.5rem;
            left: 0;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10000;
            pointer-events: none;
        }

        .nav-pill {
            background: rgba(15, 23, 42, 0.8) !important;
            backdrop-filter: blur(25px) saturate(150%) !important;
            -webkit-backdrop-filter: blur(25px) saturate(150%) !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            border-radius: 9999px !important;
            padding: 0.625rem;
            display: flex;
            align-items: center;
            gap: 0.625rem;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5) !important;
            pointer-events: auto;
            width: 95%;
            max-width: 400px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 0.875rem 1.5rem;
            border-radius: 9999px;
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
            font-weight: 800;
            text-decoration: none;
            transition: 0.3s;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .nav-link:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.1);
        }

        .nav-refresh-btn {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            color: white;
            padding: 0.875rem 2rem;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            gap: 10px;
            border: none;
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            cursor: pointer;
            box-shadow: 0 12px 24px -6px rgba(99, 102, 241, 0.4) !important;
            transition: all 0.3s;
        }

        .nav-refresh-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 15px 30px -6px rgba(99, 102, 241, 0.5) !important;
        }

        {{-- Widget Specific Improvements --}}
        .interactive-pill {
            flex-direction: row !important;
            padding: 0.85rem 1.5rem !important;
            gap: 1.25rem !important;
            max-width: 500px !important;
        }

        /* See English / Malayalam Toggle Buttons */
        .transcript-toggle-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.25rem;
            border-radius: 99px;
            font-size: 0.85rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid transparent;
        }

        .transcript-toggle-btn:hover {
            transform: translateY(-1px);
        }

        .transcript-toggle-btn:active {
            transform: translateY(0);
        }

        /* See English specific styles */
        .btn-english-toggle {
            background: #00c2e8 !important;
            border-color: #00c2e8 !important;
            color: #0f172a !important;
            box-shadow: 0 4px 10px rgba(0, 194, 232, 0.15);
        }

        .btn-english-toggle:hover {
            background: #00b0d4 !important;
            box-shadow: 0 6px 14px rgba(0, 194, 232, 0.25);
        }

        .btn-english-toggle.is-active {
            background: #00a0c2 !important;
            border-color: #00a0c2 !important;
            color: #0f172a !important;
        }

        /* See Malayalam specific styles */
        .btn-malayalam-toggle {
            background: #10b981 !important;
            border-color: #10b981 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.15);
        }

        .btn-malayalam-toggle:hover {
            background: #059669 !important;
            box-shadow: 0 6px 14px rgba(16, 185, 129, 0.25);
        }

        .btn-malayalam-toggle.is-active {
            background: #047857 !important;
            border-color: #047857 !important;
            color: #ffffff !important;
        }

        /* Pulsing Glow Animations for Speech/Recording states */
        @keyframes mic-pulse-prep {
            0% { box-shadow: 0 0 0 0 rgba(234, 179, 8, 0.7); }
            70% { box-shadow: 0 0 0 15px rgba(234, 179, 8, 0); }
            100% { box-shadow: 0 0 0 0 rgba(234, 179, 8, 0); }
        }
        @keyframes mic-pulse-listen {
            0% { box-shadow: 0 0 0 0 rgba(244, 63, 94, 0.7); }
            70% { box-shadow: 0 0 0 20px rgba(244, 63, 94, 0); }
            100% { box-shadow: 0 0 0 0 rgba(244, 63, 94, 0); }
        }
        .mic-preparing {
            animation: mic-pulse-prep 1.5s infinite !important;
        }
        .mic-listening {
            animation: mic-pulse-listen 1.2s infinite !important;
        }
    </style>

    {{-- All Classes Navigation Button (Top) --}}
    @if(!request()->is('student/*'))
        <div style="display: flex; justify-content: center; margin-bottom: -0.75rem; position: relative; z-index: 100;">
            <a href="{{ $libraryRoute }}" 
               style="display: inline-flex; align-items: center; gap: 0.625rem; padding: 0.5rem 1.5rem 0.5rem 0.6rem; background: rgba(255,255,255,0.8); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.4); border-radius: 9999px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); text-decoration: none;">
                <div style="width: 32px; height: 32px; background: #eef2ff; color: #4f46e5; border-radius: 9999px; display: flex; align-items: center; justify-content: center;">
                    <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7" /></svg>
                </div>
                <span style="font-weight: 800; color: #1f2937; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.12em; white-space: nowrap;">All Classes</span>
            </a>
        </div>
    @endif

    <div class="lesson-master-card mx-auto">
        
        <div class="iridescent-border"></div>

        <div class="flex flex-col" style="padding-top: 1.5rem;">
            {{-- Lesson Title --}}
            <div style="text-align: center; margin-bottom: 2rem; padding: 0 2rem;">
                <h1 style="font-size: 2rem; font-weight: 950; color: #0f172a; line-height: 1.1; letter-spacing: -0.05em; margin: 0;">{{ $unit->title ?? ($record->title ?? 'Untitled Lesson') }}</h1>
                <div style="height: 5px; width: 80px; background: linear-gradient(90deg, #6366f1, #a855f7); margin: 0.75rem auto 0 auto; border-radius: 99px; opacity: 0.4;"></div>
            </div>
            @foreach($contentBlocks as $index => $block)
                @if($index > 0)
                    <div class="block-separator"></div>
                @endif
                <div class="block-section" style="width: 100%; display: flex; flex-direction: column; align-items: center;">
                    
                    {{-- 1. VIDEO BLOCK --}}
                    @if($block['type'] === 'video')
                        @php
                            $data = $block['data'];
                            $vUrl = null;
                            if (!empty($data['video_path'])) {
                                $vUrl = asset('storage/' . $data['video_path']);
                            } elseif (!empty($data['media_item_id'])) {
                                $media = \App\Models\MediaItem::find($data['media_item_id']);
                                $vUrl = $media ? asset('storage/' . $media->path) : null;
                            }
                            $meaningMalayalam = $data['meaning_malayalam'] ?? '';
                        @endphp
                        <div class="video-section w-full" x-data="{ showEnglish: false, showMalayalam: false }" style="padding: 0.25rem 1.5rem; max-width: 700px; margin: 0 auto; display: flex; flex-direction: column; gap: 0.75rem;">
                            @if(!empty($data['instructions']))
                                <div style="text-align: center; width: 100%;">
                                    <p style="font-size: 0.95rem; font-weight: 700; color: #1e293b; margin: 0 0 0.5rem 0; line-height: 1.4;">{{ $data['instructions'] }}</p>
                                </div>
                            @endif

                            <div style="border-radius: 2.5rem; overflow: hidden; background: #000; aspect-ratio: 16/9; position: relative; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3); border: 2px solid rgba(255,255,255,0.1);">
                                @if(!empty($data['bunny_id']))
                                    <x-bunny-player :videoId="$data['bunny_id']" />
                                @elseif($vUrl)
                                    <video controls style="width:100%; height:100%; object-fit:cover;">
                                        <source src="{{ $vUrl }}" type="video/mp4">
                                    </video>
                                @endif
                            </div>
                            
                            {{-- Toggleable Transcript Section --}}
                            @if(!empty($data['transcript']))
                                <div style="width: 100%; display: flex; flex-direction: column; gap: 0.75rem; align-items: center; margin-top: 0.25rem;">
                                    <!-- Toggle Buttons Container -->
                                    <div style="display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap; width: 100%;">
                                        <!-- See English Toggle -->
                                        <button type="button" 
                                                @click="showEnglish = !showEnglish"
                                                class="transcript-toggle-btn btn-english-toggle"
                                                :class="{ 'is-active': showEnglish }">
                                            <!-- Icon -->
                                            <svg x-show="!showEnglish" style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            <svg x-show="showEnglish" style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3" x-cloak><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.893 7.893L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                                            <span x-text="showEnglish ? 'Hide English' : 'See English'">See English</span>
                                        </button>

                                        <!-- See Malayalam Toggle -->
                                        @if(!empty($meaningMalayalam))
                                            <button type="button" 
                                                    @click="showMalayalam = !showMalayalam"
                                                    class="transcript-toggle-btn btn-malayalam-toggle"
                                                    :class="{ 'is-active': showMalayalam }">
                                                <!-- Icon -->
                                                <svg x-show="!showMalayalam" style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                <svg x-show="showMalayalam" style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3" x-cloak><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.893 7.893L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                                                <span x-text="showMalayalam ? 'Hide Malayalam' : 'See Malayalam'">See Malayalam</span>
                                            </button>
                                        @endif
                                    </div>

                                    <!-- English Transcript Content -->
                                    <div x-show="showEnglish" 
                                         x-transition:enter="transition ease-out duration-300"
                                         x-transition:enter-start="opacity-0 transform scale-95"
                                         x-transition:enter-end="opacity-100 transform scale-100"
                                         style="width: 100%; display: inline-block; background: rgba(15, 23, 42, 0.85); border: 1px solid rgba(255, 255, 255, 0.15); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border-radius: 0.85rem; padding: 0.7rem 1.4rem; box-shadow: 0 12px 35px -12px rgba(0,0,0,0.5);">
                                        <div style="color: #ffffff; font-weight: 800; font-size: 1.05rem; line-height: 1.6; text-align: center; white-space: pre-line; text-shadow: 0 1px 2px rgba(0,0,0,0.5);">{!! nl2br(e(trim($data['transcript']))) !!}</div>
                                    </div>

                                    <!-- Malayalam Translation Content -->
                                    @if(!empty($meaningMalayalam))
                                        <div x-show="showMalayalam" 
                                             x-transition:enter="transition ease-out duration-300"
                                             x-transition:enter-start="opacity-0 transform scale-95"
                                             x-transition:enter-end="opacity-100 transform scale-100"
                                             style="width: 100%; display: inline-block; background: rgba(15, 23, 42, 0.85); border: 1px solid rgba(16, 185, 129, 0.4); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border-radius: 0.85rem; padding: 0.7rem 1.4rem; box-shadow: 0 12px 35px -12px rgba(0,0,0,0.5);">
                                            <div style="color: #34d399; font-weight: 800; font-size: 1.05rem; line-height: 1.6; text-align: center; white-space: pre-line; text-shadow: 0 1px 2px rgba(0,0,0,0.5);">{!! nl2br(e(trim($meaningMalayalam))) !!}</div>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>

                    {{-- 2. QUIZ BLOCK --}}
                    @elseif($block['type'] === 'quiz')
                        <div x-data="{ selectedOption: null, showResult: false, quizSolved: false, attemptedWrong: [] }" 
                             style="margin: 0.25rem 0; width: 100%; display: justify-content: center;">
                            <div class="lego-block" style="gap: 2rem;">
                                <div style="text-align: center;">
                                    <span style="font-size: 11px; font-weight: 950; color: #6366f1; text-transform: uppercase; letter-spacing: 0.35rem; display: block; margin-bottom: 0.75rem;">Knowledge Check</span>
                                    @if(!empty($block['data']['instructions']))
                                        <p style="font-size: 0.95rem; font-weight: 700; color: #1e293b; margin-bottom: 0.5rem; line-height: 1.4;">{{ $block['data']['instructions'] }}</p>
                                    @endif
                                    <h3 style="font-size: 1.85rem; font-weight: 950; color: #0f172a; line-height: 1.2; letter-spacing: -0.03em;">{{ $block['data']['question'] ?? 'Choose the correct answer' }}</h3>
                                </div>
                                
                                <div style="display: flex; flex-direction: column; gap: 12px; width: 100%; max-width: 480px; margin: 0 auto;">
                                    @foreach($block['data']['options'] ?? [] as $option)
                                        @php
                                            $optAudioUrl = !empty($option['custom_audio_url']) 
                                                ? (str_starts_with($option['custom_audio_url'], 'http') ? $option['custom_audio_url'] : asset('storage/' . $option['custom_audio_url']))
                                                : (!empty($option['audio_path']) ? asset('storage/' . $option['audio_path']) : null);
                                        @endphp
                                        <button @click="selectedOption = {{ json_encode($option) }}; 
                                                        showResult = true; 
                                                        if(selectedOption.is_correct) { 
                                                            quizSolved = true; 
                                                            if(window.confetti) { confetti({ particleCount: 150, spread: 70, origin: { y: 0.6 } }); }
                                                            @if($optAudioUrl)
                                                                setTimeout(() => { 
                                                                    let a = new Audio('{{ $optAudioUrl }}');
                                                                    a.play().catch(e => console.log('Audio autoplay blocked'));
                                                                }, 500);
                                                             @elseif(!empty($option['speech_text']))
                                                                 setTimeout(() => {
                                                                     if (window.speechSynthesis.speaking) window.speechSynthesis.cancel();
                                                                     const u = new SpeechSynthesisUtterance('{{ addslashes($option['speech_text']) }}');
                                                                     u.lang = '{{ $option['speech_accent'] ?? 'en-US' }}';
                                                                     const voices = window.speechSynthesis.getVoices();
                                                                     let matchedVoice = null;
                                                                     const gender = '{{ $option['speech_gender'] ?? 'any' }}';
                                                                     if (gender !== 'any') {
                                                                         matchedVoice = voices.find(v => v.lang.startsWith('{{ $option['speech_accent'] ?? 'en-US' }}') && v.name.toLowerCase().includes(gender));
                                                                     }
                                                                     if (!matchedVoice) {
                                                                         matchedVoice = voices.find(v => v.lang.startsWith('{{ $option['speech_accent'] ?? 'en-US' }}'));
                                                                     }
                                                                     if (matchedVoice) u.voice = matchedVoice;
                                                                     window.speechSynthesis.speak(u);
                                                                 }, 500);
                                                             @endif
                                                        } else {
                                                            if(!attemptedWrong.includes(selectedOption.text)) {
                                                                attemptedWrong.push(selectedOption.text);
                                                            }
                                                        }"
                                                :disabled="quizSolved"
                                                style="width: 100%; padding: 1.25rem 1.5rem; border-radius: 1.75rem; text-align: left; display: flex; align-items: center; justify-content: space-between; border: 2px solid; transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); cursor: pointer;"
                                                :style="{
                                                    background: quizSolved && {{ $option['is_correct'] ? 'true' : 'false' }} ? 'rgba(16, 185, 129, 0.1)' : 
                                                               (!quizSolved && attemptedWrong.includes('{{ addslashes($option['text']) }}') && !{{ $option['is_correct'] ? 'true' : 'false' }} ? 'rgba(244, 63, 94, 0.1)' : 'rgba(255,255,255,0.6)'),
                                                    borderColor: quizSolved && {{ $option['is_correct'] ? 'true' : 'false' }} ? '#10b981' : 
                                                               (!quizSolved && attemptedWrong.includes('{{ addslashes($option['text']) }}') && !{{ $option['is_correct'] ? 'true' : 'false' }} ? '#f43f5e' : 'rgba(255,255,255,0.8)'),
                                                    transform: (selectedOption?.text === '{{ addslashes($option['text']) }}') ? 'scale(1.02)' : 'scale(1)',
                                                    boxShadow: quizSolved && {{ $option['is_correct'] ? 'true' : 'false' }} ? '0 10px 20px -10px rgba(16, 185, 129, 0.3)' : 'none'
                                                }">
                                            <span style="font-size: 16px; font-weight: 800; color: #1e293b;">{{ $option['text'] }}</span>
                                            
                                            <template x-if="quizSolved && {{ $option['is_correct'] ? 'true' : 'false' }}">
                                                <div style="background: #10b981; border-radius: 9999px; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(16, 185, 129, 0.4);">
                                                    <svg style="width:14px; height:14px; color: #fff;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="4"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                                                </div>
                                            </template>

                                            <template x-if="!quizSolved && attemptedWrong.includes('{{ addslashes($option['text']) }}') && !{{ $option['is_correct'] ? 'true' : 'false' }}">
                                                <div style="background: #f43f5e; border-radius: 9999px; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(244, 63, 94, 0.4);">
                                                    <svg style="width:12px; height:12px; color: #fff;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                </div>
                                            </template>
                                        </button>
                                    @endforeach
                                </div>

                                {{-- Reveal Audio Player for Correct Answer --}}
                                @foreach($block['data']['options'] ?? [] as $option)
                                    @php
                                        $optAudioUrl = !empty($option['custom_audio_url']) 
                                            ? (str_starts_with($option['custom_audio_url'], 'http') ? $option['custom_audio_url'] : asset('storage/' . $option['custom_audio_url']))
                                            : (!empty($option['audio_path']) ? asset('storage/' . $option['audio_path']) : null);
                                    @endphp
                                    @if($option['is_correct'])
                                        @if($optAudioUrl)
                                            <div x-show="quizSolved" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translateY(10px)" x-transition:enter-end="opacity-100 translateY(0)"
                                                 style="width: 100%; display: flex; justify-content: center; margin-top: 1rem;">
                                                <div class="interactive-pill" style="background: rgba(16, 185, 129, 0.1) !important; border-color: rgba(16, 185, 129, 0.3) !important;">
                                                    <button x-data="{ playing: false }" @click="playing = !playing; playing ? $refs.quizAud{{ $loop->parent->index or $index }}{{ $loop->index }}.play() : $refs.quizAud{{ $loop->parent->index or $index }}{{ $loop->index }}.pause()" 
                                                            style="width: 50px; height: 50px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none; border-radius: 9999px; color: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.3s; box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2);">
                                                        <svg x-show="!playing" style="width:22px; height:22px;" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                                        <svg x-show="playing" style="width:22px; height:22px;" fill="currentColor" viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                                                        <audio x-ref="quizAud{{ $loop->parent->index or $index }}{{ $loop->index }}" @ended="playing = false" @pause="playing = false" style="display:none;"><source src="{{ $optAudioUrl }}" type="audio/mpeg"></audio>
                                                    </button>
                                                    <div style="display: flex; flex-direction: column; line-height: 1.3;">
                                                        <span style="font-size: 10px; font-weight: 950; color: #059669; letter-spacing: 0.2rem; text-transform: uppercase;">Correct!</span>
                                                        <span style="font-size: 16px; font-weight: 900; color: #1e293b; letter-spacing: -0.01em;">Listen to Audio</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @elseif(!empty($option['speech_text']))
                                            <div x-show="quizSolved" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translateY(10px)" x-transition:enter-end="opacity-100 translateY(0)"
                                                 style="width: 100%; display: flex; justify-content: center; margin-top: 1rem;">
                                                <div class="interactive-pill" style="background: rgba(16, 185, 129, 0.1) !important; border-color: rgba(16, 185, 129, 0.3) !important;">
                                                    <button x-data="{ playing: false }" @click="playing = !playing; if(playing) { if(window.speechSynthesis.speaking) window.speechSynthesis.cancel(); const u = new SpeechSynthesisUtterance('{{ addslashes($option['speech_text']) }}'); u.lang = '{{ $option['speech_accent'] ?? 'en-US' }}'; const matchedVoice = window.getAmterSpeechVoice('{{ $option['speech_accent'] ?? 'en-US' }}', '{{ $option['speech_gender'] ?? 'any' }}'); if(matchedVoice) u.voice = matchedVoice; u.onend = () => { playing = false; }; window.speechSynthesis.speak(u); } else { window.speechSynthesis.cancel(); }" 
                                                            style="width: 50px; height: 50px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none; border-radius: 9999px; color: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.3s; box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2);">
                                                        <svg x-show="!playing" style="width:22px; height:22px;" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                                        <svg x-show="playing" style="width:22px; height:22px;" fill="currentColor" viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                                                    </button>
                                                    <div style="display: flex; flex-direction: column; line-height: 1.3;">
                                                        <span style="font-size: 10px; font-weight: 950; color: #059669; letter-spacing: 0.2rem; text-transform: uppercase;">Correct!</span>
                                                        <span style="font-size: 16px; font-weight: 900; color: #1e293b; letter-spacing: -0.01em;">{{ $option['speech_text'] }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                @endforeach
                            </div>
                        </div>

                    {{-- 3. RICH TEXT BLOCK --}}

                    @elseif($block['type'] === 'rich_text')
                        @php
                            $textColor = $block['data']['text_color'] ?? 'default';
                            $alignment = $block['data']['alignment'] ?? 'left';
                            $colorHex = match($textColor) {
                                'indigo' => '#6366f1',
                                'emerald' => '#10b981',
                                'rose' => '#f43f5e',
                                'blue' => '#3b82f6',
                                default => '#334155',
                            };
                        @endphp
                        <div style="padding: 0 1.5rem; margin: 1rem 0;">
                            <div class="prose max-w-none" style="
                                background: #ffffff !important;
                                border-radius: 2rem !important;
                                padding: 2rem 2.5rem !important;
                                text-align: {{ $alignment }};
                                color: {{ $colorHex }} !important;
                                --tw-prose-body: {{ $colorHex }} !important;
                                --tw-prose-headings: {{ $colorHex }} !important;
                                --tw-prose-bold: {{ $colorHex }} !important;
                                --tw-prose-bullets: {{ $colorHex }} !important;
                                --tw-prose-quotes: {{ $colorHex }} !important;
                                --tw-prose-counters: {{ $colorHex }} !important;
                                --tw-prose-links: #4f46e5 !important;
                                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.02) !important;
                                border: 1px solid rgba(0, 0, 0, 0.04) !important;
                            ">
                                {!! $block['data']['content'] !!}
                            </div>
                        </div>

                    @elseif($block['type'] === 'image')
                        @php
                            $mediaItemId = !empty($block['data']['media_item_selection']) ? $block['data']['media_item_selection'] : null;
                            $cUrl = !empty($block['data']['custom_url']) ? $block['data']['custom_url'] : null;
                            if ($mediaItemId) {
                                $latestItem = \App\Models\MediaItem::find($mediaItemId);
                                if ($latestItem) {
                                    $cUrl = $latestItem->path;
                                }
                            }
                            if (!empty($cUrl) && !str_contains($cUrl, '/') && !str_starts_with($cUrl, 'http')) {
                                $cUrl = 'media-library/' . $cUrl;
                            }
                            $iUrl = !empty($cUrl) 
                                ? (str_starts_with($cUrl, 'http') ? $cUrl : (str_starts_with($cUrl, 'storage/') ? asset($cUrl) : asset('storage/' . $cUrl)))
                                : (!empty($block['data']['url']) ? asset('storage/' . $block['data']['url']) : null);
                        @endphp
                        @php
                            $imgPath = $cUrl;
                            if (empty($imgPath)) {
                                $imgPath = !empty($block['data']['url']) ? $block['data']['url'] : null;
                            }
                        @endphp
                        @if($iUrl)
                            <div style="width: 100%; display: flex; justify-content: center; margin: 0.25rem 0; padding: 0 1.5rem;">
                                <div style="max-width: 700px; width: 100%; border-radius: 2.5rem; overflow: hidden; background: #fff; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1); border: 1px solid rgba(255,255,255,0.4);">
                                    @if(!empty($imgPath) && !str_starts_with($imgPath, 'http'))
                                        <x-image :src="$imgPath" 
                                                 :width="760" 
                                                 :height="400" 
                                                 :crop="false"
                                                 alt="{{ $block['data']['alt'] ?? '' }}" />
                                    @else
                                        <img src="{{ $iUrl }}" alt="{{ $block['data']['alt'] ?? '' }}" style="width: 100%; display: block; height: auto;">
                                    @endif
                                </div>
                            </div>
                        @endif

                    @elseif($block['type'] === 'audio')
                        @php
                            $aUrl = !empty($block['data']['custom_url']) 
                                ? (str_starts_with($block['data']['custom_url'], 'http') ? $block['data']['custom_url'] : asset('storage/' . $block['data']['custom_url']))
                                : (!empty($block['data']['url']) ? asset('storage/' . $block['data']['url']) : null);
                        @endphp
                        @if($aUrl)
                            <div style="padding: 0.25rem 1.5rem; width: 100%; display: flex; flex-direction: column; align-items: center;">
                                <div class="interactive-pill">
                                    <button x-data="{ playing: false }" @click="playing = !playing; playing ? $refs.aud.play() : $refs.aud.pause()" 
                                            style="width: 60px; height: 60px; background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); border: none; border-radius: 9999px; color: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.3s; box-shadow: 0 10px 20px rgba(99,102,241,0.3);">
                                        <svg x-show="!playing" style="width:26px; height:26px;" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                        <svg x-show="playing" style="width:26px; height:26px;" fill="currentColor" viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                                        <audio x-ref="aud" @ended="playing = false" @pause="playing = false" style="display:none;"><source src="{{ $aUrl }}" type="audio/mpeg"></audio>
                                    </button>
                                    <div style="display: flex; flex-direction: column; line-height: 1.3;">
                                        <span style="font-size: 11px; font-weight: 950; color: #6366f1; letter-spacing: 0.25rem; text-transform: uppercase;">Listen Now</span>
                                        <span style="font-size: 18px; font-weight: 900; color: #1e293b; letter-spacing: -0.01em;">Audio Guide</span>
                                    </div>
                                </div>
                                @if(!empty($block['data']['transcript']))
                                    <div style="max-width: 600px; text-align: center; padding: 1.25rem 2rem 0 2rem;">
                                        <p style="font-size: 1.25rem; font-weight: 600; color: #475569; font-style: italic; line-height: 1.5; opacity: 0.8;">"{{ $block['data']['transcript'] }}"</p>
                                    </div>
                                @endif
                            </div>
                        @endif

                    @elseif($block['type'] === 'voice_match')
                        @php
                            $data = $block['data'];
                            $targets = (array)($data['phrase'] ?? []);
                            $btnLabel = $data['button_label'] ?? 'Tap to Speak';
                            $meaningMalayalam = $data['meaning_malayalam'] ?? '';
                        @endphp
                        <div x-data="voiceMatcher({{ json_encode(array_values(array_unique($targets))) }}, {{ ($data['hide_phrase'] ?? true) ? 'false' : 'true' }})" style="margin: 0.25rem 0; width: 100%; display: flex; justify-content: center;">
                            <div class="lego-block" style="gap: 1.5rem; width: 95%; max-width: 550px;">
                                <div style="text-align: center; position: relative; width: 100%;">
                                    <span style="font-size: 11px; font-weight: 950; color: #6366f1; text-transform: uppercase; letter-spacing: 0.35rem; margin-bottom: 0.75rem; display: block;">Voice Check</span>
                                    
                                    @if(!empty($data['instructions']))
                                        <p style="font-size: 0.95rem; font-weight: 700; color: #1e293b; margin-bottom: 0.5rem; line-height: 1.4;">{{ $data['instructions'] }}</p>
                                    @endif

                                    <div style="width: 100%; display: flex; flex-direction: column; gap: 0.75rem; align-items: center; margin-top: 0.75rem;">
                                        <!-- Toggle Buttons Container -->
                                        <div style="display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap; width: 100%;">
                                            <!-- See English Toggle -->
                                            <button type="button" 
                                                    @click="revealed = !revealed"
                                                    class="transcript-toggle-btn btn-english-toggle"
                                                    :class="{ 'is-active': revealed }">
                                                <!-- Icon -->
                                                <svg x-show="!revealed" style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                <svg x-show="revealed" style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3" x-cloak><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.893 7.893L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                                                <span x-text="revealed ? 'Hide English' : 'See English'">See English</span>
                                            </button>
 
                                            <!-- See Malayalam Toggle -->
                                            @if(!empty($meaningMalayalam))
                                                <button type="button" 
                                                        @click="showMalayalam = !showMalayalam"
                                                        class="transcript-toggle-btn btn-malayalam-toggle"
                                                        :class="{ 'is-active': showMalayalam }">
                                                    <!-- Icon -->
                                                    <svg x-show="!showMalayalam" style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                    <svg x-show="showMalayalam" style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3" x-cloak><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.893 7.893L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                                                    <span x-text="showMalayalam ? 'Hide Malayalam' : 'See Malayalam'">See Malayalam</span>
                                                </button>
                                            @endif
                                        </div>

                                        <!-- English Text Area (Hidden by default) -->
                                        @if(!empty($targets[0]))
                                            <div x-show="revealed" 
                                                 x-transition:enter="transition ease-out duration-300"
                                                 x-transition:enter-start="opacity-0 transform scale-95"
                                                 x-transition:enter-end="opacity-100 transform scale-100"
                                                 style="width: 100%; background: rgba(255, 255, 255, 0.5); border: 1px solid rgba(0, 0, 0, 0.04); border-radius: 1.5rem; padding: 1.25rem 1.5rem; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.02); margin-top: 0.5rem;">
                                                <h3 style="font-size: 2rem; font-weight: 950; color: #0f172a; letter-spacing: -0.03em; line-height: 1.1; margin: 0;">
                                                    "{{ implode(' / ', $targets) }}"
                                                </h3>
                                            </div>
                                        @endif

                                        <!-- Malayalam Meaning Area (Hidden by default) -->
                                        @if(!empty($meaningMalayalam))
                                            <div x-show="showMalayalam" 
                                                 x-transition:enter="transition ease-out duration-300"
                                                 x-transition:enter-start="opacity-0 transform scale-95"
                                                 x-transition:enter-end="opacity-100 transform scale-100"
                                                 style="width: 100%; background: rgba(255, 255, 255, 0.5); border: 1px solid rgba(16, 185, 129, 0.15); border-radius: 1.5rem; padding: 1.25rem 1.5rem; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.02);">
                                                <p style="font-size: 1.15rem; font-weight: 700; color: #059669; margin: 0; line-height: 1.6;">{{ $meaningMalayalam }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <button type="button" @click="status === 'listening' || status === 'preparing' ? stopVoice() : startVoice()" :disabled="status === 'success'"
                                        class="transition-all duration-300"
                                        :class="{ 'mic-preparing': status === 'preparing', 'mic-listening': status === 'listening' }"
                                        style="width: 5.5rem; height: 5.5rem; border-radius: 9999px; border: none; display: flex; align-items: center; justify-content: center; z-index: 10; cursor: pointer; transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);"
                                        :style="{ 
                                            background: status === 'listening' ? '#f43f5e' : (status === 'preparing' ? '#eab308' : (status === 'success' ? '#10b981' : 'linear-gradient(135deg, #6366f1 0%, #a855f7 100%)')),
                                            transform: status === 'listening' || status === 'preparing' ? 'scale(1.1)' : 'scale(1)',
                                            boxShadow: '0 15px 30px rgba(99,102,241,0.25)'
                                        }">
                                    <svg x-show="status !== 'success'" style="width: 2.75rem; height: 2.75rem; color: #fff;" fill="currentColor" viewBox="0 0 24 24"><path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/><path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/></svg>
                                    <svg x-show="status === 'success'" style="width: 3rem; height: 3rem; color: #fff;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="4"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                </button>

                                <div style="text-align: center; min-height: 50px; display: flex; flex-direction: column; justify-content: center; gap: 0.5rem; margin-top: 1rem;">
                                    <!-- Dynamic Score Pill -->
                                    <template x-if="score > 0">
                                        <div style="display: inline-flex; align-items: center; justify-content: center; margin-bottom: 0.25rem;">
                                            <span style="padding: 0.4rem 1.2rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 900; color: #fff; letter-spacing: 0.05em; text-transform: uppercase; transition: all 0.3s;"
                                                  :style="{
                                                      background: score === 100 ? 'linear-gradient(135deg, #10b981 0%, #059669 100%)' :
                                                                 (score >= 85 ? 'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)' :
                                                                 (score >= 50 ? 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)' :
                                                                                'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)')),
                                                      boxShadow: '0 4px 12px rgba(0,0,0,0.1)'
                                                  }"
                                                  x-text="score + '% ACCURATE'">
                                            </span>
                                        </div>
                                    </template>

                                    <span style="font-size: 13px; font-weight: 950; text-transform: uppercase; letter-spacing: 0.15em;"
                                          :style="{ color: status === 'success' ? '#10b981' : (status === 'error' ? '#f43f5e' : '#64748b') }"
                                          x-text="status === 'idle' ? '{{ $btnLabel }}' : (status === 'preparing' ? 'Connecting Mic...' : (status === 'listening' ? 'Speak Now...' : (status === 'success' ? (score === 100 ? 'Perfect Pronunciation!' : 'Great Pronunciation!') : 'Didn\'t catch that. Try again?')))"></span>
                                    <p x-show="recognizedText" style="font-size: 16px; font-weight: 800; font-style: italic; color: #6366f1; line-height: 1.4;" x-text="recognizedText"></p>
                                </div>
                            </div>
                        </div>


                    {{-- 8. CODE BLOCK --}}
                    @elseif($block['type'] === 'code')
                        <div style="width: 100%; padding: 1rem 1.5rem;">
                            <div style="background: #1e293b; color: #e2e8f0; padding: 2rem; border-radius: 2rem; font-family: 'JetBrains Mono', monospace; font-size: 14px; overflow-x: auto; box-shadow: 0 20px 50px rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.05);">
                                {!! $block['data']['code'] ?? '' !!}
                            </div>
                        </div>

                    {{-- 10. TEXT TO SPEECH (AI PRONUNCIATION BLOCK) --}}
                    @elseif($block['type'] === 'text_to_speech')
                        @php
                            $data = $block['data'];
                            $heading = $data['heading'] ?? 'AI Pronunciation Guide';
                            $defaultText = $data['text'] ?? '';
                            $accent = $data['default_accent'] ?? 'en-US';
                            $canInput = $data['student_input_allowed'] ?? false;
                            $speechGender = $data['speech_gender'] ?? 'any';
                            $meaningMalayalam = $data['meaning_malayalam'] ?? '';
                        @endphp

                        <div x-data="textToSpeech('{{ addslashes($defaultText) }}', '{{ $accent }}', '{{ $speechGender }}')" 
                             style="margin: 0.25rem 0; width: 100%; display: flex; justify-content: center;">
                            <div class="lego-block" style="gap: 1.5rem; width: 95%; max-width: 550px;">
                                
                                @if($data['show_headings'] ?? false)
                                    <div style="text-align: center; width: 100%;">
                                        <span style="font-size: 11px; font-weight: 950; color: #6366f1; text-transform: uppercase; letter-spacing: 0.35rem; display: block; margin-bottom: 0.5rem;">Interactive Audio</span>
                                        <p style="font-size: 1rem; font-weight: 500; color: #475569; margin: 0; line-height: 1.4;">{{ $heading }}</p>
                                    </div>
                                @endif

                                @if(!empty($data['instructions']))
                                    <div style="text-align: center; width: 100%;">
                                        <p style="font-size: 0.95rem; font-weight: 700; color: #1e293b; margin: 0 0 0.5rem 0; line-height: 1.4;">{{ $data['instructions'] }}</p>
                                    </div>
                                @endif

                                @if($canInput)
                                    <textarea x-model="text" 
                                              rows="3" 
                                              style="width: 100%; border-radius: 1.5rem; background: rgba(255, 255, 255, 0.7); border: 1.5px solid rgba(99, 102, 241, 0.2); color: #1e293b; padding: 1rem 1.25rem; font-size: 16px; font-weight: 600; outline: none; transition: all 0.3s;"
                                              placeholder="Type or paste text here to speak..."></textarea>
                                @else
                                    <div style="width: 100%; display: flex; flex-direction: column; gap: 0.75rem; align-items: center;">
                                        <!-- Toggle Buttons Container -->
                                        <div style="display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap; width: 100%;">
                                            <!-- See English Toggle -->
                                            <button type="button" 
                                                    @click="showEnglish = !showEnglish"
                                                    class="transcript-toggle-btn btn-english-toggle"
                                                    :class="{ 'is-active': showEnglish }">
                                                <!-- Icon -->
                                                <svg x-show="!showEnglish" style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                <svg x-show="showEnglish" style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3" x-cloak><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.893 7.893L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                                                <span x-text="showEnglish ? 'Hide English' : 'See English'">See English</span>
                                            </button>
 
                                            <!-- See Malayalam Toggle -->
                                            @if(!empty($meaningMalayalam))
                                                <button type="button" 
                                                        @click="showMalayalam = !showMalayalam"
                                                        class="transcript-toggle-btn btn-malayalam-toggle"
                                                        :class="{ 'is-active': showMalayalam }">
                                                    <!-- Icon -->
                                                    <svg x-show="!showMalayalam" style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                    <svg x-show="showMalayalam" style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3" x-cloak><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.893 7.893L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                                                    <span x-text="showMalayalam ? 'Hide Malayalam' : 'See Malayalam'">See Malayalam</span>
                                                </button>
                                            @endif
                                        </div>

                                        <!-- English Text Area (Hidden by default) -->
                                        <div x-show="showEnglish" 
                                             x-transition:enter="transition ease-out duration-300"
                                             x-transition:enter-start="opacity-0 transform scale-95"
                                             x-transition:enter-end="opacity-100 transform scale-100"
                                             style="width: 100%; background: rgba(255, 255, 255, 0.5); border: 1px solid rgba(0, 0, 0, 0.04); border-radius: 1.5rem; padding: 1.25rem 1.5rem; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.02);">
                                            <p style="font-size: 1.15rem; font-weight: 700; color: #334155; margin: 0; line-height: 1.5;" x-text="text"></p>
                                        </div>

                                        <!-- Malayalam Meaning Area (Hidden by default) -->
                                        @if(!empty($meaningMalayalam))
                                            <div x-show="showMalayalam" 
                                                 x-transition:enter="transition ease-out duration-300"
                                                 x-transition:enter-start="opacity-0 transform scale-95"
                                                 x-transition:enter-end="opacity-100 transform scale-100"
                                                 style="width: 100%; background: rgba(255, 255, 255, 0.5); border: 1px solid rgba(16, 185, 129, 0.15); border-radius: 1.5rem; padding: 1.25rem 1.5rem; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.02);">
                                                <p style="font-size: 1.15rem; font-weight: 700; color: #059669; margin: 0; line-height: 1.6;">{{ $meaningMalayalam }}</p>
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                <div style="display: flex; align-items: center; justify-content: center; width: 100%;">
                                    <button type="button" @click="speakNow()" 
                                            style="width: 55px; height: 55px; border-radius: 9999px; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.3s; background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); box-shadow: 0 10px 20px rgba(99, 102, 241, 0.25); flex-shrink: 0;">
                                        <svg x-show="!speaking" style="width: 22px; height: 22px; color: #fff;" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                        <svg x-show="speaking" style="width: 22px; height: 22px; color: #fff;" fill="currentColor" viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                    {{-- 11. DICTATION TEST BLOCK --}}
                    @elseif($block['type'] === 'dictation_test')
                        @php
                            $data = $block['data'];
                            $correctSentence = $data['correct_sentence'] ?? '';
                            $accent = $data['accent'] ?? 'en-US';
                            $successMsg = $data['success_message'] ?? 'Perfect spelling and listening!';
                            $voiceGender = $data['speech_gender'] ?? 'any';
                        @endphp

                        <div x-data="dictationTest('{{ addslashes($correctSentence) }}', '{{ $accent }}', '{{ $voiceGender }}')" 
                             style="margin: 0.25rem 0; width: 100%; display: flex; justify-content: center;">
                            <div class="lego-block" style="gap: 1.5rem; width: 95%; max-width: 550px;">
                                
                                <div style="text-align: center; width: 100%;">
                                    <span style="font-size: 11px; font-weight: 950; color: #6366f1; text-transform: uppercase; letter-spacing: 0.35rem; display: block; margin-bottom: 0.5rem;">Listening Quiz</span>
                                    <h3 style="font-size: 1.65rem; font-weight: 950; color: #0f172a; line-height: 1.2; letter-spacing: -0.03em;">{{ $data['heading'] ?? 'Dictation Test' }}</h3>
                                </div>

                                <div style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
                                    <button type="button" @click="playText()"
                                            style="width: 60px; height: 60px; border-radius: 9999px; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.3s; background: linear-gradient(135deg, #a855f7 0%, #6366f1 100%); box-shadow: 0 10px 20px rgba(168, 85, 247, 0.25);">
                                        <svg style="width: 26px; height: 26px; color: #fff;" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                    </button>
                                    <span style="font-size: 12px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.1em;">Tap to Listen</span>
                                </div>

                                <div style="width: 100%; display: flex; flex-direction: column; gap: 1rem;">
                                    <input type="text" 
                                           x-model="typedInput" 
                                           :disabled="isSolved"
                                           @keydown.enter="checkAnswer()"
                                           placeholder="Type what you heard here..." 
                                           style="width: 100%; border-radius: 1.25rem; background: rgba(255, 255, 255, 0.7); border: 1.5px solid rgba(0, 0, 0, 0.1); color: #1e293b; padding: 1rem 1.25rem; font-size: 16px; font-weight: 700; outline: none; text-align: center;"
                                           :style="isSolved ? 'background: rgba(16, 185, 129, 0.05); border-color: #10b981;' : (attempted && !isSolved ? 'border-color: #f43f5e;' : '')">
                                    
                                    <template x-if="!isSolved">
                                        <button type="button" @click="checkAnswer()" 
                                                style="width: 100%; padding: 1rem; border-radius: 1.25rem; background: #1e293b; color: #fff; border: none; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; cursor: pointer; transition: 0.2s;">
                                            Submit Answer
                                        </button>
                                    </template>

                                    <div x-show="attempted" x-transition style="text-align: center; margin-top: 0.5rem;">
                                        <template x-if="isSolved">
                                            <div style="color: #10b981; font-weight: 800; font-size: 14px;">
                                                {{ $successMsg }}
                                            </div>
                                        </template>
                                        <template x-if="!isSolved && attempted">
                                            <div style="color: #f43f5e; font-weight: 800; font-size: 14px;">
                                                Not quite! Listen again and check your spelling.
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                    {{-- 12. LISTEN AND SPEAK BLOCK --}}
                    @elseif($block['type'] === 'listen_speak')
                        @php
                            $data = $block['data'];
                            $englishText = trim($data['english_text'] ?? '');
                            $hideEnglish = $data['hide_english'] ?? false;
                            $meaningMalayalam = $data['malayalam_text'] ?? '';
                        @endphp
                        <div x-data="listenSpeakBlock('{{ addslashes($englishText) }}', {{ $hideEnglish ? 'true' : 'false' }})" style="margin: 0.25rem 0; width: 100%; display: flex; justify-content: center;">
                            <div class="lego-block" style="gap: 1.5rem; width: 95%; max-width: 550px;">
                                <div style="text-align: center; position: relative; width: 100%;">
                                    
                                    <div style="width: 100%; display: flex; flex-direction: column; gap: 0.75rem; align-items: center;">
                                        <!-- Toggle Buttons Container -->
                                        <div style="display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap; width: 100%;">
                                            <!-- See English Toggle -->
                                            @if($hideEnglish)
                                                <button type="button" 
                                                        @click="showEnglish = !showEnglish"
                                                        class="transcript-toggle-btn btn-english-toggle"
                                                        :class="{ 'is-active': showEnglish }">
                                                    <!-- Icon -->
                                                    <svg x-show="!showEnglish" style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                    <svg x-show="showEnglish" style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3" x-cloak><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.893 7.893L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                                                    <span x-text="showEnglish ? 'Hide English' : 'See English'">See English</span>
                                                </button>
                                            @endif
                                            
                                            <!-- See Malayalam Toggle moved below English text -->
                                        </div>
                                        
                                        <!-- English Text Area -->
                                        <div x-show="showEnglish" 
                                             x-transition:enter="transition ease-out duration-300"
                                             x-transition:enter-start="opacity-0 transform scale-95"
                                             x-transition:enter-end="opacity-100 transform scale-100"
                                             style="width: 100%; background: rgba(255, 255, 255, 0.5); border: 1px solid rgba(0, 0, 0, 0.04); border-radius: 1.5rem; padding: 1.25rem 1.5rem; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.02); margin-top: 0.5rem;">
                                            <h3 style="font-size: 1.7rem; font-weight: 800; color: #0f172a; letter-spacing: -0.02em; line-height: 1.2; margin: 0;">
                                                "{{ $englishText }}"
                                            </h3>
                                        </div>
                                        
                                        <!-- See Malayalam Toggle -->
                                        @if(!empty($meaningMalayalam))
                                            <div style="width: 100%; display: flex; justify-content: center; margin-top: 0.25rem;">
                                                <button type="button" 
                                                        @click="showMalayalam = !showMalayalam"
                                                        class="transcript-toggle-btn btn-malayalam-toggle"
                                                        :class="{ 'is-active': showMalayalam }">
                                                    <!-- Icon -->
                                                    <svg x-show="!showMalayalam" style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                    <svg x-show="showMalayalam" style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3" x-cloak><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.893 7.893L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                                                    <span x-text="showMalayalam ? 'Hide Malayalam' : 'See Malayalam'">See Malayalam</span>
                                                </button>
                                            </div>
                                        @endif
                                        
                                        <!-- Malayalam Meaning Area -->
                                        @if(!empty($meaningMalayalam))
                                            <div x-show="showMalayalam" 
                                                 x-transition:enter="transition ease-out duration-300"
                                                 x-transition:enter-start="opacity-0 transform scale-95"
                                                 x-transition:enter-end="opacity-100 transform scale-100"
                                                 style="width: 100%; background: rgba(255, 255, 255, 0.5); border: 1px solid rgba(16, 185, 129, 0.15); border-radius: 1.5rem; padding: 1.25rem 1.5rem; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.02);">
                                                <p style="font-size: 1.15rem; font-weight: 700; color: #059669; margin: 0; line-height: 1.6;">{{ $meaningMalayalam }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                
                                <div style="display: flex; justify-content: space-around; width: 100%; margin-top: 0.5rem;">
                                    <div style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
                                        <button type="button" @click="speakNow()"
                                                style="width: 55px; height: 55px; border-radius: 9999px; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.3s; background: linear-gradient(135deg, #a855f7 0%, #6366f1 100%); box-shadow: 0 10px 20px rgba(168, 85, 247, 0.25);">
                                            <svg x-show="!speaking" style="width: 22px; height: 22px; color: #fff;" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                            <svg x-show="speaking" style="width: 22px; height: 22px; color: #fff;" fill="currentColor" viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                                        </button>
                                        <span style="font-size: 12px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.1em;">Listen</span>
                                    </div>

                                    <div style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
                                        <button type="button" @click="micStatus === 'listening' || micStatus === 'preparing' ? stopMic() : startMic()"
                                                class="transition-all duration-300"
                                                :class="{ 'mic-preparing': micStatus === 'preparing', 'mic-listening': micStatus === 'listening' }"
                                                style="width: 55px; height: 55px; border-radius: 9999px; border: none; display: flex; align-items: center; justify-content: center; z-index: 10; cursor: pointer; transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);"
                                                :style="{ 
                                                    background: micStatus === 'listening' ? '#f43f5e' : (micStatus === 'preparing' ? '#eab308' : (micStatus === 'success' ? '#10b981' : 'linear-gradient(135deg, #6366f1 0%, #a855f7 100%)')),
                                                    transform: micStatus === 'listening' || micStatus === 'preparing' ? 'scale(1.1)' : 'scale(1)',
                                                    boxShadow: '0 15px 30px rgba(99,102,241,0.25)'
                                                }">
                                            <svg x-show="micStatus !== 'success'" style="width: 22px; height: 22px; color: #fff;" fill="currentColor" viewBox="0 0 24 24"><path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/><path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/></svg>
                                            <svg x-show="micStatus === 'success'" style="width: 24px; height: 24px; color: #fff;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="4"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                        </button>
                                        <span style="font-size: 12px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.1em;">Speak</span>
                                    </div>
                                </div>
                                
                                <div style="text-align: center; min-height: 50px; display: flex; flex-direction: column; justify-content: center; gap: 0.5rem;">
                                    <template x-if="score > 0">
                                        <div style="display: inline-flex; align-items: center; justify-content: center; margin-bottom: 0.25rem;">
                                            <span style="padding: 0.4rem 1.2rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 900; color: #fff; letter-spacing: 0.05em; text-transform: uppercase; transition: all 0.3s;"
                                                  :style="{
                                                      background: score === 100 ? 'linear-gradient(135deg, #10b981 0%, #059669 100%)' :
                                                                 (score >= 85 ? 'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)' :
                                                                 (score >= 50 ? 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)' :
                                                                                'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)')),
                                                      boxShadow: '0 4px 12px rgba(0,0,0,0.1)'
                                                  }"
                                                  x-text="score + '% ACCURATE'">
                                            </span>
                                        </div>
                                    </template>
                                    <span style="font-size: 13px; font-weight: 950; text-transform: uppercase; letter-spacing: 0.15em;"
                                          :style="{ color: micStatus === 'success' ? '#10b981' : (micStatus === 'error' ? '#f43f5e' : '#64748b') }"
                                          x-text="micStatus === 'idle' ? '' : (micStatus === 'preparing' ? 'Connecting Mic...' : (micStatus === 'listening' ? 'Speak Now...' : (micStatus === 'success' ? (score === 100 ? 'Perfect Pronunciation!' : 'Great Pronunciation!') : 'Didn\'t catch that. Try again?')))"></span>
                                    <p x-show="recognizedText" style="font-size: 16px; font-weight: 800; font-style: italic; color: #6366f1; line-height: 1.4;" x-text="recognizedText"></p>
                                </div>

                            </div>
                        </div>

                    {{-- 9. SEPARATOR BLOCK --}}
                    @elseif($block['type'] === 'separator')
                        @php $style = $block['data']['style'] ?? 'empty_space'; @endphp
                        @if($style === 'empty_space')
                            <div style="height: 3.5rem;"></div>
                        @elseif($style === 'thin_line')
                            <div style="height: 1px; background: rgba(0,0,0,0.06); margin: 3.5rem 2rem;"></div>
                        @elseif($style === 'bold_divider')
                            <div style="height: 5px; width: 80px; background: linear-gradient(90deg, #4f46e5, #a855f7); margin: 4.5rem auto; border-radius: 99px; opacity: 0.4;"></div>
                        @endif
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- End of Lesson --}}
    <div style="padding: 0 1rem 10rem 1rem; text-align: center; display: flex; flex-direction: column; gap: 1.5rem; align-items: center;">
        @if(auth()->check())
            <div style="max-width: 320px; margin: 0 auto; width: 100%;">
                @if(auth()->user()->completedUnits()->where('unit_id', $unit->id)->exists())
                    <div style="background: rgba(16, 185, 129, 0.2); color: #10b981; padding: 1.5rem; border-radius: 2rem; font-weight: 950; font-size: 14px; display: flex; align-items: center; justify-content: center; gap: 10px; border: 1px solid rgba(16, 185, 129, 0.4); backdrop-filter: blur(10px);">
                        <svg style="width: 22px; height: 22px;" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"></path></svg>
                        <span style="letter-spacing: 0.1em;">MASTERED</span>
                    </div>
                @else
                    <button @if($isPublic) onclick="this.parentElement.querySelector('form').submit()" @else wire:click="markAsComplete" @endif
                            style="width: 100%; padding: 1.5rem; border-radius: 2.25rem; color: #fff; font-weight: 950; font-size: 13px; letter-spacing: 0.25em; text-transform: uppercase; background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); border: none; cursor: pointer; box-shadow: 0 15px 35px -10px rgba(99, 102, 241, 0.5); transition: 0.3s;"
                            onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 20px 40px -10px rgba(99, 102, 241, 0.6)';"
                            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 15px 35px -10px rgba(99, 102, 241, 0.5)';"
                            class="complete-lesson-btn">
                        MARK AS COMPLETE
                    </button>
                    @if($isPublic)
                        <form action="{{ route('student.units.complete', $unit) }}" method="POST" style="display:none;">@csrf</form>
                    @endif
                @endif
            </div>
        @endif
        
        <div style="font-size: 11px; font-weight: 950; letter-spacing: 0.5em; color: rgba(255,255,255,0.3); text-transform: uppercase; margin-top: 0.5rem;">--- End of Lesson ---</div>
    </div>

    {{-- Universal Navigation Bar (The Pill) --}}
    <div class="nav-pill-wrapper">
        <div class="nav-pill">
            <div style="flex: 1; display: flex; justify-content: center;">
                @if($navPrev)
                    <a href="{{ $isPublic ? route('student.units.show', $navPrev) : ($isStudentPanel ? \App\Filament\Student\Resources\Units\UnitResource::getUrl('view', ['record' => $navPrev->id]) : \App\Filament\Resources\UnitResource::getUrl('view', ['record' => $navPrev->id])) }}" 
                       class="nav-link">
                        <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M15 19l-7-7 7-7" /></svg>
                        <span>Prev</span>
                    </a>
                @endif
            </div>

            <button type="button" onclick="window.location.reload()" 
                    class="nav-refresh-btn">
                <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                <span>Refresh</span>
            </button>

            <div style="flex: 1; display: flex; justify-content: center;">
                @if($navNext)
                    <a href="{{ $isPublic ? route('student.units.show', $navNext) : ($isStudentPanel ? \App\Filament\Student\Resources\Units\UnitResource::getUrl('view', ['record' => $navNext->id]) : \App\Filament\Resources\UnitResource::getUrl('view', ['record' => $navNext->id])) }}" 
                       class="nav-link">
                        <span>Next</span>
                        <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M9 5l7 7-7 7" /></svg>
                    </a>
                @else
                    <a href="{{ $libraryRoute }}" class="nav-link">
                        <span>Library</span>
                        <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 6h16M4 12h16M4 18h7" /></svg>
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('voiceMatcher', (targets, defaultRevealed = false) => ({
            status: 'idle',
            recognizedText: '',
            playing: false,
            quizSolved: false,
            selectedOption: null,
            showResult: false,
            revealed: defaultRevealed,
            showMalayalam: false,
            score: 0,
            recInstance: null,
            safetyTimeout: null,

            getWordMatchPercentage(targetStr, spokenStr) {
                const cleanWord = (w) => {
                    let word = w.toLowerCase().trim().replace(/[?.,\/#!$%\^&\*;:{}=\-_`~()']/g,'');
                    if (word === 'whats' || word === 'whatis') return 'was';
                    if (word === 'where') return 'were';
                    if (word === 'their' || word === 'theyre' || word === 'theyare') return 'there';
                    return word;
                };
                
                const targetWords = targetStr.split(/\s+/).map(cleanWord).filter(w => w.length > 0);
                const spokenWords = spokenStr.split(/\s+/).map(cleanWord).filter(w => w.length > 0);
                
                if (targetWords.length === 0) return 0;
                
                const dp = Array(targetWords.length + 1).fill(null).map(() => Array(spokenWords.length + 1).fill(0));
                
                for (let i = 0; i <= targetWords.length; i++) dp[i][0] = i;
                for (let j = 0; j <= spokenWords.length; j++) dp[0][j] = j;
                
                for (let i = 1; i <= targetWords.length; i++) {
                    for (let j = 1; j <= spokenWords.length; j++) {
                        if (targetWords[i - 1] === spokenWords[j - 1]) {
                            dp[i][j] = dp[i - 1][j - 1];
                        } else {
                            dp[i][j] = Math.min(
                                dp[i - 1][j] + 1,
                                dp[i][j - 1] + 1,
                                dp[i - 1][j - 1] + 1
                            );
                        }
                    }
                }
                
                const editDistance = dp[targetWords.length][spokenWords.length];
                const maxLen = Math.max(targetWords.length, spokenWords.length);
                
                let percentage = Math.round(((maxLen - editDistance) / maxLen) * 100);
                return Math.max(0, percentage);
            },
            
            startVoice() {
                const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
                if (!SpeechRecognition) return;

                this.stopVoice();

                const rec = new SpeechRecognition();
                this.recInstance = rec;
                rec.lang = 'en-US';
                rec.interimResults = true;
                this.status = 'preparing';
                this.score = 0;
                this.recognizedText = '';

                rec.onstart = () => {
                    this.status = 'listening';
                    // Safety timeout: auto-stop recording after 8 seconds of active listening
                    this.safetyTimeout = setTimeout(() => {
                        if (this.status === 'listening') {
                            rec.stop();
                        }
                    }, 8000);
                };

                rec.onresult = (e) => { 
                    this.recognizedText = Array.from(e.results).map(r => r[0].transcript).join(''); 
                };

                rec.onerror = (e) => {
                    console.error("Speech recognition error:", e.error);
                    if (this.status === 'listening' || this.status === 'preparing') {
                        this.status = 'error';
                        setTimeout(() => {
                            if (this.status === 'error') this.status = 'idle';
                        }, 2000);
                    }
                };

                rec.onend = () => {
                    if (this.safetyTimeout) {
                        clearTimeout(this.safetyTimeout);
                        this.safetyTimeout = null;
                    }
                    this.recInstance = null;

                    if (!this.recognizedText.trim()) {
                        if (this.status === 'listening' || this.status === 'preparing') {
                            this.status = 'idle';
                        }
                        this.score = 0;
                        return;
                    }

                    const clean = (s) => {
                        let t = s.toLowerCase().trim().replace(/[?.,\/#!$%\^&\*;:{}=\-_`~()']/g,'');
                        // Specific fix for common STT misrecognitions
                        // We treat them as equivalent in this context
                        return t.split(' ').map(word => {
                            if (word === 'whats' || word === 'whatis') return 'was';
                            if (word === 'where') return 'were';
                            if (word === 'their' || word === 'theyre' || word === 'theyare') return 'there';
                            return word;
                        }).join('');
                    };
                    const text = clean(this.recognizedText);
                    const isStrictMatch = targets.some(t => clean(t) === text);

                    let highestScore = 0;
                    targets.forEach(t => {
                        const s = this.getWordMatchPercentage(t, this.recognizedText);
                        if (s > highestScore) {
                            highestScore = s;
                        }
                    });

                    this.score = highestScore;

                    if (isStrictMatch && this.score < 100) {
                        this.score = 100;
                    }

                    // Lowered passing threshold from 85% to 70% to be more sensitive/friendly
                    if (isStrictMatch || this.score >= 70) {
                        this.status = 'success';
                        if (window.confetti) confetti();
                    } else {
                        this.status = 'error';
                        setTimeout(() => {
                            if (this.status === 'error') {
                                this.status = 'idle';
                            }
                        }, 2000);
                    }
                };
                rec.start();
            },

            stopVoice() {
                if (this.safetyTimeout) {
                    clearTimeout(this.safetyTimeout);
                    this.safetyTimeout = null;
                }
                if (this.recInstance) {
                    try {
                        this.recInstance.abort(); // Use abort to immediately cancel without processing
                    } catch (e) {}
                    this.recInstance = null;
                }
                if (this.status === 'listening' || this.status === 'preparing') {
                    this.status = 'idle';
                }
            }
        }));

        window.getAmterSpeechVoice = function(accent, gender) {
            const voices = window.speechSynthesis.getVoices();
            if (!voices || voices.length === 0) return null;
            if (!gender || gender === 'any') {
                return voices.find(v => v.lang.startsWith(accent));
            }
            const femaleKeywords = ['female', 'zira', 'samantha', 'victoria', 'hazel', 'susan', 'linda', 'heather', 'helena', 'catherine', 'zari', 'serena', 'google us english'];
            const maleKeywords = ['male', 'david', 'mark', 'george', 'daniel', 'peter', 'alex', 'james', 'richard', 'sean'];
            const preferredKeywords = gender.toLowerCase() === 'female' ? femaleKeywords : maleKeywords;
            let matched = voices.find(v => {
                if (!v.lang.startsWith(accent)) return false;
                const name = v.name.toLowerCase();
                return preferredKeywords.some(kw => name.includes(kw));
            });
            if (!matched) {
                matched = voices.find(v => preferredKeywords.some(kw => v.name.toLowerCase().includes(kw)));
            }
            if (!matched) {
                matched = voices.find(v => v.lang.startsWith(accent));
            }
            return matched;
        };

        Alpine.data('listenSpeakBlock', (englishText, hideEnglish) => ({
            englishText: englishText,
            showEnglish: !hideEnglish,
            showMalayalam: false,
            speaking: false,
            
            // Mic logic (adapted from voiceMatcher)
            micStatus: 'idle',
            recognizedText: '',
            score: 0,
            recInstance: null,
            safetyTimeout: null,

            speakNow() {
                if (!this.englishText.trim()) return;

                if (window.speechSynthesis.speaking || this.speaking) {
                    window.speechSynthesis.cancel();
                    this.speaking = false;
                    return;
                }

                this.speaking = true;
                const utterance = new SpeechSynthesisUtterance(this.englishText);
                utterance.lang = 'en-US';
                utterance.rate = 1.0;

                const matchedVoice = window.getAmterSpeechVoice('en-US', 'any');
                if (matchedVoice) {
                    utterance.voice = matchedVoice;
                }

                utterance.onstart = () => { this.speaking = true; };
                utterance.onend = () => { this.speaking = false; };
                utterance.onerror = () => { this.speaking = false; };

                window.speechSynthesis.speak(utterance);
            },

            getWordMatchPercentage(targetStr, spokenStr) {
                const cleanWord = (w) => {
                    let word = w.toLowerCase().trim().replace(/[?.,\/#!$%\^&\*;:{}=\-_`~()']/g,'');
                    if (word === 'whats' || word === 'whatis') return 'was';
                    if (word === 'where') return 'were';
                    if (word === 'their' || word === 'theyre' || word === 'theyare') return 'there';
                    return word;
                };
                
                const targetWords = targetStr.split(/\s+/).map(cleanWord).filter(w => w.length > 0);
                const spokenWords = spokenStr.split(/\s+/).map(cleanWord).filter(w => w.length > 0);
                
                if (targetWords.length === 0) return 0;
                
                const dp = Array(targetWords.length + 1).fill(null).map(() => Array(spokenWords.length + 1).fill(0));
                
                for (let i = 0; i <= targetWords.length; i++) dp[i][0] = i;
                for (let j = 0; j <= spokenWords.length; j++) dp[0][j] = j;
                
                for (let i = 1; i <= targetWords.length; i++) {
                    for (let j = 1; j <= spokenWords.length; j++) {
                        if (targetWords[i - 1] === spokenWords[j - 1]) {
                            dp[i][j] = dp[i - 1][j - 1];
                        } else {
                            dp[i][j] = Math.min(
                                dp[i - 1][j] + 1,
                                dp[i][j - 1] + 1,
                                dp[i - 1][j - 1] + 1
                            );
                        }
                    }
                }
                
                const editDistance = dp[targetWords.length][spokenWords.length];
                const maxLen = Math.max(targetWords.length, spokenWords.length);
                
                let percentage = Math.round(((maxLen - editDistance) / maxLen) * 100);
                return Math.max(0, percentage);
            },
            
            startMic() {
                const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
                if (!SpeechRecognition) return;

                this.stopMic();

                const rec = new SpeechRecognition();
                this.recInstance = rec;
                rec.lang = 'en-US';
                rec.interimResults = true;
                this.micStatus = 'preparing';
                this.score = 0;
                this.recognizedText = '';

                rec.onstart = () => {
                    this.micStatus = 'listening';
                    this.safetyTimeout = setTimeout(() => {
                        if (this.micStatus === 'listening') {
                            rec.stop();
                        }
                    }, 8000);
                };

                rec.onresult = (e) => { 
                    this.recognizedText = Array.from(e.results).map(r => r[0].transcript).join(''); 
                };

                rec.onerror = (e) => {
                    if (this.micStatus === 'listening' || this.micStatus === 'preparing') {
                        this.micStatus = 'error';
                        setTimeout(() => {
                            if (this.micStatus === 'error') this.micStatus = 'idle';
                        }, 2000);
                    }
                };

                rec.onend = () => {
                    if (this.safetyTimeout) {
                        clearTimeout(this.safetyTimeout);
                        this.safetyTimeout = null;
                    }
                    this.recInstance = null;

                    if (!this.recognizedText.trim()) {
                        if (this.micStatus === 'listening' || this.micStatus === 'preparing') {
                            this.micStatus = 'idle';
                        }
                        this.score = 0;
                        return;
                    }

                    const clean = (s) => {
                        let t = s.toLowerCase().trim().replace(/[?.,\/#!$%\^&\*;:{}=\-_`~()']/g,'');
                        return t.split(' ').map(word => {
                            if (word === 'whats' || word === 'whatis') return 'was';
                            if (word === 'where') return 'were';
                            if (word === 'their' || word === 'theyre' || word === 'theyare') return 'there';
                            return word;
                        }).join('');
                    };
                    const text = clean(this.recognizedText);
                    const isStrictMatch = (clean(this.englishText) === text);

                    this.score = this.getWordMatchPercentage(this.englishText, this.recognizedText);

                    if (isStrictMatch && this.score < 100) {
                        this.score = 100;
                    }

                    if (isStrictMatch || this.score >= 70) {
                        this.micStatus = 'success';
                        if (window.confetti) confetti();
                    } else {
                        this.micStatus = 'error';
                        setTimeout(() => {
                            if (this.micStatus === 'error') {
                                this.micStatus = 'idle';
                            }
                        }, 2000);
                    }
                };
                rec.start();
            },

            stopMic() {
                if (this.safetyTimeout) {
                    clearTimeout(this.safetyTimeout);
                    this.safetyTimeout = null;
                }
                if (this.recInstance) {
                    try {
                        this.recInstance.abort();
                    } catch (e) {}
                    this.recInstance = null;
                }
                if (this.micStatus === 'listening' || this.micStatus === 'preparing') {
                    this.micStatus = 'idle';
                }
            }
        }));

        Alpine.data('textToSpeech', (defaultText, defaultAccent, gender = 'any') => ({
            text: defaultText,
            accent: defaultAccent,
            gender: gender,
            rate: 1.0,
            speaking: false,
            showEnglish: false,
            showMalayalam: false,

            speakNow() {
                if (!this.text.trim()) return;

                if (window.speechSynthesis.speaking || this.speaking) {
                    window.speechSynthesis.cancel();
                    this.speaking = false;
                    return;
                }

                this.speaking = true; // Set playing state immediately on click

                const utterance = new SpeechSynthesisUtterance(this.text);
                utterance.lang = this.accent;
                utterance.rate = parseFloat(this.rate);

                const matchedVoice = window.getAmterSpeechVoice(this.accent, this.gender);
                if (matchedVoice) {
                    utterance.voice = matchedVoice;
                }

                utterance.onstart = () => { this.speaking = true; };
                utterance.onend = () => { this.speaking = false; };
                utterance.onerror = () => { this.speaking = false; };

                window.speechSynthesis.speak(utterance);
            }
        }));

        Alpine.data('dictationTest', (correctSentence, accent, gender = 'any') => ({
            correct: correctSentence,
            accent: accent,
            gender: gender,
            typedInput: '',
            attempted: false,
            isSolved: false,

            playText() {
                if (window.speechSynthesis.speaking) {
                    window.speechSynthesis.cancel();
                }
                const utterance = new SpeechSynthesisUtterance(this.correct);
                utterance.lang = this.accent;
                utterance.rate = 0.9;

                const matchedVoice = window.getAmterSpeechVoice(this.accent, this.gender);
                if (matchedVoice) utterance.voice = matchedVoice;

                window.speechSynthesis.speak(utterance);
            },

            checkAnswer() {
                if (!this.typedInput.trim()) return;

                this.attempted = true;
                const cleanStr = (str) => str.toLowerCase().trim().replace(/[.,\/#!$%\^&\*;:{}=\-_`~()']/g,"").replace(/\s+/g," ");
                
                if (cleanStr(this.typedInput) === cleanStr(this.correct)) {
                    this.isSolved = true;
                    if (window.confetti) confetti();
                } else {
                    this.isSolved = false;
                }
            }
        }));
    });

    // Pre-warm Web Speech API on first user interaction for instantaneous mobile playback
    (function() {
        const preWarm = () => {
            try {
                if (window.speechSynthesis) {
                    const u = new SpeechSynthesisUtterance('');
                    u.volume = 0;
                    window.speechSynthesis.speak(u);
                }
            } catch (e) {}
            document.removeEventListener('click', preWarm);
            document.removeEventListener('touchstart', preWarm);
        };
        document.addEventListener('click', preWarm);
        document.addEventListener('touchstart', preWarm);
    })();
</script>
