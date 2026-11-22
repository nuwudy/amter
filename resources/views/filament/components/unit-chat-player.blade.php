@php
    $isFullscreen = $isFullscreen ?? !request()->is('admin/*');
    $groupData = isset($unit)
        ? app(\App\Services\LessonGroupResolver::class)->resolveForUnit($unit, $currentTrack ?? null)
        : ['units' => [], 'unit_ids' => [], 'lead_video' => null, 'previous_group_unit' => null, 'next_group_unit' => null];

    $chatTurns = isset($unit)
        ? app(\App\Services\LessonChatTransformer::class)->transformGroup(
            $groupData['units'],
            url('/tts-stream'),
            $groupData['lead_video']
        )
        : [];

    $leadVideo = $groupData['lead_video'] ?? null;
    $leadVideoUrl = trim((string) ($leadVideo['video_url'] ?? ''));
    $leadVideoId = trim((string) ($leadVideo['video_id'] ?? ($unit->video_id ?? '')));
    $leadSentence = trim((string) ($leadVideo['target_sentence'] ?? ''));

    $trackId = isset($currentTrack) && $currentTrack ? $currentTrack->id : request()->query('track_id');
    $completeUrl = isset($unit) && auth()->check()
        ? route('student.units.complete', array_filter(['unit' => $unit->id, 'track_id' => $trackId]))
        : null;

    $groupUnitIdsCsv = implode(',', $groupData['unit_ids'] ?? (isset($unit) ? [$unit->id] : []));
    $storageKey = 'amter_step_v2_' . ($groupUnitIdsCsv !== '' ? $groupUnitIdsCsv : ($unit->id ?? '0'));

    $course = $course ?? null;
    $makeUnitUrl = function ($targetUnit) use ($trackId, $course) {
        if (!$targetUnit) {
            return null;
        }
        if (auth()->check()) {
            return route('student.units.show', array_filter(['unit' => $targetUnit->id, 'track_id' => $trackId]));
        }
        if (isset($course) && $course) {
            return route('public.unit.show', ['course' => $course->id, 'unit' => $targetUnit->id]);
        }
        return route('student.units.show', array_filter(['unit' => $targetUnit->id, 'track_id' => $trackId]));
    };

    $prevUnitObj = $groupData['previous_group_unit'] ?? null;
    $nextUnitObj = $groupData['next_group_unit'] ?? null;
    $prevGroupUrl = $makeUnitUrl($prevUnitObj);
    $nextGroupUrl = $makeUnitUrl($nextUnitObj);

    $homeUrl = auth()->check()
        ? route('filament.student.pages.dashboard')
        : route('public.library');

    $backToSessionUrl = (isset($course) && $course)
        ? route('public.course.show', ['course' => $course->id])
        : (auth()->check() && isset($unit) && $unit->courseSession?->module_id
            ? route('student.modules.show', ['module' => $unit->courseSession->module_id])
            : route('public.library'));

    $rawUnitTitle = trim((string) ($unit->title ?? 'Lesson'));
    $unitDisplayTitle = ctype_digit($rawUnitTitle) ? "Lesson {$rawUnitTitle}" : ($rawUnitTitle !== '' ? $rawUnitTitle : 'Lesson');
    $sessionTitle = trim((string) ($unit?->courseSession?->title ?? ''));

    $isAlreadyCompleted = auth()->check() && isset($unit)
        ? auth()->user()->completedUnits()->where('unit_id', $unit->id)->exists()
        : false;

    $isAdmin = auth()->check() && auth()->user()->isAdmin();
    $adminEditUrl = ($isAdmin && isset($unit))
        ? \App\Filament\Resources\UnitResource::getUrl('edit', ['record' => $unit->id], panel: 'admin')
            . ($unit->course_session_id ? '?session_id=' . $unit->course_session_id : '')
        : null;

    // Build flat vertical step cards matching the Android LessonPlayerScreen flow
    $steps = [];
    $addedLeadVideo = false;
    $seenPhrases = [];

    $normPhrase = static fn (string $s): string => strtolower(trim((string) preg_replace('/[^a-z0-9\s]/i', '', $s)));

    if ($leadVideoUrl !== '') {
        $steps[] = [
            'keyId' => 'lead_video_' . ($unit->id ?? '0'),
            'type' => 'video',
            'title' => (string) ($leadVideo['title'] ?? $unitDisplayTitle),
            'mediaUrl' => $leadVideoUrl,
            'instructions' => trim((string) ($leadVideo['instructions'] ?? '')) ?: null,
            'transcript' => trim((string) ($leadVideo['transcript'] ?? $leadSentence)) ?: null,
            'nextHint' => 'Swipe up to begin',
        ];
        $addedLeadVideo = true;
    } elseif ($leadVideoId !== '') {
        $steps[] = [
            'keyId' => 'ext_video_' . ($unit->id ?? '0'),
            'type' => 'external_video',
            'title' => $unitDisplayTitle,
            'mediaUrl' => $leadVideoId,
            'nextHint' => 'Swipe up to continue',
        ];
    }

    foreach ($chatTurns as $idx => $turn) {
        $kind = (string) ($turn['kind'] ?? '');
        $data = is_array($turn['data'] ?? null) ? $turn['data'] : [];
        $tKey = (string) ($turn['id'] ?? ('turn-' . ($idx + 1)));
        $title = isset($turn['title']) ? trim((string) $turn['title']) : null;
        $attachedImage = trim((string) ($data['attached_image_url'] ?? '')) ?: null;

        switch ($kind) {
            case 'separator':
                break;

            case 'video':
                $vUrl = trim((string) ($data['video_url'] ?? ''));
                $vId = trim((string) ($data['video_id'] ?? ''));
                if ($vUrl !== '' && (!$addedLeadVideo || $vUrl !== $leadVideoUrl)) {
                    $steps[] = [
                        'keyId' => "turn_{$tKey}_video",
                        'type' => 'video',
                        'title' => $title ?: $unitDisplayTitle,
                        'instructions' => trim((string) ($data['instructions'] ?? '')) ?: null,
                        'transcript' => trim((string) ($data['transcript'] ?? '')) ?: null,
                        'mediaUrl' => $vUrl,
                        'turnKey' => $tKey,
                    ];
                } elseif ($vId !== '' && $vId !== $leadVideoId) {
                    $steps[] = [
                        'keyId' => "turn_{$tKey}_ext_video",
                        'type' => 'external_video',
                        'title' => $title ?: $unitDisplayTitle,
                        'mediaUrl' => $vId,
                        'turnKey' => $tKey,
                    ];
                }
                break;

            case 'tts':
                $text = trim((string) ($data['text'] ?? ''));
                if ($text !== '') {
                    $steps[] = [
                        'keyId' => "turn_{$tKey}_listen",
                        'type' => 'listen',
                        'phrases' => [$text],
                        'instructions' => trim((string) ($data['instructions'] ?? '')) ?: null,
                        'meaningMalayalam' => trim((string) ($data['meaning_malayalam'] ?? '')) ?: null,
                        'hideEnglishByDefault' => !empty($data['hide_english']),
                        'attachedImageUrl' => $attachedImage,
                        'turnKey' => $tKey,
                        'nextHint' => 'Swipe up for next',
                    ];
                }
                break;

            case 'voice_match':
            case 'tts_drill':
            case 'listen_speak':
                $rawPhrases = $data['phrases'] ?? null;
                $phrases = [];
                if (is_array($rawPhrases)) {
                    foreach ($rawPhrases as $p) {
                        $trimmed = trim((string) $p);
                        if ($trimmed !== '') {
                            $phrases[] = $trimmed;
                        }
                    }
                }
                if (empty($phrases)) {
                    $fallbackText = trim((string) ($data['text'] ?? $data['english_text'] ?? ''));
                    if ($fallbackText !== '') {
                        $phrases[] = $fallbackText;
                    }
                }
                if (!empty($phrases)) {
                    foreach ($phrases as $p) {
                        $np = $normPhrase($p);
                        if ($np !== '') {
                            $seenPhrases[$np] = true;
                        }
                    }
                    $mlMeaning = trim((string) ($data['meaning_malayalam'] ?? $data['malayalam_text'] ?? '')) ?: null;
                    $instructions = trim((string) ($data['instructions'] ?? '')) ?: null;
                    $hideEn = !empty($data['hide_english']);
                    $btnLabel = trim((string) ($data['button_label'] ?? '')) ?: 'Tap to Speak';

                    // Card A — Listen
                    $steps[] = [
                        'keyId' => "turn_{$tKey}_listen",
                        'type' => 'listen',
                        'phrases' => $phrases,
                        'instructions' => $instructions,
                        'meaningMalayalam' => $mlMeaning,
                        'hideEnglishByDefault' => $hideEn,
                        'attachedImageUrl' => $attachedImage,
                        'turnKey' => $tKey,
                        'nextHint' => 'Swipe up to practice speaking',
                    ];
                    // Card B — Speak Practice
                    $steps[] = [
                        'keyId' => "turn_{$tKey}_speak",
                        'type' => 'speak',
                        'phrases' => $phrases,
                        'instructions' => $instructions,
                        'meaningMalayalam' => $mlMeaning,
                        'hideEnglishByDefault' => $hideEn,
                        'buttonLabel' => $btnLabel,
                        'attachedImageUrl' => $attachedImage,
                        'turnKey' => $tKey,
                        'isBaseline' => !empty($data['is_baseline']),
                        'isMastery' => !empty($data['is_mastery']),
                        'nextHint' => 'Swipe up for next',
                    ];
                }
                break;

            case 'audio':
                $audioUrl = trim((string) ($data['audio_url'] ?? ''));
                if ($audioUrl !== '') {
                    $steps[] = [
                        'keyId' => "turn_{$tKey}_audio",
                        'type' => 'audio',
                        'title' => $title ?: 'Lesson Audio',
                        'mediaUrl' => $audioUrl,
                        'transcript' => trim((string) ($data['transcript'] ?? '')) ?: null,
                        'turnKey' => $tKey,
                    ];
                }
                break;

            case 'text':
                $html = trim((string) ($data['html'] ?? ''));
                if ($html !== '') {
                    $steps[] = [
                        'keyId' => "turn_{$tKey}_text",
                        'type' => 'rich_text',
                        'title' => $title,
                        'htmlContent' => $html,
                        'alignment' => (string) ($data['alignment'] ?? 'left'),
                        'textColor' => (string) ($data['text_color'] ?? 'default'),
                        'attachedImageUrl' => $attachedImage,
                        'turnKey' => $tKey,
                    ];
                }
                break;

            case 'image':
                $imgUrl = trim((string) ($data['image_url'] ?? ''));
                if ($imgUrl !== '') {
                    $steps[] = [
                        'keyId' => "turn_{$tKey}_image",
                        'type' => 'image',
                        'title' => $title,
                        'caption' => trim((string) ($data['caption'] ?? '')) ?: null,
                        'mediaUrl' => $imgUrl,
                        'turnKey' => $tKey,
                    ];
                }
                break;

            case 'dictation':
                $correctSentence = trim((string) ($data['correct_sentence'] ?? $data['text'] ?? ''));
                if ($correctSentence !== '') {
                    $steps[] = [
                        'keyId' => "turn_{$tKey}_dictation",
                        'type' => 'dictation',
                        'heading' => trim((string) ($data['heading'] ?? '')) ?: 'Dictation & Listening Test',
                        'correctSentence' => $correctSentence,
                        'instructions' => trim((string) ($data['instructions'] ?? '')) ?: 'Listen carefully and type what you hear.',
                        'successMessage' => trim((string) ($data['success_message'] ?? '')) ?: 'Great job! Your listening is spot on!',
                        'turnKey' => $tKey,
                    ];
                }
                break;

            case 'quiz':
                $question = trim((string) ($data['question'] ?? ''));
                $rawOptions = is_array($data['options'] ?? null) ? array_values($data['options']) : [];
                $correctIdx = (int) ($data['correct_index'] ?? 0);
                $options = [];
                foreach ($rawOptions as $oIdx => $opt) {
                    if (is_array($opt)) {
                        $options[] = [
                            'text' => (string) ($opt['text'] ?? $opt['option_text'] ?? ''),
                            'is_correct' => array_key_exists('is_correct', $opt) ? (bool) $opt['is_correct'] : ($oIdx === $correctIdx),
                        ];
                    } else {
                        $options[] = [
                            'text' => (string) $opt,
                            'is_correct' => $oIdx === $correctIdx,
                        ];
                    }
                }
                if ($question !== '' && !empty($options)) {
                    $steps[] = [
                        'keyId' => "turn_{$tKey}_quiz",
                        'type' => 'quiz',
                        'question' => $question,
                        'options' => $options,
                        'instructions' => trim((string) ($data['instructions'] ?? '')) ?: null,
                        'explanation' => trim((string) ($data['explanation'] ?? '')) ?: null,
                        'turnKey' => $tKey,
                    ];
                }
                break;

            case 'code':
                $code = trim((string) ($data['code'] ?? ''));
                if ($code !== '') {
                    $steps[] = [
                        'keyId' => "turn_{$tKey}_code",
                        'type' => 'code',
                        'title' => $title,
                        'code' => $code,
                        'turnKey' => $tKey,
                    ];
                }
                break;
        }
    }

    // Fallback: if unit has a transcript not already covered by a drill, split into Listen -> Speak
    $unitTranscript = isset($unit) ? trim((string) ($unit->transcript ?? '')) : '';
    if ($unitTranscript !== '' && !isset($seenPhrases[$normPhrase($unitTranscript)])) {
        $steps[] = [
            'keyId' => 'transcript_listen_' . ($unit->id ?? '0'),
            'type' => 'listen',
            'phrases' => [$unitTranscript],
            'instructions' => 'Listen carefully to the transcript.',
            'turnKey' => 'transcript_' . ($unit->id ?? '0'),
            'nextHint' => 'Swipe up to practice speaking',
        ];
        $steps[] = [
            'keyId' => 'transcript_speak_' . ($unit->id ?? '0'),
            'type' => 'speak',
            'phrases' => [$unitTranscript],
            'instructions' => 'Practice reading the transcript aloud.',
            'buttonLabel' => 'Tap to Speak',
            'turnKey' => 'transcript_' . ($unit->id ?? '0'),
        ];
    }

    // Final Mastery Summary step
    $steps[] = [
        'keyId' => 'mastery_' . ($unit->id ?? '0'),
        'type' => 'mastery',
        'title' => $unitDisplayTitle,
    ];

    // Seamless swipe-up transition sentinel when a next unit exists
    if ($nextGroupUrl) {
        $steps[] = [
            'keyId' => 'next_sentinel_' . ($unit->id ?? '0'),
            'type' => 'next_sentinel',
        ];
    }
@endphp

@if(request()->is('admin/*'))
    <script>
        window.tailwind = window.tailwind || {};
        window.tailwind.config = { corePlugins: { preflight: false } };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
@endif

<style>
    [x-cloak] { display: none !important; }

    .amter-vp-shell {
        font-family: 'Outfit', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
        background: #f8fafc;
        color: #0f172a;
        -webkit-tap-highlight-color: transparent;
    }

    .amter-vp-scroller {
        scroll-snap-type: y mandatory;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        overscroll-behavior-y: contain;
    }
    .amter-vp-scroller::-webkit-scrollbar {
        display: none;
    }

    .amter-vp-slide {
        scroll-snap-align: start;
        scroll-snap-stop: always;
    }

    .amter-vp-inner-scroll {
        scrollbar-width: none;
    }
    .amter-vp-inner-scroll::-webkit-scrollbar {
        display: none;
    }

    /* Listen Hero Ripple */
    @keyframes amterListenRipple {
        0% {
            transform: scale(1);
            opacity: 0.35;
        }
        100% {
            transform: scale(1.48);
            opacity: 0;
        }
    }
    .amter-listen-ripple {
        animation: amterListenRipple 1.2s cubic-bezier(0.22, 1, 0.36, 1) infinite;
    }

    /* Speak Mic Pulse */
    @keyframes amterMicIdlePulse {
        0%, 100% {
            transform: scale(1);
            opacity: 0.16;
        }
        50% {
            transform: scale(1.16);
            opacity: 0.28;
        }
    }
    .amter-mic-idle-ring {
        animation: amterMicIdlePulse 1.6s ease-in-out infinite;
    }

    @keyframes amterMicLivePulse {
        0%, 100% {
            transform: scale(1.12);
            opacity: 0.32;
        }
        50% {
            transform: scale(1.36);
            opacity: 0.14;
        }
    }
    .amter-mic-live-ring {
        animation: amterMicLivePulse 0.9s ease-in-out infinite;
    }

    /* Rich Text Card Styling */
    .amter-richtext-body p {
        margin: 0 0 0.75rem 0;
        line-height: 1.6;
    }
    .amter-richtext-body p:last-child {
        margin-bottom: 0;
    }
    .amter-richtext-body strong {
        font-weight: 800;
    }
    .amter-richtext-body ul, .amter-richtext-body ol {
        padding-left: 1.25rem;
        margin: 0.5rem 0;
    }

    /* Ensure SVG icon sizes work even in Filament Admin where frontend Tailwind utilities aren't compiled */
    .amter-vp-shell svg { flex-shrink: 0; }
    .amter-vp-shell svg.w-3\.5 { width: 0.875rem; height: 0.875rem; }
    .amter-vp-shell svg.w-4 { width: 1rem; height: 1rem; }
    .amter-vp-shell svg.w-\[18px\] { width: 18px; height: 18px; }
    .amter-vp-shell svg.w-5 { width: 1.25rem; height: 1.25rem; }
    .amter-vp-shell svg.w-6 { width: 1.5rem; height: 1.5rem; }
    .amter-vp-shell svg.w-8 { width: 2rem; height: 2rem; }
    .amter-vp-shell svg.w-9 { width: 2.25rem; height: 2.25rem; }
    .amter-vp-shell svg.w-16 { width: 4rem; height: 4rem; }
</style>

<div
    x-data="amterStepPlayer({
        steps: @js($steps),
        ttsEndpoint: @js(url('/tts-stream')),
        completeUrl: @js($completeUrl),
        groupUnitIdsCsv: @js($groupUnitIdsCsv),
        trackId: @js($trackId),
        csrfToken: @js(csrf_token()),
        nextGroupUrl: @js($nextGroupUrl),
        prevGroupUrl: @js($prevGroupUrl),
        homeUrl: @js($homeUrl),
        storageKey: @js($storageKey),
        isAlreadyCompleted: @js($isAlreadyCompleted),
        isAuthenticated: @js(auth()->check()),
        unitTitle: @js($unitDisplayTitle)
    })"
    @keydown.window="handleKeydown($event)"
    class="amter-vp-shell {{ $isFullscreen ? 'fixed inset-0 z-40 h-[100dvh] w-full' : 'relative h-[78vh] min-h-[600px] w-full rounded-3xl border border-slate-200 shadow-xl' }} flex flex-col overflow-hidden select-none"
>
    {{-- Minimalist Floating Top Overlay --}}
    <header class="shrink-0 z-30 w-full bg-slate-50/90 backdrop-blur-md">
        <div class="max-w-xl mx-auto w-full px-3 pt-2.5 pb-1.5 flex items-center gap-2.5">
            {{-- Top-Left Floating Home Pill --}}
            <a
                href="{{ $homeUrl }}"
                @click="stopAllMedia()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white text-slate-900 border border-slate-200/90 shadow-sm hover:bg-slate-100 active:scale-95 transition-all shrink-0 no-underline"
                title="Go to Dashboard"
            >
                <svg class="w-[18px] h-[18px] text-indigo-600 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M11.47 3.841a.75.75 0 0 1 1.06 0l8.69 8.69a.75.75 0 1 0 1.06-1.061l-8.689-8.69a2.25 2.25 0 0 0-3.182 0l-8.69 8.69a.75.75 0 1 0 1.061 1.06l8.69-8.689Z" />
                    <path d="m12 5.432 8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 0 1-.75-.75v-4.5a.75.75 0 0 0-.75-.75h-3a.75.75 0 0 0-.75.75V21a.75.75 0 0 1-.75.75H5.625a1.875 1.875 0 0 1-1.875-1.875v-6.198a2.29 2.29 0 0 0 .091-.086L12 5.432Z" />
                </svg>
                <span class="text-xs font-bold tracking-tight">Home</span>
            </a>

            {{-- Center: Slim Unit Title, Step Counter & Progress Bar --}}
            <div class="flex-1 min-w-0 flex flex-col">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-bold text-slate-600 truncate">
                        {{ $unitDisplayTitle }}
                    </span>
                    <span
                        class="text-[11px] font-extrabold text-indigo-600 shrink-0 tabular-nums"
                        x-text="`${displayStep}/${contentStepCount}`"
                    ></span>
                </div>
                <div class="mt-1.5 h-[5px] w-full rounded-full bg-slate-200 overflow-hidden">
                    <div
                        class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-cyan-500 transition-all duration-300 ease-out"
                        :style="`width: ${progressPercent}%`"
                    ></div>
                </div>
            </div>

            {{-- Top-Right Compact Overflow Menu --}}
            <div class="relative shrink-0" @click.outside="menuOpen = false">
                <button
                    type="button"
                    @click="menuOpen = !menuOpen"
                    class="w-9 h-9 rounded-full inline-flex items-center justify-center text-slate-600 hover:bg-slate-200/70 active:scale-95 transition-all cursor-pointer border-0 bg-transparent"
                    aria-label="Lesson options"
                >
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="5.5" r="1.75" />
                        <circle cx="12" cy="12" r="1.75" />
                        <circle cx="12" cy="18.5" r="1.75" />
                    </svg>
                </button>

                <div
                    x-show="menuOpen"
                    x-cloak
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                    class="absolute right-0 mt-1.5 w-48 rounded-2xl bg-white border border-slate-200 shadow-xl py-1.5 z-50"
                >
                    <a
                        href="{{ $backToSessionUrl }}"
                        @click="stopAllMedia()"
                        class="flex items-center gap-2.5 px-3.5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 no-underline"
                    >
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        <span>Back to Session</span>
                    </a>

                    <button
                        type="button"
                        @click="menuOpen = false; restartUnit()"
                        class="w-full flex items-center gap-2.5 px-3.5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 text-left border-0 bg-transparent cursor-pointer"
                    >
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Restart Unit</span>
                    </button>

                    @if($prevGroupUrl)
                        <a
                            href="{{ $prevGroupUrl }}"
                            @click="stopAllMedia()"
                            class="flex items-center gap-2.5 px-3.5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 no-underline"
                        >
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            <span>Previous Unit</span>
                        </a>
                    @endif

                    @if($nextGroupUrl)
                        <button
                            type="button"
                            @click="menuOpen = false; advanceToNextUnit()"
                            class="w-full flex items-center gap-2.5 px-3.5 py-2.5 text-sm font-semibold text-indigo-700 hover:bg-indigo-50 text-left border-0 bg-transparent cursor-pointer"
                        >
                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            <span>Next Unit</span>
                        </button>
                    @endif

                    @if($adminEditUrl)
                        <div class="my-1 border-t border-slate-100"></div>
                        <a
                            href="{{ $adminEditUrl }}"
                            class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-bold text-amber-700 hover:bg-amber-50 no-underline"
                        >
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span>Edit Unit (Admin)</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </header>

    {{-- Toast Notification (e.g. +100 XP on auto-complete) --}}
    <div
        x-show="toastMessage"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        class="fixed top-16 left-1/2 -translate-x-1/2 z-50 px-4 py-2 rounded-full bg-slate-900 text-white text-xs font-bold shadow-lg flex items-center gap-2 pointer-events-none"
    >
        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
        <span x-text="toastMessage"></span>
    </div>

    {{-- Main Full-Screen Vertical Scroll-Snap Step Stage --}}
    <main
        x-ref="scroller"
        @scroll.passive="onScrollerScroll()"
        class="amter-vp-scroller flex-1 min-h-0 w-full overflow-y-auto relative"
    >
        <template x-for="(step, idx) in steps" :key="step.keyId">
            <section
                :data-step-index="idx"
                class="amter-vp-slide h-full w-full flex flex-col items-center justify-center overflow-hidden"
            >
                <div class="amter-vp-inner-scroll w-full max-w-xl mx-auto max-h-full overflow-y-auto px-5 py-4 flex flex-col items-center justify-center">

                    {{-- ==================== 1. VIDEO STEP ==================== --}}
                    <template x-if="step.type === 'video'">
                        <div class="w-full flex flex-col items-center gap-3.5">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-sky-500/10 text-sky-700 text-[11px] font-extrabold tracking-widest uppercase">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5.14v14l11-7-11-7z"/></svg>
                                Watch &amp; Listen
                            </span>

                            <p
                                x-show="step.instructions"
                                class="text-sm text-slate-600 text-center font-medium leading-relaxed m-0"
                                x-text="step.instructions"
                            ></p>

                            <div class="w-full rounded-2xl overflow-hidden bg-slate-950 shadow-xl border border-slate-200/80">
                                <video
                                    :src="step.mediaUrl"
                                    :data-step-video="idx"
                                    playsinline
                                    controls
                                    @loadedmetadata="captureNativeDuration($event)"
                                    @timeupdate="handleVideoTimeUpdate($event, step.keyId)"
                                    @play="videoPlayingMap[step.keyId] = true"
                                    @pause="videoPlayingMap[step.keyId] = false"
                                    class="w-full max-h-[44dvh] object-contain bg-black block"
                                ></video>
                                <div class="px-3.5 py-2.5 bg-slate-900 flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <button
                                            type="button"
                                            @click="toggleStepVideo(idx, step.keyId)"
                                            class="px-3 py-1.5 rounded-full text-xs font-bold transition-colors border-0 cursor-pointer"
                                            :class="videoPlayingMap[step.keyId] ? 'bg-amber-400 text-slate-950' : 'bg-white/15 text-white hover:bg-white/25'"
                                            x-text="videoPlayingMap[step.keyId] ? 'Pause' : 'Play'"
                                        ></button>
                                        <button
                                            type="button"
                                            @click="toggleVideoSlow(idx)"
                                            class="px-3 py-1.5 rounded-full text-xs font-bold transition-colors border-0 cursor-pointer"
                                            :class="slowSpeed ? 'bg-cyan-500 text-white' : 'bg-white/10 text-slate-200 hover:bg-white/20'"
                                        >0.75x</button>
                                        <button
                                            type="button"
                                            @click="loopVideo = !loopVideo"
                                            class="px-3 py-1.5 rounded-full text-xs font-bold transition-colors border-0 cursor-pointer"
                                            :class="loopVideo ? 'bg-indigo-500 text-white' : 'bg-white/10 text-slate-200 hover:bg-white/20'"
                                        >Loop</button>
                                    </div>
                                    <span class="text-[11px] font-semibold text-slate-400 truncate" x-text="step.title || 'Native Clip'"></span>
                                </div>
                            </div>

                            <p
                                x-show="step.transcript"
                                class="text-base sm:text-lg font-extrabold text-slate-900 text-center leading-snug m-0 px-2"
                                x-text="step.transcript"
                            ></p>
                        </div>
                    </template>

                    {{-- ==================== 1b. EXTERNAL VIDEO (BUNNY) STEP ==================== --}}
                    <template x-if="step.type === 'external_video'">
                        <div class="w-full flex flex-col items-center gap-3.5">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-sky-500/10 text-sky-700 text-[11px] font-extrabold tracking-widest uppercase">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5.14v14l11-7-11-7z"/></svg>
                                Video Lesson
                            </span>
                            <div class="w-full rounded-2xl overflow-hidden bg-black shadow-xl aspect-video border border-slate-200">
                                <iframe
                                    :src="`https://iframe.mediadelivery.net/embed/569307/${step.mediaUrl}?autoplay=false`"
                                    loading="lazy"
                                    class="w-full h-full border-0"
                                    allow="accelerometer;gyroscope;autoplay;encrypted-media;picture-in-picture;"
                                    allowfullscreen="true"
                                ></iframe>
                            </div>
                        </div>
                    </template>

                    {{-- ==================== 2. CARD A — LISTEN STEP ==================== --}}
                    <template x-if="step.type === 'listen'">
                        <div class="w-full flex flex-col items-center text-center">
                            {{-- Eyebrow --}}
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-cyan-600/10 text-cyan-700 text-[11px] font-extrabold tracking-widest uppercase">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.3" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 13a8 8 0 0116 0v5a2 2 0 01-2 2h-1a2 2 0 01-2-2v-3a2 2 0 012-2h3M4 13v5a2 2 0 002 2h1a2 2 0 002-2v-3a2 2 0 00-2-2H4"/>
                                </svg>
                                LISTEN
                            </span>

                            <p
                                x-show="step.instructions"
                                class="mt-2.5 text-sm text-slate-500 font-medium leading-relaxed max-w-md m-0"
                                x-text="step.instructions"
                            ></p>

                            <template x-if="step.attachedImageUrl">
                                <img
                                    :src="step.attachedImageUrl"
                                    alt=""
                                    class="mt-4 max-h-[160px] w-auto rounded-2xl object-contain border border-slate-200 shadow-sm"
                                />
                            </template>

                            {{-- Primary English Phrase --}}
                            <div class="mt-5 w-full">
                                <h2
                                    x-show="!isEnglishHidden(step)"
                                    class="text-2xl sm:text-[28px] font-extrabold text-slate-900 leading-snug tracking-tight m-0 select-text"
                                    x-text="(step.phrases || []).join(' / ')"
                                ></h2>

                                <div
                                    x-show="isEnglishHidden(step)"
                                    class="inline-block px-4 py-3 rounded-2xl bg-slate-100 border border-slate-200 text-sm italic text-slate-500 font-medium"
                                >
                                    Listen carefully without reading
                                </div>
                            </div>

                            {{-- Malayalam Meaning --}}
                            <div
                                x-show="step.meaningMalayalam && isMalayalamShown(step)"
                                x-transition
                                class="mt-3 px-4 py-2 rounded-xl bg-emerald-600/10 text-emerald-700 text-sm font-semibold leading-snug"
                                x-text="step.meaningMalayalam"
                            ></div>

                            {{-- Subtle Toggle Pills --}}
                            <div class="mt-4 flex flex-wrap items-center justify-center gap-2">
                                <button
                                    type="button"
                                    x-show="step.meaningMalayalam"
                                    @click="toggleMalayalam(step)"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border transition-all cursor-pointer"
                                    :class="isMalayalamShown(step)
                                        ? 'bg-emerald-600/12 border-emerald-600/40 text-emerald-700'
                                        : 'bg-slate-200/60 border-slate-300/70 text-slate-600 hover:bg-slate-200'"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/></svg>
                                    <span x-text="isMalayalamShown(step) ? 'Hide മലയാളം' : 'മലയാളം'"></span>
                                </button>

                                <button
                                    type="button"
                                    @click="toggleEnglish(step)"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border transition-all cursor-pointer"
                                    :class="isEnglishHidden(step)
                                        ? 'bg-indigo-600/12 border-indigo-600/40 text-indigo-700'
                                        : 'bg-slate-200/60 border-slate-300/70 text-slate-600 hover:bg-slate-200'"
                                >
                                    <svg x-show="!isEnglishHidden(step)" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                    <svg x-show="isEnglishHidden(step)" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span x-text="isEnglishHidden(step) ? 'Show Text' : 'Hide Text'"></span>
                                </button>
                            </div>

                            {{-- Prominent Centered Circular Play Hero Button (84px) --}}
                            <div class="mt-8 relative flex items-center justify-center w-[112px] h-[112px]">
                                <span
                                    x-show="playingStepKey === step.keyId"
                                    class="amter-listen-ripple absolute w-[84px] h-[84px] rounded-full bg-cyan-600/30 pointer-events-none"
                                ></span>
                                <button
                                    type="button"
                                    @click="toggleListenPlay(step)"
                                    class="relative z-10 w-[84px] h-[84px] rounded-full bg-cyan-600 hover:bg-cyan-700 active:scale-95 text-white flex items-center justify-center shadow-[0_12px_28px_-6px_rgba(8,145,178,0.55)] transition-all cursor-pointer border-0"
                                    :aria-label="playingStepKey === step.keyId ? 'Stop audio' : 'Play audio'"
                                >
                                    <svg x-show="playingStepKey !== step.keyId" class="w-9 h-9" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M13.5 4.06c0-1.336-1.616-2.005-2.56-1.06l-4.5 4.5H4.508c-1.141 0-2.318.664-2.66 1.905A9.76 9.76 0 0 0 1.5 12c0 .898.121 1.768.35 2.595.341 1.24 1.518 1.905 2.659 1.905h1.93l4.5 4.5c.945.945 2.561.276 2.561-1.06V4.06ZM18.584 5.106a.75.75 0 0 1 1.06 0c3.808 3.807 3.808 9.98 0 13.788a.75.75 0 0 1-1.06-1.06 8.25 8.25 0 0 0 0-11.668.75.75 0 0 1 0-1.06Z" />
                                        <path d="M15.932 7.757a.75.75 0 0 1 1.061 0 6 6 0 0 1 0 8.486.75.75 0 0 1-1.06-1.061 4.5 4.5 0 0 0 0-6.364.75.75 0 0 1 0-1.06Z" />
                                    </svg>
                                    <svg x-show="playingStepKey === step.keyId" class="w-9 h-9" fill="currentColor" viewBox="0 0 24 24">
                                        <rect x="6" y="6" width="12" height="12" rx="2.5" />
                                    </svg>
                                </button>
                            </div>

                            <p
                                class="mt-2 text-sm font-bold text-cyan-700 m-0"
                                x-text="playingStepKey === step.keyId ? 'Playing...' : 'Tap to Listen'"
                            ></p>

                            {{-- 0.75x Slow Toggle Pill --}}
                            <button
                                type="button"
                                @click="toggleSlowAndPlay(step)"
                                class="mt-3 inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold border transition-all cursor-pointer"
                                :class="slowSpeed
                                    ? 'bg-cyan-600/12 border-cyan-600/40 text-cyan-700'
                                    : 'bg-slate-200/60 border-slate-300/70 text-slate-600 hover:bg-slate-200'"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span x-text="slowSpeed ? '0.75x Slow · On' : '0.75x Slow'"></span>
                            </button>

                            {{-- Ready Prompt after Listening --}}
                            <div
                                x-show="hasListened(step) && playingStepKey !== step.keyId"
                                x-transition
                                @click="goToStep(idx + 1)"
                                class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-emerald-500/12 text-emerald-700 text-xs font-bold cursor-pointer hover:bg-emerald-500/20 transition-colors"
                            >
                                <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd"/></svg>
                                <span x-text="step.nextHint === 'Swipe up to practice speaking' ? 'Ready! Swipe up to practice speaking' : 'Ready! Swipe up to continue'"></span>
                            </div>
                        </div>
                    </template>

                    {{-- ==================== 3. CARD B — SPEAK PRACTICE STEP ==================== --}}
                    <template x-if="step.type === 'speak'">
                        <div class="w-full flex flex-col items-center text-center">
                            {{-- Top Eyebrow OR Prominent Animated Check / Red Try-Again Badge + Score Banner --}}
                            <template x-if="getSpeakFeedback(step)">
                                <div class="flex flex-col items-center gap-2">
                                    @if(request()->is('admin/*'))
    <script>
        window.tailwind = window.tailwind || {};
        window.tailwind.config = { corePlugins: { preflight: false } };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
@endif

<style>
                                        @keyframes amterBadgePop {
                                            0% { transform: scale(0.35) rotate(-10deg); opacity: 0; }
                                            65% { transform: scale(1.12) rotate(3deg); opacity: 1; }
                                            100% { transform: scale(1) rotate(0deg); opacity: 1; }
                                        }
                                        @keyframes amterBadgeShake {
                                            0% { transform: scale(0.5); opacity: 0; }
                                            45% { transform: scale(1.08) translateX(-4px); opacity: 1; }
                                            65% { transform: scale(1.02) translateX(4px); }
                                            82% { transform: scale(1) translateX(-2px); }
                                            100% { transform: scale(1) translateX(0); opacity: 1; }
                                        }
                                        @keyframes amterCheckDraw {
                                            0% { stroke-dashoffset: 36; }
                                            100% { stroke-dashoffset: 0; }
                                        }
                                        .amter-badge-pass { animation: amterBadgePop 0.55s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; }
                                        .amter-badge-retry { animation: amterBadgeShake 0.5s ease-out forwards; }
                                        .amter-check-stroke { stroke-dasharray: 36; stroke-dashoffset: 36; animation: amterCheckDraw 0.42s ease-out 0.15s forwards; }
                                    </style>

                                    {{-- >= 75%: Animated Mint-Green Scalloped Checkmark Badge --}}
                                    <template x-if="getSpeakFeedback(step).score >= 75">
                                        <div class="amter-badge-pass drop-shadow-md">
                                            <svg class="w-16 h-16" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M32 3.5C35.2 3.5 37.6 5.9 40.5 6.8C43.4 7.7 46.8 6.8 49.4 8.7C52 10.6 52.3 14.1 54.1 16.7C55.9 19.3 59.3 20.5 60.2 23.4C61.1 26.3 59.1 29.3 59.1 32.5C59.1 35.7 61.1 38.7 60.2 41.6C59.3 44.5 55.9 45.7 54.1 48.3C52.3 50.9 52 54.4 49.4 56.3C46.8 58.2 43.4 57.3 40.5 58.2C37.6 59.1 35.2 61.5 32 61.5C28.8 61.5 26.4 59.1 23.5 58.2C20.6 57.3 17.2 58.2 14.6 56.3C12 54.4 11.7 50.9 9.9 48.3C8.1 45.7 4.7 44.5 3.8 41.6C2.9 38.7 4.9 35.7 4.9 32.5C4.9 29.3 2.9 26.3 3.8 23.4C4.7 20.5 8.1 19.3 9.9 16.7C11.7 14.1 12 10.6 14.6 8.7C17.2 6.8 20.6 7.7 23.5 6.8C26.4 5.9 28.8 3.5 32 3.5Z" fill="#2CC59B"/>
                                                <path class="amter-check-stroke" d="M21 33.5L28.5 41L43.5 24" stroke="#FFFFFF" stroke-width="5.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </div>
                                    </template>

                                    {{-- < 75%: Animated Red Try-Again Badge --}}
                                    <template x-if="getSpeakFeedback(step).score < 75">
                                        <div class="amter-badge-retry drop-shadow-md">
                                            <svg class="w-16 h-16" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <circle cx="32" cy="32" r="28" fill="#EF4444"/>
                                                <path d="M42.5 27.5A11.5 11.5 0 1 0 43 35.5" stroke="#FFFFFF" stroke-width="4.5" stroke-linecap="round"/>
                                                <path d="M44 19.5V28.5H35" stroke="#FFFFFF" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </div>
                                    </template>

                                    <div
                                        class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border text-xs font-extrabold tracking-wide"
                                        :class="getSpeakFeedback(step).score >= 75
                                            ? 'bg-emerald-600/12 border-emerald-600/40 text-emerald-700'
                                            : 'bg-rose-600/12 border-rose-600/40 text-rose-700'"
                                    >
                                        <span x-text="formatScoreBanner(getSpeakFeedback(step).score)"></span>
                                    </div>
                                </div>
                            </template>

                            <template x-if="!getSpeakFeedback(step)">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-violet-600/12 text-violet-700 text-[11px] font-extrabold tracking-widest uppercase">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8.25 4.5a3.75 3.75 0 1 1 7.5 0v8.25a3.75 3.75 0 1 1-7.5 0V4.5Z"/><path d="M6 10.5a.75.75 0 0 1 .75.75v1.5a5.25 5.25 0 1 0 10.5 0v-1.5a.75.75 0 0 1 1.5 0v1.5a6.751 6.751 0 0 1-6 6.709v2.291h3a.75.75 0 0 1 0 1.5h-7.5a.75.75 0 0 1 0-1.5h3v-2.291a6.751 6.751 0 0 1-6-6.709v-1.5A.75.75 0 0 1 6 10.5Z"/></svg>
                                    SPEAK PRACTICE
                                </span>
                            </template>

                            <p
                                x-show="!getSpeakFeedback(step) && step.instructions"
                                class="mt-2 text-sm text-slate-500 font-medium leading-relaxed max-w-md m-0"
                                x-text="step.instructions"
                            ></p>

                            {{-- Target Phrase (compacts smoothly when feedback is visible) --}}
                            <div :class="getSpeakFeedback(step) ? 'mt-2.5' : 'mt-4'" class="w-full transition-all">
                                <h2
                                    x-show="!isEnglishHidden(step)"
                                    :class="getSpeakFeedback(step) ? 'text-lg sm:text-xl' : 'text-2xl sm:text-[26px]'"
                                    class="font-extrabold text-slate-900 leading-snug tracking-tight m-0 transition-all select-text"
                                    x-text="(step.phrases || []).join(' / ')"
                                ></h2>

                                <div
                                    x-show="isEnglishHidden(step)"
                                    class="inline-block px-4 py-2.5 rounded-2xl bg-slate-100 border border-slate-200 text-sm italic text-slate-500 font-medium"
                                >
                                    Speak from memory
                                </div>
                            </div>

                            {{-- Malayalam Meaning --}}
                            <div
                                x-show="step.meaningMalayalam && isMalayalamShown(step)"
                                x-transition
                                class="mt-2 px-3.5 py-1.5 rounded-xl bg-emerald-600/10 text-emerald-700 text-xs sm:text-sm font-semibold"
                                x-text="step.meaningMalayalam"
                            ></div>

                            {{-- Subtle Toggle Pills --}}
                            <div class="mt-2.5 flex flex-wrap items-center justify-center gap-2">
                                <button
                                    type="button"
                                    x-show="step.meaningMalayalam"
                                    @click="toggleMalayalam(step)"
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border transition-all cursor-pointer"
                                    :class="isMalayalamShown(step)
                                        ? 'bg-emerald-600/12 border-emerald-600/40 text-emerald-700'
                                        : 'bg-slate-200/60 border-slate-300/70 text-slate-600 hover:bg-slate-200'"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/></svg>
                                    <span x-text="isMalayalamShown(step) ? 'Hide മലയാളം' : 'മലയാളം'"></span>
                                </button>

                                <button
                                    type="button"
                                    @click="toggleEnglish(step)"
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border transition-all cursor-pointer"
                                    :class="isEnglishHidden(step)
                                        ? 'bg-indigo-600/12 border-indigo-600/40 text-indigo-700'
                                        : 'bg-slate-200/60 border-slate-300/70 text-slate-600 hover:bg-slate-200'"
                                >
                                    <span x-text="isEnglishHidden(step) ? 'Show Text' : 'Hide Text'"></span>
                                </button>
                            </div>

                            {{-- LIVE INTERIM RECOGNIZED SPEECH (Center Stage Above Mic Dock) --}}
                            <div
                                x-show="isListening && activeSpeakKey === step.keyId && liveInterimText"
                                x-transition
                                class="mt-3.5 w-full px-4 py-2.5 rounded-2xl bg-slate-100 border border-red-400/50 text-sm font-bold italic text-slate-800"
                                x-text="`“${liveInterimText}”`"
                            ></div>

                            {{-- PROMINENT CENTER-STAGE WORD-DIFF FEEDBACK CARD (Above Mic Dock) --}}
                            <template x-if="getSpeakFeedback(step)">
                                <div class="mt-3.5 w-full flex flex-col items-center">
                                    <div
                                        class="w-full rounded-2xl p-4 text-left text-white shadow-[0_10px_24px_-6px_rgba(79,70,229,0.4)]"
                                        style="background: linear-gradient(135deg, #5B4FE9 0%, #4F46E5 100%);"
                                    >
                                        <div class="flex items-center justify-between gap-2">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <span class="text-[10px] font-extrabold tracking-widest uppercase text-white/85 shrink-0">YOUR ATTEMPT</span>
                                                <span
                                                    x-show="getSpeakFeedback(step).rhythmLabel"
                                                    class="px-2 py-0.5 rounded-full bg-white/20 text-[10px] font-bold uppercase text-white truncate"
                                                    x-text="getSpeakFeedback(step).rhythmLabel"
                                                ></span>
                                            </div>
                                            <span
                                                class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold text-white shrink-0"
                                                :class="getSpeakFeedback(step).score >= 75 ? 'bg-emerald-500' : 'bg-white/25'"
                                                x-text="`${getSpeakFeedback(step).score}%`"
                                            ></span>
                                        </div>

                                        {{-- Word Chips --}}
                                        <div class="mt-2.5 flex flex-wrap gap-1.5">
                                            <template x-for="(chip, cIdx) in (getSpeakFeedback(step).diff || [])" :key="cIdx">
                                                <button
                                                    type="button"
                                                    @click="chip.status !== 'matched' ? playTts(chip.word, 0.75) : null"
                                                    class="px-2.5 py-1 rounded-lg text-xs font-bold text-white border-0 transition-transform"
                                                    :class="chip.status === 'matched'
                                                        ? 'bg-sky-500 cursor-default'
                                                        : 'bg-pink-500 hover:bg-pink-400 active:scale-95 cursor-pointer'"
                                                    x-text="chip.word"
                                                ></button>
                                            </template>
                                        </div>

                                        <p
                                            x-show="(getSpeakFeedback(step).diff || []).some(c => c.status !== 'matched')"
                                            class="mt-2 mb-0 text-[11px] font-semibold text-white/90"
                                        >
                                            Tap any pink word to hear it slowly (0.75x)
                                        </p>

                                        <p
                                            x-show="getSpeakFeedback(step).spoken"
                                            class="mt-1.5 mb-0 text-xs text-white/85 leading-snug"
                                        >
                                            Heard: &ldquo;<span x-text="getSpeakFeedback(step).spoken"></span>&rdquo;
                                        </p>
                                    </div>

                                    {{-- Coaching Hint --}}
                                    <p
                                        class="mt-2 mb-0 text-xs font-bold"
                                        :class="getSpeakFeedback(step).score >= 75 ? 'text-emerald-700' : 'text-amber-700'"
                                        x-text="getSpeakFeedback(step).score >= 75
                                            ? 'Swipe up to continue ↑ or tap mic to practice again'
                                            : 'Tap any pink word above to hear it slowly, then tap mic to retry'"
                                    ></p>
                                </div>
                            </template>

                            {{-- Status / Error Pill (when no feedback card yet) --}}
                            <div
                                x-show="speakStatusFor(step.keyId) && !getSpeakFeedback(step)"
                                class="mt-3.5 px-3.5 py-1.5 rounded-full text-xs font-bold"
                                :class="isListening && activeSpeakKey === step.keyId
                                    ? 'bg-red-600/10 text-red-600'
                                    : (speakErrorKey === step.keyId ? 'bg-rose-100 text-rose-700' : 'bg-indigo-600/10 text-indigo-700')"
                                x-text="speakStatusFor(step.keyId)"
                            ></div>

                            {{-- CENTERED HERO MIC + FLANKING SECONDARY CONTROLS --}}
                            <div
                                :class="getSpeakFeedback(step) ? 'mt-4' : 'mt-7'"
                                class="flex items-center justify-center gap-6 transition-all"
                            >
                                {{-- Left: Replay Audio --}}
                                <div class="flex flex-col items-center gap-1">
                                    <button
                                        type="button"
                                        @click="replaySpeakPhrase(step)"
                                        title="Replay phrase"
                                        class="w-[46px] h-[46px] rounded-full bg-slate-200/80 hover:bg-slate-300/80 active:scale-95 text-slate-700 flex items-center justify-center border-0 cursor-pointer transition-all"
                                    >
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M13.5 4.06c0-1.336-1.616-2.005-2.56-1.06l-4.5 4.5H4.508c-1.141 0-2.318.664-2.66 1.905A9.76 9.76 0 0 0 1.5 12c0 .898.121 1.768.35 2.595.341 1.24 1.518 1.905 2.659 1.905h1.93l4.5 4.5c.945.945 2.561.276 2.561-1.06V4.06ZM18.584 5.106a.75.75 0 0 1 1.06 0c3.808 3.807 3.808 9.98 0 13.788a.75.75 0 0 1-1.06-1.06 8.25 8.25 0 0 0 0-11.668.75.75 0 0 1 0-1.06Z" />
                                        </svg>
                                    </button>
                                    <span class="text-[11px] font-semibold text-slate-500">Replay</span>
                                </div>

                                {{-- Center: Hero Mic Button (86px idle -> 68px compact with feedback) --}}
                                <div
                                    class="relative flex items-center justify-center transition-all duration-200"
                                    :style="getSpeakFeedback(step) ? 'width: 86px; height: 86px;' : 'width: 112px; height: 112px;'"
                                >
                                    <span
                                        class="absolute rounded-full pointer-events-none transition-all duration-200"
                                        :class="isListening && activeSpeakKey === step.keyId
                                            ? 'bg-red-600 amter-mic-live-ring'
                                            : 'bg-violet-600 amter-mic-idle-ring'"
                                        :style="getSpeakFeedback(step) ? 'width: 70px; height: 70px;' : 'width: 88px; height: 88px;'"
                                    ></span>
                                    <button
                                        type="button"
                                        @click="toggleSpeechMatch(step)"
                                        class="relative z-10 rounded-full text-white flex items-center justify-center border-0 cursor-pointer active:scale-95 transition-all duration-200"
                                        :class="isListening && activeSpeakKey === step.keyId
                                            ? 'bg-red-600 shadow-[0_12px_28px_-6px_rgba(220,38,38,0.55)]'
                                            : 'bg-violet-600 hover:bg-violet-700 shadow-[0_12px_28px_-6px_rgba(124,58,237,0.5)]'"
                                        :style="getSpeakFeedback(step) ? 'width: 68px; height: 68px;' : 'width: 86px; height: 86px;'"
                                        :aria-label="isListening && activeSpeakKey === step.keyId ? 'Stop listening' : 'Tap to speak'"
                                    >
                                        <svg
                                            x-show="!(isListening && activeSpeakKey === step.keyId)"
                                            :class="getSpeakFeedback(step) ? 'w-8 h-8' : 'w-9 h-9'"
                                            fill="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path d="M8.25 4.5a3.75 3.75 0 1 1 7.5 0v8.25a3.75 3.75 0 1 1-7.5 0V4.5Z"/>
                                            <path d="M6 10.5a.75.75 0 0 1 .75.75v1.5a5.25 5.25 0 1 0 10.5 0v-1.5a.75.75 0 0 1 1.5 0v1.5a6.751 6.751 0 0 1-6 6.709v2.291h3a.75.75 0 0 1 0 1.5h-7.5a.75.75 0 0 1 0-1.5h3v-2.291a6.751 6.751 0 0 1-6-6.709v-1.5A.75.75 0 0 1 6 10.5Z"/>
                                        </svg>
                                        <svg
                                            x-show="isListening && activeSpeakKey === step.keyId"
                                            :class="getSpeakFeedback(step) ? 'w-8 h-8' : 'w-9 h-9'"
                                            fill="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <rect x="6" y="6" width="12" height="12" rx="2.5" />
                                        </svg>
                                    </button>
                                </div>

                                {{-- Right: Acoustic Mirror (record / play) --}}
                                <div class="flex flex-col items-center gap-1">
                                    <button
                                        type="button"
                                        @click="handleMirrorTap(step)"
                                        @contextmenu.prevent="toggleMirrorRecord(step)"
                                        :title="isRecordingMirror && mirrorStepKey === step.keyId ? 'Stop mirror recording' : (mirrorUrls[step.keyId] ? 'Play your voice mirror' : 'Record voice mirror')"
                                        class="w-[46px] h-[46px] rounded-full flex items-center justify-center border-0 cursor-pointer active:scale-95 transition-all"
                                        :class="isRecordingMirror && mirrorStepKey === step.keyId
                                            ? 'bg-red-600/15 text-red-600'
                                            : (mirrorUrls[step.keyId] ? 'bg-emerald-600/15 text-emerald-700 hover:bg-emerald-600/25' : 'bg-slate-200/80 text-slate-700 hover:bg-slate-300/80')"
                                    >
                                        <svg x-show="isRecordingMirror && mirrorStepKey === step.keyId" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><rect x="6" y="6" width="12" height="12" rx="2" /></svg>
                                        <svg x-show="!(isRecordingMirror && mirrorStepKey === step.keyId) && mirrorUrls[step.keyId]" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5.14v14l11-7-11-7z"/></svg>
                                        <svg x-show="!(isRecordingMirror && mirrorStepKey === step.keyId) && !mirrorUrls[step.keyId]" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19V5m4 11V8m4 7v-6M5 15V9"/></svg>
                                    </button>
                                    <span
                                        class="text-[11px] font-semibold"
                                        :class="isRecordingMirror && mirrorStepKey === step.keyId ? 'text-red-600' : 'text-slate-500'"
                                        x-text="isRecordingMirror && mirrorStepKey === step.keyId ? 'Stop' : (mirrorUrls[step.keyId] ? 'My Voice' : 'Mirror')"
                                    ></span>
                                </div>
                            </div>

                            <p
                                class="mt-1.5 mb-0 text-sm font-bold"
                                :class="isListening && activeSpeakKey === step.keyId ? 'text-red-600' : 'text-violet-700'"
                                x-text="isListening && activeSpeakKey === step.keyId
                                    ? 'Listening... Tap to stop'
                                    : (getSpeakFeedback(step) ? 'Tap Mic to Try Again' : (step.buttonLabel || 'Tap to Speak'))"
                            ></p>
                        </div>
                    </template>

                    {{-- ==================== 4. AUDIO STEP ==================== --}}
                    <template x-if="step.type === 'audio'">
                        <div class="w-full rounded-3xl bg-white border border-slate-200 shadow-sm p-6 flex flex-col items-center gap-4 text-center">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-500/10 text-indigo-700 text-[11px] font-extrabold tracking-widest uppercase">
                                Audio Lesson
                            </span>
                            <h3 class="text-lg font-extrabold text-slate-900 m-0" x-text="step.title || 'Lesson Audio'"></h3>
                            <audio
                                :src="step.mediaUrl"
                                controls
                                class="w-full"
                            ></audio>
                            <p
                                x-show="step.transcript"
                                class="text-sm text-slate-600 leading-relaxed m-0"
                                x-text="step.transcript"
                            ></p>
                        </div>
                    </template>

                    {{-- ==================== 5. RICH TEXT STEP ==================== --}}
                    <template x-if="step.type === 'rich_text'">
                        <div class="w-full flex flex-col items-center gap-4">
                            <template x-if="step.attachedImageUrl">
                                <img
                                    :src="step.attachedImageUrl"
                                    alt=""
                                    class="max-h-[190px] w-auto rounded-2xl object-contain border border-slate-200 shadow-sm"
                                />
                            </template>
                            <div class="w-full rounded-3xl bg-white border border-slate-200/90 shadow-sm p-6">
                                <h3
                                    x-show="step.title"
                                    class="text-base font-extrabold text-indigo-600 uppercase tracking-wide mb-3 mt-0"
                                    x-text="step.title"
                                ></h3>
                                <div
                                    class="amter-richtext-body text-[15px] sm:text-base text-slate-800 font-medium select-text"
                                    :class="step.alignment === 'center' ? 'text-center' : 'text-left'"
                                    x-html="step.htmlContent"
                                ></div>
                            </div>
                        </div>
                    </template>

                    {{-- ==================== 6. IMAGE STEP ==================== --}}
                    <template x-if="step.type === 'image'">
                        <div class="w-full flex flex-col items-center gap-3 text-center">
                            <h3 x-show="step.title" class="text-base font-extrabold text-slate-800 m-0" x-text="step.title"></h3>
                            <img
                                :src="step.mediaUrl"
                                alt=""
                                class="max-h-[55dvh] w-auto max-w-full rounded-2xl object-contain border border-slate-200 shadow-md"
                            />
                            <p x-show="step.caption && step.caption !== step.title" class="text-xs font-semibold text-slate-500 m-0" x-text="step.caption"></p>
                        </div>
                    </template>

                    {{-- ==================== 7. DICTATION STEP ==================== --}}
                    <template x-if="step.type === 'dictation'">
                        <div class="w-full rounded-3xl bg-white border border-slate-200 shadow-sm p-6 flex flex-col items-center gap-4 text-center">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-cyan-600/10 text-cyan-700 text-[11px] font-extrabold tracking-widest uppercase">
                                Dictation
                            </span>
                            <h3 class="text-lg font-extrabold text-slate-900 m-0" x-text="step.heading"></h3>
                            <p class="text-sm text-slate-500 m-0" x-text="step.instructions"></p>

                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    @click="playTts(step.correctSentence, 1.0)"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-cyan-600 hover:bg-cyan-700 text-white text-sm font-bold border-0 cursor-pointer shadow-md"
                                >
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5.14v14l11-7-11-7z"/></svg>
                                    Play Sentence
                                </button>
                                <button
                                    type="button"
                                    @click="playTts(step.correctSentence, 0.75)"
                                    class="px-3.5 py-2.5 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold border border-slate-200 cursor-pointer"
                                >
                                    0.75x Slow
                                </button>
                            </div>

                            <div class="w-full flex gap-2">
                                <input
                                    type="text"
                                    x-model="dictationInputs[step.keyId]"
                                    @keydown.enter.prevent="checkDictation(step)"
                                    enterkeyhint="done"
                                    placeholder="Type what you hear..."
                                    class="flex-1 rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-slate-50"
                                />
                                <button
                                    type="button"
                                    @click="checkDictation(step)"
                                    class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold border-0 cursor-pointer"
                                >Check</button>
                            </div>

                            <template x-if="dictationResults[step.keyId]">
                                <div class="w-full rounded-2xl p-3.5 text-left"
                                     :class="dictationResults[step.keyId].score >= 80 ? 'bg-emerald-50 border border-emerald-200' : 'bg-amber-50 border border-amber-200'">
                                    <div class="flex items-center justify-between text-xs font-extrabold mb-2"
                                         :class="dictationResults[step.keyId].score >= 80 ? 'text-emerald-700' : 'text-amber-700'">
                                        <span x-text="dictationResults[step.keyId].score >= 80 ? step.successMessage : 'Almost there — check highlighted words'"></span>
                                        <span x-text="`${dictationResults[step.keyId].score}%`"></span>
                                    </div>
                                    <div class="flex flex-wrap gap-1.5">
                                        <template x-for="(chip, cIdx) in dictationResults[step.keyId].diff" :key="cIdx">
                                            <span
                                                class="px-2 py-0.5 rounded-md text-xs font-bold"
                                                :class="chip.status === 'matched' ? 'bg-emerald-500 text-white' : 'bg-rose-500 text-white'"
                                                x-text="chip.word"
                                            ></span>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- ==================== 8. QUIZ STEP ==================== --}}
                    <template x-if="step.type === 'quiz'">
                        <div class="w-full rounded-3xl bg-white border border-slate-200 shadow-sm p-6 flex flex-col gap-3.5">
                            <span class="self-center inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-500/10 text-indigo-700 text-[11px] font-extrabold tracking-widest uppercase">
                                Quick Check
                            </span>
                            <p x-show="step.instructions" class="text-xs text-slate-500 text-center m-0" x-text="step.instructions"></p>
                            <h3 class="text-lg font-extrabold text-slate-900 text-center leading-snug m-0" x-text="step.question"></h3>

                            <div class="flex flex-col gap-2.5 mt-1">
                                <template x-for="(opt, oIdx) in (step.options || [])" :key="oIdx">
                                    <button
                                        type="button"
                                        @click="selectQuizOption(step, oIdx)"
                                        class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl border-2 text-left text-sm font-bold transition-all cursor-pointer"
                                        :class="quizSelections[step.keyId] === oIdx
                                            ? (opt.is_correct ? 'border-emerald-500 bg-emerald-50 text-emerald-900' : 'border-rose-500 bg-rose-50 text-rose-900')
                                            : (quizSelections[step.keyId] !== undefined && opt.is_correct ? 'border-emerald-400/70 bg-emerald-50/50 text-emerald-900' : 'border-slate-200 bg-white text-slate-800 hover:border-indigo-400 hover:bg-indigo-50/40')"
                                    >
                                        <span
                                            class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-black shrink-0"
                                            :class="quizSelections[step.keyId] === oIdx
                                                ? (opt.is_correct ? 'bg-emerald-500 text-white' : 'bg-rose-500 text-white')
                                                : 'bg-slate-200 text-slate-700'"
                                            x-text="String.fromCharCode(65 + oIdx)"
                                        ></span>
                                        <span class="flex-1" x-text="opt.text"></span>
                                    </button>
                                </template>
                            </div>

                            <p
                                x-show="quizSelections[step.keyId] !== undefined && step.explanation"
                                class="mt-1 mb-0 p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-600"
                                x-text="step.explanation"
                            ></p>
                        </div>
                    </template>

                    {{-- ==================== 9. CODE STEP ==================== --}}
                    <template x-if="step.type === 'code'">
                        <div class="w-full rounded-2xl bg-slate-900 text-slate-100 p-5 text-left font-mono text-sm overflow-x-auto shadow-md select-text">
                            <pre class="m-0 whitespace-pre-wrap" x-text="step.code"></pre>
                        </div>
                    </template>

                    {{-- ==================== 10. MASTERY SUMMARY STEP ==================== --}}
                    <template x-if="step.type === 'mastery'">
                        <div class="w-full flex flex-col items-center text-center">
                            <div class="w-[72px] h-[72px] rounded-full bg-emerald-500/15 flex items-center justify-center">
                                <svg class="w-9 h-9 text-emerald-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path fill-rule="evenodd" d="M5.166 2.621v.858c-1.035.148-2.059.33-3.071.543a.75.75 0 0 0-.584.859 6.753 6.753 0 0 0 6.138 5.6 6.73 6.73 0 0 0 2.743 1.346A6.707 6.707 0 0 1 9.279 15H8.54c-1.036 0-1.875.84-1.875 1.875V19.5h-.75a2.25 2.25 0 0 0-2.25 2.25c0 .414.336.75.75.75h15a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-2.25-2.25h-.75v-2.625c0-1.036-.84-1.875-1.875-1.875h-.739a6.706 6.706 0 0 1-1.112-3.173 6.73 6.73 0 0 0 2.743-1.347 6.753 6.753 0 0 0 6.139-5.6.75.75 0 0 0-.585-.858 47.077 47.077 0 0 0-3.07-.543V2.62a.75.75 0 0 0-.658-.744 49.22 49.22 0 0 0-6.093-.377c-2.063 0-4.096.128-6.093.377a.75.75 0 0 0-.657.744Zm0 2.629c0 1.196.312 2.32.857 3.294A5.266 5.266 0 0 1 3.16 5.337a45.6 45.6 0 0 1 2.006-.343v.256Zm13.5 0v-.256c.674.1 1.343.214 2.006.343a5.265 5.265 0 0 1-2.863 3.207 6.72 6.72 0 0 0 .857-3.294Z" clip-rule="evenodd"/>
                                </svg>
                            </div>

                            <h2
                                class="mt-4 mb-1 text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight"
                                x-text="isCompleted ? 'Unit Completed!' : 'Unit Complete'"
                            ></h2>

                            <p class="text-sm font-medium text-slate-500 m-0" x-text="unitTitle"></p>

                            {{-- Before vs. After Scores --}}
                            <template x-if="baselineScore !== null || latestScore !== null">
                                <div class="mt-6 w-full flex items-center gap-2.5">
                                    <div class="flex-1 rounded-2xl bg-white border border-slate-200 px-4 py-3.5">
                                        <div class="text-[11px] font-bold tracking-wider uppercase text-slate-500">BEFORE</div>
                                        <div class="mt-1 text-2xl font-extrabold text-slate-900" x-text="baselineScore !== null ? `${baselineScore}%` : '—'"></div>
                                    </div>
                                    <svg class="w-5 h-5 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                    <div class="flex-1 rounded-2xl bg-emerald-500/10 border border-emerald-500/40 px-4 py-3.5">
                                        <div class="text-[11px] font-bold tracking-wider uppercase text-emerald-700">AFTER</div>
                                        <div class="mt-1 text-2xl font-extrabold text-emerald-700" x-text="latestScore !== null ? `${latestScore}%` : '—'"></div>
                                    </div>
                                </div>
                            </template>

                            {{-- Manual Mark Complete fallback (if authenticated & not yet completed) --}}
                            <template x-if="isAuthenticated && !isCompleted && completeUrl">
                                <button
                                    type="button"
                                    @click="markComplete()"
                                    :disabled="isCompleting"
                                    class="mt-6 w-full py-3 rounded-2xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-sm border border-indigo-200 flex items-center justify-center gap-2 cursor-pointer transition-all disabled:opacity-60"
                                >
                                    <span x-text="isCompleting ? 'Saving progress...' : 'Mark Unit Complete'"></span>
                                </button>
                            </template>

                            {{-- Prominent Next Unit CTA OR Back to Dashboard --}}
                            <template x-if="resolvedNextUrl">
                                <div class="mt-5 w-full flex flex-col items-center">
                                    <button
                                        type="button"
                                        @click="advanceToNextUnit()"
                                        :disabled="isTransitioningUnit"
                                        class="w-full py-3.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 active:scale-[0.99] text-white font-bold text-base shadow-lg shadow-indigo-600/25 flex items-center justify-center gap-2 border-0 cursor-pointer transition-all disabled:opacity-60"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                        <span x-text="isTransitioningUnit ? 'Loading Next Unit...' : 'Next Unit'"></span>
                                    </button>
                                    <p class="mt-2.5 mb-0 text-xs font-medium text-slate-400">
                                        Or swipe up to continue seamlessly
                                    </p>
                                </div>
                            </template>

                            <template x-if="!resolvedNextUrl">
                                <a
                                    href="{{ $homeUrl }}"
                                    class="mt-6 w-full py-3.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-base shadow-lg shadow-indigo-600/25 flex items-center justify-center gap-2 no-underline transition-all"
                                >
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.47 3.841a.75.75 0 0 1 1.06 0l8.69 8.69a.75.75 0 1 0 1.06-1.061l-8.689-8.69a2.25 2.25 0 0 0-3.182 0l-8.69 8.69a.75.75 0 1 0 1.061 1.06l8.69-8.689Z" /><path d="m12 5.432 8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 0 1-.75-.75v-4.5a.75.75 0 0 0-.75-.75h-3a.75.75 0 0 0-.75.75V21a.75.75 0 0 1-.75.75H5.625a1.875 1.875 0 0 1-1.875-1.875v-6.198a2.29 2.29 0 0 0 .091-.086L12 5.432Z" /></svg>
                                    <span>Back to Dashboard</span>
                                </a>
                            </template>
                        </div>
                    </template>

                    {{-- ==================== 11. SEAMLESS NEXT-UNIT SENTINEL ==================== --}}
                    <template x-if="step.type === 'next_sentinel'">
                        <div class="flex flex-col items-center justify-center gap-3 text-center">
                            <div class="w-8 h-8 rounded-full border-[3px] border-indigo-200 border-t-indigo-600 animate-spin"></div>
                            <p class="text-sm font-semibold text-slate-500 m-0">Loading next unit...</p>
                        </div>
                    </template>
                </div>
            </section>
        </template>
    </main>

    {{-- Subtle Tappable Bottom Swipe-Up Cue (for Desktop & Accessibility) --}}
    <footer
        x-show="showBottomCue"
        class="shrink-0 z-20 w-full flex items-center justify-center pb-2.5 pt-1 bg-slate-50/90 backdrop-blur-sm"
    >
        <button
            type="button"
            @click="goToStep(currentIndex + 1)"
            class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-semibold text-slate-500 hover:text-slate-800 hover:bg-slate-200/70 active:scale-95 transition-all border-0 bg-transparent cursor-pointer"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
            </svg>
            <span x-text="currentStep?.nextHint || 'Swipe up for next'"></span>
        </button>
    </footer>
</div>

<script>
function amterStepPlayer(config) {
    return {
        steps: config.steps || [],
        ttsEndpoint: config.ttsEndpoint || '/tts-stream',
        completeUrl: config.completeUrl || null,
        groupUnitIdsCsv: config.groupUnitIdsCsv || '',
        trackId: config.trackId || null,
        csrfToken: config.csrfToken || '',
        nextGroupUrl: config.nextGroupUrl || null,
        prevGroupUrl: config.prevGroupUrl || null,
        homeUrl: config.homeUrl || '/',
        storageKey: config.storageKey || null,
        isAuthenticated: !!config.isAuthenticated,
        unitTitle: config.unitTitle || 'Lesson',

        currentIndex: 0,
        menuOpen: false,
        slowSpeed: false,
        loopVideo: false,
        nativeDurationMs: 4500,
        videoPlayingMap: {},

        // Completion & Mastery
        isCompleted: !!config.isAlreadyCompleted,
        isCompleting: false,
        autoCompleteAttempted: !!config.isAlreadyCompleted,
        isTransitioningUnit: false,
        nextUrlFromCompletion: null,
        baselineScore: null,
        latestScore: null,
        toastMessage: '',
        toastTimer: null,

        // Per-step / per-turn state
        turnStates: {},
        reveals: {},
        dictationInputs: {},
        dictationResults: {},
        quizSelections: {},

        // Media & Speech
        ttsAudio: null,
        playingStepKey: null,
        isListening: false,
        activeSpeakKey: null,
        liveInterimText: '',
        speakStatusMap: {},
        speakErrorKey: null,
        speechStartedAt: 0,
        recognition: null,
        voiceTimeout: null,

        // Acoustic Mirror
        isRecordingMirror: false,
        mirrorStepKey: null,
        mediaRecorder: null,
        recordedChunks: [],
        mirrorUrls: {},

        // Scroll sync
        scrollDebounce: null,

        get contentStepCount() {
            if (!this.steps.length) return 0;
            return this.steps[this.steps.length - 1].type === 'next_sentinel'
                ? this.steps.length - 1
                : this.steps.length;
        },

        get displayStep() {
            if (!this.contentStepCount) return 1;
            return Math.min(Math.max(this.currentIndex + 1, 1), this.contentStepCount);
        },

        get progressPercent() {
            if (!this.contentStepCount) return 100;
            return Math.round((this.displayStep / this.contentStepCount) * 100);
        },

        get currentStep() {
            return this.steps[this.currentIndex] || null;
        },

        get resolvedNextUrl() {
            return this.nextUrlFromCompletion || this.nextGroupUrl;
        },

        get showBottomCue() {
            const step = this.currentStep;
            if (!step) return false;
            return this.currentIndex < this.steps.length - 1
                && step.type !== 'mastery'
                && step.type !== 'next_sentinel';
        },

        init() {
            this.loadProgress();
            this.$nextTick(() => {
                if (this.currentIndex > 0) {
                    this.scrollToStep(this.currentIndex, 'instant');
                }
            });
        },

        // ---------- Navigation & Media Lifecycle ----------
        onScrollerScroll() {
            const scroller = this.$refs.scroller;
            if (!scroller || !scroller.clientHeight) return;
            const rawIdx = Math.round(scroller.scrollTop / scroller.clientHeight);
            const clamped = Math.max(0, Math.min(rawIdx, this.steps.length - 1));
            if (clamped !== this.currentIndex) {
                this.onStepChanged(clamped);
            }
        },

        goToStep(idx) {
            const target = Math.max(0, Math.min(idx, this.steps.length - 1));
            this.scrollToStep(target, 'smooth');
        },

        scrollToStep(idx, behavior = 'smooth') {
            const scroller = this.$refs.scroller;
            if (!scroller) return;
            const top = idx * scroller.clientHeight;
            scroller.scrollTo({ top, behavior });
        },

        handleKeydown(event) {
            const tag = (event.target?.tagName || '').toLowerCase();
            if (tag === 'input' || tag === 'textarea' || event.target?.isContentEditable) {
                return;
            }
            if (event.key === 'ArrowDown' || event.key === 'PageDown') {
                event.preventDefault();
                this.goToStep(this.currentIndex + 1);
            } else if (event.key === 'ArrowUp' || event.key === 'PageUp') {
                event.preventDefault();
                this.goToStep(this.currentIndex - 1);
            } else if (event.key === 'Escape' && this.menuOpen) {
                this.menuOpen = false;
            }
        },

        onStepChanged(newIndex) {
            this.stopAllMedia();
            this.currentIndex = newIndex;
            const step = this.steps[newIndex];
            if (!step) return;

            if (step.type === 'next_sentinel') {
                this.advanceToNextUnit();
                return;
            }

            this.saveProgress();

            if (step.type === 'mastery') {
                this.maybeAutoComplete();
            }
        },

        stopAllMedia() {
            // Stop TTS audio
            if (this.ttsAudio) {
                try { this.ttsAudio.pause(); this.ttsAudio.currentTime = 0; } catch (_) {}
                this.ttsAudio = null;
            }
            this.playingStepKey = null;

            if (typeof window.stopAmterSpeech === 'function') {
                try { window.stopAmterSpeech(); } catch (_) {}
            }

            // Pause all video & audio tags inside this player
            const scroller = this.$refs.scroller;
            if (scroller) {
                scroller.querySelectorAll('video, audio').forEach((el) => {
                    try {
                        if (!el.paused) el.pause();
                    } catch (_) {}
                });
            }
            this.videoPlayingMap = {};

            // Stop speech recognition & acoustic mirror recording
            this.stopSpeechRecognition();
            this.stopMirrorRecording();
        },

        // ---------- Progress Persistence ----------
        loadProgress() {
            if (!this.storageKey) return;
            try {
                const raw = window.localStorage.getItem(this.storageKey);
                if (!raw) return;
                const saved = JSON.parse(raw);
                if (!saved || typeof saved !== 'object') return;

                const maxContentIdx = Math.max(0, this.contentStepCount - 1);
                if (typeof saved.currentIndex === 'number') {
                    this.currentIndex = Math.max(0, Math.min(saved.currentIndex, maxContentIdx));
                }
                if (saved.reveals && typeof saved.reveals === 'object') this.reveals = saved.reveals;
                if (saved.turnStates && typeof saved.turnStates === 'object') this.turnStates = saved.turnStates;
                if (saved.dictationInputs && typeof saved.dictationInputs === 'object') this.dictationInputs = saved.dictationInputs;
                if (saved.dictationResults && typeof saved.dictationResults === 'object') this.dictationResults = saved.dictationResults;
                if (saved.quizSelections && typeof saved.quizSelections === 'object') this.quizSelections = saved.quizSelections;
                if (typeof saved.baselineScore === 'number') this.baselineScore = saved.baselineScore;
                if (typeof saved.latestScore === 'number') this.latestScore = saved.latestScore;
            } catch (_) {}
        },

        saveProgress() {
            if (!this.storageKey) return;
            try {
                const payload = {
                    currentIndex: Math.min(this.currentIndex, Math.max(0, this.contentStepCount - 1)),
                    reveals: this.reveals,
                    turnStates: this.turnStates,
                    dictationInputs: this.dictationInputs,
                    dictationResults: this.dictationResults,
                    quizSelections: this.quizSelections,
                    baselineScore: this.baselineScore,
                    latestScore: this.latestScore,
                };
                window.localStorage.setItem(this.storageKey, JSON.stringify(payload));
            } catch (_) {}
        },

        restartUnit() {
            this.stopAllMedia();
            this.turnStates = {};
            this.reveals = {};
            this.dictationInputs = {};
            this.dictationResults = {};
            this.quizSelections = {};
            this.baselineScore = null;
            this.latestScore = null;
            this.speakStatusMap = {};
            this.speakErrorKey = null;
            if (this.storageKey) {
                try { window.localStorage.removeItem(this.storageKey); } catch (_) {}
            }
            this.currentIndex = 0;
            this.scrollToStep(0, 'smooth');
        },

        // ---------- Video Helpers ----------
        captureNativeDuration(event) {
            const d = event.target?.duration;
            if (d && isFinite(d) && d > 0) {
                this.nativeDurationMs = Math.min(d, 5) * 1000;
            }
        },

        handleVideoTimeUpdate(event, keyId) {
            const video = event.target;
            if (!video) return;
            if (this.loopVideo && video.currentTime >= 5) {
                video.currentTime = 0;
                video.play().catch(() => {});
            }
        },

        toggleStepVideo(idx, keyId) {
            const video = this.$refs.scroller?.querySelector(`video[data-step-video="${idx}"]`);
            if (!video) return;
            if (video.paused) {
                video.playbackRate = this.slowSpeed ? 0.75 : 1.0;
                video.play().catch(() => {});
            } else {
                video.pause();
            }
        },

        toggleVideoSlow(idx) {
            this.slowSpeed = !this.slowSpeed;
            const video = this.$refs.scroller?.querySelector(`video[data-step-video="${idx}"]`);
            if (video) {
                video.playbackRate = this.slowSpeed ? 0.75 : 1.0;
            }
        },

        // ---------- Reveal Toggles ----------
        stateKeyFor(step) {
            return step.turnKey || step.keyId;
        },

        isEnglishHidden(step) {
            const k = `${this.stateKeyFor(step)}:en`;
            if (k in this.reveals) {
                return !this.reveals[k];
            }
            return !!step.hideEnglishByDefault;
        },

        toggleEnglish(step) {
            const k = `${this.stateKeyFor(step)}:en`;
            const currentlyHidden = this.isEnglishHidden(step);
            this.reveals[k] = currentlyHidden; // true => show English
            this.saveProgress();
        },

        isMalayalamShown(step) {
            const k = `${this.stateKeyFor(step)}:ml`;
            return !!this.reveals[k];
        },

        toggleMalayalam(step) {
            const k = `${this.stateKeyFor(step)}:ml`;
            this.reveals[k] = !this.reveals[k];
            this.saveProgress();
        },

        // ---------- Card A: Listen TTS ----------
        hasListened(step) {
            return !!this.turnStates[this.stateKeyFor(step)]?.listened;
        },

        playAudioUrl(url, rate = 1.0, stepKey = null) {
            if (!url) return Promise.resolve();
            return new Promise((resolve) => {
                if (this.ttsAudio) {
                    try { this.ttsAudio.pause(); } catch (_) {}
                }
                const audio = new Audio(url);
                audio.playbackRate = rate;
                this.ttsAudio = audio;
                this.playingStepKey = stepKey;

                const finish = () => {
                    if (this.ttsAudio === audio) {
                        this.playingStepKey = null;
                    }
                    resolve();
                };
                audio.onended = finish;
                audio.onerror = finish;
                audio.play().catch(finish);
            });
        },

        playTts(text, rate = 1.0, stepKey = null) {
            const clean = (text || '').trim();
            if (!clean) return Promise.resolve();
            const url = `${this.ttsEndpoint.replace(/\/$/, '')}?text=${encodeURIComponent(clean)}&tl=en-US`;
            return this.playAudioUrl(url, rate, stepKey);
        },

        async toggleListenPlay(step, overrideRate = null) {
            const phrase = (step.phrases || [])[0] || '';
            if (!phrase.trim()) return;

            if (this.playingStepKey === step.keyId && overrideRate === null) {
                if (this.ttsAudio) {
                    try { this.ttsAudio.pause(); } catch (_) {}
                }
                this.playingStepKey = null;
                return;
            }

            const rate = overrideRate ?? (this.slowSpeed ? 0.75 : 1.0);
            const sKey = this.stateKeyFor(step);
            this.turnStates[sKey] = {
                ...(this.turnStates[sKey] || {}),
                listened: true,
            };
            this.saveProgress();

            await this.playTts(phrase, rate, step.keyId);
        },

        toggleSlowAndPlay(step) {
            this.slowSpeed = !this.slowSpeed;
            this.toggleListenPlay(step, this.slowSpeed ? 0.75 : 1.0);
        },

        // ---------- Card B: Speak Practice ----------
        getSpeakFeedback(step) {
            const st = this.turnStates[this.stateKeyFor(step)];
            if (st && Array.isArray(st.diff) && st.diff.length > 0) {
                return st;
            }
            return null;
        },

        formatScoreBanner(score) {
            if (score >= 90) return `${score}% · SPOT ON!`;
            if (score >= 75) return `${score}% · GREAT JOB!`;
            return `${score}% · ALMOST THERE`;
        },

        speakStatusFor(stepKey) {
            return this.speakStatusMap[stepKey] || null;
        },

        replaySpeakPhrase(step) {
            const phrase = (step.phrases || [])[0] || '';
            if (!phrase) return;
            this.stopSpeechRecognition();
            this.playTts(phrase, this.slowSpeed ? 0.75 : 1.0);
        },

        isMobileDevice() {
            const ua = navigator.userAgent || '';
            const mobileUa = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Mobile/i.test(ua);
            const coarseTouch = (navigator.maxTouchPoints > 1) && window.matchMedia && window.matchMedia('(pointer: coarse)').matches;
            return mobileUa || coarseTouch;
        },

        async toggleSpeechMatch(step) {
            if (this.isListening && this.activeSpeakKey === step.keyId) {
                this.stopSpeechRecognition();
                if (!this.liveInterimText && !this.getSpeakFeedback(step)) {
                    this.speakStatusMap[step.keyId] = "Didn't catch that — tap mic to try again.";
                } else {
                    this.speakStatusMap[step.keyId] = null;
                }
                return;
            }

            this.stopAllMedia();
            this.speakErrorKey = null;
            this.activeSpeakKey = step.keyId;
            this.isListening = true;
            this.liveInterimText = '';
            this.speakStatusMap[step.keyId] = 'Listening... speak now';
            this.speechStartedAt = performance.now();

            // Clear previous feedback for fresh attempt while listening
            const sKey = this.stateKeyFor(step);
            if (this.turnStates[sKey]) {
                const { diff, score, spoken, rhythmLabel, ...rest } = this.turnStates[sKey];
                this.turnStates[sKey] = rest;
            }

            const SpeechRec = window.SpeechRecognition || window.webkitSpeechRecognition;
            if (!SpeechRec) {
                this.isListening = false;
                this.speakErrorKey = step.keyId;
                this.speakStatusMap[step.keyId] = 'Speech recognition is not supported in this browser. Try Chrome or Safari.';
                return;
            }

            const candidates = (Array.isArray(step.phrases) && step.phrases.length) ? step.phrases : [''];
            const primaryTarget = candidates[0] || '';
            const rec = new SpeechRec();
            this.recognition = rec;
            rec.lang = 'en-US';
            rec.interimResults = true;
            rec.continuous = false;
            rec.maxAlternatives = 1;

            let hasResult = false;

            rec.onresult = (event) => {
                let transcript = '';
                for (let i = 0; i < event.results.length; i++) {
                    transcript += event.results[i][0].transcript + ' ';
                }
                const cleaned = transcript.trim();
                if (!cleaned) return;

                hasResult = true;
                this.liveInterimText = cleaned;

                let best = { diff: [], score: -1 };
                for (const candidate of candidates) {
                    const res = this.computeWordDiff(candidate, cleaned);
                    if (res.score > best.score) best = res;
                }

                const isFinal = !!event.results[event.results.length - 1]?.isFinal;
                const passed = best.score >= 75;

                if (isFinal || passed) {
                    const elapsedMs = Math.max(250, performance.now() - this.speechStartedAt);
                    const rhythmLabel = this.computeRhythmLabel(primaryTarget, cleaned, elapsedMs);

                    this.turnStates[sKey] = {
                        ...(this.turnStates[sKey] || {}),
                        spoken: cleaned,
                        diff: best.diff,
                        score: best.score,
                        rhythmLabel,
                        hasCompleted: passed || !!this.turnStates[sKey]?.hasCompleted,
                    };

                    if (step.isBaseline || this.baselineScore === null) {
                        if (this.baselineScore === null) {
                            this.baselineScore = best.score;
                        }
                    }
                    this.latestScore = best.score;
                    this.speakStatusMap[step.keyId] = null;
                    this.saveProgress();

                    if (passed && !isFinal) {
                        this.stopSpeechRecognition();
                    }
                }
            };

            rec.onend = () => {
                if (this.recognition === rec) {
                    this.recognition = null;
                    this.isListening = false;
                    // If we had interim text that didn't trigger isFinal, score it now!
                    if (this.liveInterimText && !this.getSpeakFeedback(step)) {
                        const cleaned = this.liveInterimText.trim();
                        let best = { diff: [], score: -1 };
                        for (const candidate of candidates) {
                            const res = this.computeWordDiff(candidate, cleaned);
                            if (res.score > best.score) best = res;
                        }
                        const elapsedMs = Math.max(250, performance.now() - this.speechStartedAt);
                        const rhythmLabel = this.computeRhythmLabel(primaryTarget, cleaned, elapsedMs);
                        this.turnStates[sKey] = {
                            ...(this.turnStates[sKey] || {}),
                            spoken: cleaned,
                            diff: best.diff,
                            score: best.score,
                            rhythmLabel,
                        };
                        if (this.baselineScore === null) this.baselineScore = best.score;
                        this.latestScore = best.score;
                        this.speakStatusMap[step.keyId] = null;
                        this.saveProgress();
                    } else if (!hasResult && !this.speakErrorKey) {
                        this.speakStatusMap[step.keyId] = "Didn't catch that — tap mic to try again.";
                    }
                    this.liveInterimText = '';
                }
            };

            rec.onerror = (e) => {
                const code = e?.error || 'unknown';
                const messages = {
                    'not-allowed': 'Microphone access denied. Allow mic permission in browser settings.',
                    'service-not-allowed': 'Speech recognition blocked. Allow microphone access and retry.',
                    'audio-capture': 'Microphone is busy or unavailable.',
                    'no-speech': "Didn't catch any speech — tap mic and try again.",
                    'network': 'Speech recognition needs an internet connection.',
                };
                if (code !== 'aborted') {
                    this.speakErrorKey = step.keyId;
                    this.speakStatusMap[step.keyId] = messages[code] || 'Could not start voice recognition. Try again.';
                }
                if (this.recognition === rec) {
                    this.stopSpeechRecognition();
                }
            };

            try {
                rec.start();
                this.voiceTimeout = setTimeout(() => {
                    if (this.isListening && this.activeSpeakKey === step.keyId) {
                        this.stopSpeechRecognition();
                    }
                }, 10000);
            } catch (_) {
                this.speakErrorKey = step.keyId;
                this.speakStatusMap[step.keyId] = 'Could not start speech recognition. Tap mic again.';
                this.stopSpeechRecognition();
            }
        },

        stopSpeechRecognition() {
            this.isListening = false;
            if (this.voiceTimeout) {
                clearTimeout(this.voiceTimeout);
                this.voiceTimeout = null;
            }
            if (this.recognition) {
                const rec = this.recognition;
                this.recognition = null;
                try { rec.stop(); } catch (_) {}
            }
        },

        // ---------- Acoustic Mirror (Record & Playback) ----------
        handleMirrorTap(step) {
            if (this.isRecordingMirror && this.mirrorStepKey === step.keyId) {
                this.stopMirrorRecording();
                return;
            }
            if (this.mirrorUrls[step.keyId]) {
                this.playAudioUrl(this.mirrorUrls[step.keyId], 1.0);
                return;
            }
            this.toggleMirrorRecord(step);
        },

        async toggleMirrorRecord(step) {
            if (this.isRecordingMirror) {
                this.stopMirrorRecording();
                return;
            }
            this.stopAllMedia();

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                this.speakStatusMap[step.keyId] = 'Microphone recording is not supported in this browser.';
                return;
            }

            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                this.recordedChunks = [];
                this.mirrorStepKey = step.keyId;
                this.isRecordingMirror = true;
                this.speakStatusMap[step.keyId] = 'Recording your voice mirror... tap Stop when done';

                const recorder = new MediaRecorder(stream);
                this.mediaRecorder = recorder;
                recorder.ondataavailable = (e) => {
                    if (e.data && e.data.size > 0) this.recordedChunks.push(e.data);
                };
                recorder.onstop = () => {
                    stream.getTracks().forEach((t) => t.stop());
                    this.isRecordingMirror = false;
                    if (this.recordedChunks.length) {
                        const blob = new Blob(this.recordedChunks, { type: 'audio/webm' });
                        const url = URL.createObjectURL(blob);
                        this.mirrorUrls[step.keyId] = url;
                        this.speakStatusMap[step.keyId] = 'Voice mirror saved — tap My Voice to play';
                    }
                };
                recorder.start();
            } catch (_) {
                this.isRecordingMirror = false;
                this.speakStatusMap[step.keyId] = 'Mic permission required for Acoustic Mirror.';
            }
        },

        stopMirrorRecording() {
            this.isRecordingMirror = false;
            if (this.mediaRecorder && this.mediaRecorder.state !== 'inactive') {
                try { this.mediaRecorder.stop(); } catch (_) {}
            }
            this.mediaRecorder = null;
        },

        // ---------- Rhythm & Word-Diff Engine (Android Parity) ----------
        computeRhythmLabel(target, spoken, elapsedMs) {
            const words = (spoken || '').trim().split(/\s+/).filter(Boolean).length;
            if (words === 0 || elapsedMs < 250) return 'Clear pace';
            const targetWords = (target || '').trim().split(/\s+/).filter(Boolean).length;
            const expectedSec = Math.max(0.8, Math.min(12.0, targetWords / 2.2));
            const actualSec = elapsedMs / 1000.0;
            const ratio = actualSec / expectedSec;
            if (ratio >= 0.65 && ratio <= 1.55) return 'Steady pace';
            if (ratio < 0.65) return 'Fast pace';
            return 'Try slightly faster';
        },

        computeWordDiff(targetText, spokenText) {
            const norm = (s) => (s || '')
                .toLowerCase()
                .replace(/[^a-z0-9'\s]/g, ' ')
                .trim()
                .split(/\s+/)
                .filter(Boolean);

            const targetWords = norm(targetText);
            const rawSpoken = norm(spokenText);

            // Merge adjacent spoken words when they form a compound target word (e.g. "every body" -> "everybody")
            const spokenWords = [];
            let idx = 0;
            while (idx < rawSpoken.length) {
                if (idx + 1 < rawSpoken.length) {
                    const combined = rawSpoken[idx] + rawSpoken[idx + 1];
                    if (targetWords.includes(combined)) {
                        spokenWords.push(combined);
                        idx += 2;
                        continue;
                    }
                }
                spokenWords.push(rawSpoken[idx]);
                idx += 1;
            }

            const n = targetWords.length;
            const m = spokenWords.length;
            if (n === 0) return { diff: [], score: 0 };

            const dp = Array.from({ length: n + 1 }, () => Array(m + 1).fill(0));
            for (let i = 1; i <= n; i++) {
                for (let j = 1; j <= m; j++) {
                    dp[i][j] = targetWords[i - 1] === spokenWords[j - 1]
                        ? dp[i - 1][j - 1] + 1
                        : Math.max(dp[i - 1][j], dp[i][j - 1]);
                }
            }

            const diff = [];
            let i = n, j = m, matched = 0;
            while (i > 0 || j > 0) {
                if (i > 0 && j > 0 && targetWords[i - 1] === spokenWords[j - 1]) {
                    diff.unshift({ word: targetWords[i - 1], status: 'matched' });
                    matched++;
                    i--; j--;
                } else if (j > 0 && (i === 0 || dp[i][j - 1] >= dp[i - 1][j])) {
                    j--;
                } else {
                    diff.unshift({ word: targetWords[i - 1], status: 'missing' });
                    i--;
                }
            }

            const score = Math.min(100, Math.max(0, Math.round((matched / n) * 100)));
            return { diff, score };
        },

        // ---------- Dictation & Quiz ----------
        checkDictation(step) {
            const spoken = (this.dictationInputs[step.keyId] || '').trim();
            const res = this.computeWordDiff(step.correctSentence || '', spoken);
            this.dictationResults[step.keyId] = res;
            this.saveProgress();
        },

        selectQuizOption(step, oIdx) {
            this.quizSelections[step.keyId] = oIdx;
            this.saveProgress();
        },

        // ---------- Mastery Auto-Completion & Seamless Next Unit ----------
        showToast(msg) {
            this.toastMessage = msg;
            if (this.toastTimer) clearTimeout(this.toastTimer);
            this.toastTimer = setTimeout(() => { this.toastMessage = ''; }, 3200);
        },

        maybeAutoComplete() {
            if (this.isCompleted || this.isCompleting || this.autoCompleteAttempted) return;
            if (!this.isAuthenticated || !this.completeUrl) return;
            this.autoCompleteAttempted = true;
            this.markComplete({ silent: false });
        },

        async markComplete({ silent = false } = {}) {
            if (!this.completeUrl || this.isCompleting) return;
            this.isCompleting = true;
            try {
                const body = new URLSearchParams();
                body.append('_token', this.csrfToken);
                if (this.groupUnitIdsCsv) body.append('group_unit_ids', this.groupUnitIdsCsv);
                if (this.trackId) body.append('track_id', String(this.trackId));

                const res = await fetch(this.completeUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    credentials: 'same-origin',
                    body: body.toString(),
                });

                if (res.ok) {
                    const data = await res.json().catch(() => ({}));
                    this.isCompleted = true;
                    if (data.next_unit_url) {
                        this.nextUrlFromCompletion = data.next_unit_url;
                    }
                    if (!silent) {
                        this.showToast(
                            data.newly_completed
                                ? `Unit completed! +${data.xp_gained || 100} XP`
                                : 'Unit completed!'
                        );
                    }
                }
            } catch (_) {
                // Keep manual button available on network failure
            } finally {
                this.isCompleting = false;
            }
        },

        async advanceToNextUnit() {
            if (this.isTransitioningUnit) return;
            this.isTransitioningUnit = true;
            this.stopAllMedia();

            if (this.isAuthenticated && !this.isCompleted && this.completeUrl) {
                await this.markComplete({ silent: true });
            }

            const target = this.resolvedNextUrl;
            if (target) {
                window.location.href = target;
            } else {
                this.isTransitioningUnit = false;
            }
        },
    };
}
</script>
