@php
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
    $leadVideoUrl = $leadVideo['video_url'] ?? null;
    $leadSentence = $leadVideo['target_sentence'] ?? '';
    $trackId = isset($currentTrack) && $currentTrack ? $currentTrack->id : request()->query('track_id');
    $completeUrl = isset($unit) && auth()->check()
        ? route('student.units.complete', array_filter(['unit' => $unit->id, 'track_id' => $trackId]))
        : null;
    $groupUnitIdsCsv = implode(',', $groupData['unit_ids'] ?? []);
    $chatStorageKey = 'amter_chat_v1_' . ($groupUnitIdsCsv !== '' ? $groupUnitIdsCsv : ($unit->id ?? '0'));
    $nextGroupUrl = !empty($groupData['next_group_unit'])
        ? (auth()->check()
            ? route('student.units.show', array_filter(['unit' => $groupData['next_group_unit']->id, 'track_id' => $trackId]))
            : (isset($course)
                ? route('public.unit.show', ['course' => $course->id, 'unit' => $groupData['next_group_unit']->id])
                : route('student.units.show', array_filter(['unit' => $groupData['next_group_unit']->id, 'track_id' => $trackId]))))
        : null;
@endphp

<style>
    .amter-chat-root {
        width: 100%;
        max-width: 680px;
        margin: 0 auto;
        padding: 0 8px 110px 8px;
        box-sizing: border-box;
        font-family: 'Outfit', system-ui, -apple-system, sans-serif;
    }

    /* Sticky Collapsible Video Header */
    .amter-video-card {
        position: sticky;
        top: 10px;
        z-index: 35;
        margin-bottom: 18px;
        border-radius: 22px;
        overflow: hidden;
        background: rgba(15, 23, 42, 0.94);
        border: 1px solid rgba(56, 189, 248, 0.35);
        box-shadow: 0 18px 40px -12px rgba(15, 23, 42, 0.45);
        backdrop-filter: blur(12px);
        transition: all 0.22s ease;
    }
    .amter-video-card.is-docked {
        height: 90px;
    }
    .amter-video-docked-row {
        height: 90px;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 8px 12px;
        box-sizing: border-box;
    }
    .amter-video-docked-thumb {
        width: 118px;
        height: 72px;
        border-radius: 14px;
        overflow: hidden;
        background: #000;
        flex-shrink: 0;
    }
    .amter-video-docked-thumb video {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .amter-video-topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 10px 14px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }
    .amter-pill-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        background: rgba(56, 189, 248, 0.16);
        color: #7dd3fc;
        border: 1px solid rgba(56, 189, 248, 0.35);
    }
    .amter-mini-btn {
        border: none;
        cursor: pointer;
        border-radius: 10px;
        padding: 5px 10px;
        font-size: 11px;
        font-weight: 700;
        background: rgba(255, 255, 255, 0.1);
        color: #e2e8f0;
        transition: background 0.15s ease, transform 0.1s ease;
    }
    .amter-mini-btn:hover { background: rgba(255, 255, 255, 0.18); }
    .amter-mini-btn.is-active {
        background: #f59e0b;
        color: #0f172a;
    }

    /* Progress Bar Strip */
    .amter-progress-strip {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 14px;
        padding: 8px 14px;
        border-radius: 16px;
        background: rgba(255, 255, 255, 0.72);
        border: 1px solid rgba(148, 163, 184, 0.25);
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
    }
    .amter-progress-track {
        flex: 1;
        height: 7px;
        border-radius: 999px;
        background: #e2e8f0;
        overflow: hidden;
    }
    .amter-progress-fill {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #6366f1, #06b6d4);
        transition: width 0.3s ease;
    }

    /* Chat Stream */
    .amter-chat-stream {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .amter-msg-row {
        display: flex;
        align-items: flex-end;
        gap: 8px;
        width: 100%;
    }
    .amter-msg-row.is-tutor {
        justify-content: flex-start;
    }
    .amter-msg-row.is-student {
        justify-content: flex-end;
    }
    .amter-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        background: #ffffff;
        border: 2px solid #38bdf8;
        box-shadow: 0 6px 14px rgba(56, 189, 248, 0.25);
    }
    .amter-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .amter-speak-row {
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px dashed rgba(148, 163, 184, 0.35);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .amter-speak-invite {
        padding: 9px 18px;
        font-size: 13px;
        box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.5);
        animation: amterSpeakInvite 2s infinite;
    }
    .amter-speak-listening {
        padding: 9px 18px;
        font-size: 13px;
        background: linear-gradient(135deg, #f43f5e, #e11d48);
        animation: amterPulse 1.1s infinite;
    }
    @keyframes amterSpeakInvite {
        0% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.55);
        }
        70% {
            transform: scale(1.03);
            box-shadow: 0 0 0 10px rgba(99, 102, 241, 0);
        }
        100% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(99, 102, 241, 0);
        }
    }
    .amter-bubble {
        max-width: min(86%, 520px);
        padding: 14px 16px;
        border-radius: 22px;
        box-shadow: 0 10px 25px -10px rgba(15, 23, 42, 0.14);
        box-sizing: border-box;
    }
    .amter-bubble.is-tutor {
        background: #ffffff;
        color: #0f172a;
        border: 1px solid rgba(148, 163, 184, 0.25);
        border-bottom-left-radius: 6px;
    }
    .amter-bubble.is-prompt {
        background: #f8fafc;
        color: #0f172a;
        border: 1.5px solid rgba(99, 102, 241, 0.28);
        border-bottom-left-radius: 6px;
    }
    .amter-bubble.is-student {
        background: linear-gradient(135deg, #4f46e5, #6366f1);
        color: #ffffff;
        border-bottom-right-radius: 6px;
    }
    .amter-bubble-label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 6px;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #6366f1;
    }
    .amter-bubble.is-student .amter-bubble-label {
        color: #c7d2fe;
    }

    /* Cute Image Card */
    .amter-img-frame {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
    }
    .amter-img-frame img {
        display: block;
        width: auto;
        max-width: 100%;
        border-radius: 16px;
        object-fit: contain;
        border: 1px solid rgba(148, 163, 184, 0.22);
        box-shadow: 0 8px 18px -8px rgba(15, 23, 42, 0.18);
    }
    .amter-img-caption {
        font-size: 12px;
        font-weight: 600;
        color: #475569;
        margin: 0;
    }

    /* Interactive Action Pills */
    .amter-btn-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        margin-top: 6px;
    }
    .amter-pill-action {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 13px;
        border-radius: 999px;
        border: none;
        cursor: pointer;
        font-size: 12px;
        font-weight: 800;
        transition: transform 0.1s ease, filter 0.15s ease;
    }
    .amter-pill-action:active { transform: scale(0.96); }
    .amter-pill-cyan {
        background: linear-gradient(135deg, #06b6d4, #0284c7);
        color: #ffffff;
    }
    .amter-pill-emerald {
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
    }
    .amter-pill-slate {
        background: #f1f5f9;
        color: #334155;
        border: 1px solid #cbd5e1;
    }
    .amter-pill-indigo {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: #ffffff;
    }

    /* Cute Quiz Option Cards */
    .amter-quiz-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-top: 8px;
    }
    .amter-quiz-opt {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        text-align: left;
        padding: 10px 14px;
        border-radius: 14px;
        border: 1.5px solid #e2e8f0;
        background: #ffffff;
        color: #1e293b;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .amter-quiz-opt:hover {
        border-color: #818cf8;
        background: #eef2ff;
    }
    .amter-quiz-opt.is-correct {
        border-color: #10b981;
        background: #ecfdf5;
        color: #065f46;
    }
    .amter-quiz-opt.is-wrong {
        border-color: #f43f5e;
        background: #fff1f2;
        color: #9f1239;
    }
    .amter-quiz-badge {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 900;
        background: #e2e8f0;
        color: #334155;
        flex-shrink: 0;
    }

    /* Word Diff Chips */
    .amter-diff-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 8px;
    }
    .amter-diff-chip {
        padding: 4px 9px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 800;
        border: 1px solid transparent;
    }
    .amter-diff-green { background: rgba(16, 185, 129, 0.25); border-color: #34d399; color: #ecfdf5; }
    .amter-diff-amber { background: rgba(245, 158, 11, 0.25); border-color: #fbbf24; color: #fef3c7; }
    .amter-diff-red {
        background: rgba(244, 63, 94, 0.3);
        border-color: #fb7185;
        color: #ffe4e6;
        text-decoration: underline dotted;
        cursor: pointer;
    }

    /* Waveform */
    .amter-wave-box {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 12px;
        border-radius: 14px;
        background: rgba(15, 23, 42, 0.28);
        margin-top: 6px;
    }
    .amter-wave-bars {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 3px;
        height: 22px;
    }
    .amter-wave-bar {
        flex: 1;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.78);
    }

    /* Mastery Comparison Card */
    .amter-mastery-card {
        margin-top: 20px;
        padding: 18px;
        border-radius: 22px;
        background: linear-gradient(135deg, #ecfdf5, #eef2ff);
        border: 1.5px solid #6ee7b7;
        box-shadow: 0 14px 30px -12px rgba(16, 185, 129, 0.25);
        color: #0f172a;
    }
    .amter-mastery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 10px;
        margin-top: 12px;
    }
    .amter-mastery-col {
        background: #ffffff;
        padding: 12px;
        border-radius: 14px;
        border: 1px solid #cbd5e1;
    }

    /* Fixed Bottom Action Dock */
    .amter-bottom-dock {
        position: fixed;
        bottom: 14px;
        left: 50%;
        transform: translateX(-50%);
        width: calc(100% - 24px);
        max-width: 640px;
        z-index: 60;
        background: rgba(15, 23, 42, 0.94);
        border: 1px solid rgba(255, 255, 255, 0.14);
        border-radius: 24px;
        padding: 8px 10px;
        box-shadow: 0 20px 45px -10px rgba(15, 23, 42, 0.65);
        backdrop-filter: blur(14px);
        display: flex;
        align-items: center;
        gap: 8px;
        box-sizing: border-box;
    }
    .amter-dock-side {
        border: none;
        cursor: pointer;
        border-radius: 16px;
        padding: 11px 13px;
        font-size: 12px;
        font-weight: 800;
        background: rgba(255, 255, 255, 0.1);
        color: #e2e8f0;
        flex-shrink: 0;
    }
    .amter-dock-side:hover { background: rgba(255, 255, 255, 0.18); }
    .amter-dock-mic {
        flex: 1;
        border: none;
        cursor: pointer;
        border-radius: 18px;
        padding: 12px 16px;
        font-size: 14px;
        font-weight: 900;
        color: #ffffff;
        background: linear-gradient(135deg, #6366f1, #06b6d4);
        box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
        transition: transform 0.1s ease;
    }
    .amter-dock-mic.is-listening {
        background: linear-gradient(135deg, #f43f5e, #e11d48);
        animation: amterPulse 1.2s infinite;
    }
    @keyframes amterPulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.02); }
    }
</style>

<div
    x-data="amterGuidedChat({
        turns: @js($chatTurns),
        ttsEndpoint: @js(url('/tts-stream')),
        leadVideoUrl: @js($leadVideoUrl),
        leadSentence: @js($leadSentence),
        nextGroupUrl: @js($nextGroupUrl),
        storageKey: @js($chatStorageKey)
    })"
    @scroll.window="handleWindowScroll()"
    class="amter-chat-root"
>
    {{-- Sticky Collapsible Video Header --}}
    <template x-if="leadVideoUrl">
        <div class="amter-video-card" :class="isDocked ? 'is-docked' : ''">
            {{-- Docked 90px Compact Bar --}}
            <div x-show="isDocked" class="amter-video-docked-row">
                <div class="amter-video-docked-thumb">
                    <video
                        x-ref="dockedVideo"
                        :src="leadVideoUrl"
                        :muted="miniMuted"
                        playsinline
                        @play="videoPlaying = true"
                        @pause="videoPlaying = false"
                        @loadedmetadata="captureNativeDuration($event)"
                        @timeupdate="handleLoopTimeUpdate($event)"
                    ></video>
                </div>
                <div style="flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 6px;">
                    <p style="margin: 0; font-size: 13px; font-weight: 700; color: #fff; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"
                       x-text="leadSentence || 'Native Speaker Clip'"></p>
                    <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                        <button type="button" @click="toggleVideoPlay()" class="amter-mini-btn" :class="videoPlaying ? 'is-active' : ''" x-text="videoPlaying ? 'Pause' : 'Play'"></button>
                        <button type="button" @click="toggleSlowSpeed()" class="amter-mini-btn" :class="slowSpeed ? 'is-active' : ''">0.75x</button>
                        <button type="button" @click="loopVideo = !loopVideo" class="amter-mini-btn" :class="loopVideo ? 'is-active' : ''">Loop</button>
                        <button type="button" @click="isDocked = false" class="amter-mini-btn" style="color: #7dd3fc;">Expand</button>
                    </div>
                </div>
            </div>

            {{-- Expanded Prominent Header --}}
            <div x-show="!isDocked">
                <div class="amter-video-topbar">
                    <span class="amter-pill-badge">Native Clip &bull; 5s</span>
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <button type="button" @click="toggleVideoPlay()" class="amter-mini-btn" :class="videoPlaying ? 'is-active' : ''" x-text="videoPlaying ? 'Pause' : 'Play'"></button>
                        <button type="button" @click="toggleSlowSpeed()" class="amter-mini-btn" :class="slowSpeed ? 'is-active' : ''">0.75x</button>
                        <button type="button" @click="loopVideo = !loopVideo" class="amter-mini-btn" :class="loopVideo ? 'is-active' : ''">Loop</button>
                    </div>
                </div>
                <div style="background: #000; max-height: 240px; display: flex; justify-content: center;">
                    <video
                        x-ref="heroVideo"
                        :src="leadVideoUrl"
                        :muted="miniMuted"
                        playsinline
                        controls
                        @play="videoPlaying = true"
                        @pause="videoPlaying = false"
                        @loadedmetadata="captureNativeDuration($event)"
                        @timeupdate="handleLoopTimeUpdate($event)"
                        style="width: 100%; max-height: 240px; object-fit: contain;"
                    ></video>
                </div>
                <div x-show="leadSentence" style="padding: 10px 14px; text-align: center; color: #fff; font-size: 14px; font-weight: 700;" x-text="leadSentence"></div>
            </div>
        </div>
    </template>

    {{-- Guided Progress Strip --}}
    <div class="amter-progress-strip">
        <span style="font-size: 11px; font-weight: 800; color: #475569;" x-text="`Step ${Math.min(visibleCount, turns.length)} of ${turns.length}`"></span>
        <div class="amter-progress-track">
            <div class="amter-progress-fill" :style="`width: ${turns.length ? Math.round((Math.min(visibleCount, turns.length) / turns.length) * 100) : 100}%;`"></div>
        </div>
        <div style="display: flex; align-items: center; gap: 6px;">
            <button
                type="button"
                x-show="hasProgress"
                @click="resetProgress()"
                class="amter-pill-action amter-pill-slate"
                style="padding: 4px 10px; font-size: 11px;"
            >Restart</button>
            <button
                type="button"
                @click="toggleShowAll()"
                class="amter-pill-action amter-pill-slate"
                style="padding: 4px 10px; font-size: 11px;"
                x-text="showAll ? 'Guided' : 'Show All'"
            ></button>
        </div>
    </div>

    {{-- Chat Thread --}}
    <div class="amter-chat-stream">
        <template x-for="(turn, idx) in visibleTurns" :key="turn.id">
            <div style="display: flex; flex-direction: column; gap: 10px;">
                {{-- Left Bubble: Amter Tutor --}}
                <template x-if="turn.role === 'tutor'">
                    <div class="amter-msg-row is-tutor">
                        <div class="amter-avatar">
                            <img src="{{ asset('images/amter-tutor-avatar.jpg') }}" alt="Amter Tutor">
                        </div>
                        <div class="amter-bubble is-tutor">
                            <div class="amter-bubble-label">
                                <span x-text="turn.title || 'Amter Tutor'"></span>
                            </div>

                            {{-- Inline Attached Illustration (anchored directly inside this bubble) --}}
                            <template x-if="turn.data.attached_image_url">
                                <div class="amter-img-frame" style="margin-bottom: 10px;">
                                    <img
                                        :src="turn.data.attached_image_url"
                                        alt=""
                                        style="max-height: 145px;"
                                    />
                                </div>
                            </template>

                            {{-- Tutor Text --}}
                            <template x-if="turn.kind === 'text'">
                                <div style="font-size: 14px; line-height: 1.55; color: #1e293b;" x-html="turn.data.html"></div>
                            </template>

                            {{-- Tutor TTS / Merged Listen & Speak Drill --}}
                            <template x-if="turn.kind === 'tts' || turn.kind === 'tts_drill'">
                                <div>
                                    <p
                                        x-show="!isEnglishHidden(turn)"
                                        style="margin: 0 0 8px 0; font-size: 17px; font-weight: 800; color: #0f172a; line-height: 1.35;"
                                        x-text="turn.data.text"
                                    ></p>
                                    <p
                                        x-show="turn.data.meaning_malayalam && isRevealed(turn.id, 'ml')"
                                        style="margin: 0 0 8px 0; padding: 8px 10px; border-radius: 10px; background: #ecfdf5; color: #065f46; font-size: 13px; font-weight: 700;"
                                        x-text="turn.data.meaning_malayalam"
                                    ></p>
                                    <div class="amter-btn-row">
                                        <button type="button" @click="listenToTurn(turn)" class="amter-pill-action amter-pill-cyan">
                                            &#9654; Listen
                                        </button>
                                        <button type="button" x-show="turn.data.meaning_malayalam" @click="toggleReveal(turn.id, 'ml')" class="amter-pill-action amter-pill-emerald">
                                            മലയാളം
                                        </button>
                                        <button type="button" @click="toggleReveal(turn.id, 'en')" class="amter-pill-action amter-pill-slate" x-text="isEnglishHidden(turn) ? 'Show English' : 'Hide English'"></button>
                                    </div>
                                    <div x-show="turn.kind === 'tts_drill'" class="amter-speak-row">
                                        <button
                                            type="button"
                                            @click="toggleVoicePractice(turn)"
                                            class="amter-pill-action amter-pill-indigo"
                                            :class="activeTurnId === turn.id && isListening ? 'amter-speak-listening' : 'amter-speak-invite'"
                                        >
                                            <span>&#127908;</span>
                                            <span x-text="activeTurnId === turn.id && isListening ? 'Listening... Tap to Stop' : 'Tap to Speak'"></span>
                                        </button>
                                        <span
                                            x-show="turnStates[turn.id]?.listened && !(activeTurnId === turn.id && isListening)"
                                            style="font-size: 11px; font-weight: 700; color: #059669;"
                                        >
                                            Your turn!
                                        </span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- Student Response Bubble for merged tts_drill --}}
                <template x-if="turn.kind === 'tts_drill' && turnStates[turn.id]">
                    <div class="amter-msg-row is-student">
                        <div class="amter-bubble is-student">
                            <div class="amter-bubble-label">
                                <span>Your Attempt</span>
                                <span x-show="turnStates[turn.id].rhythmLabel" style="background: rgba(255,255,255,0.18); padding: 2px 8px; border-radius: 999px; font-size: 10px;" x-text="turnStates[turn.id].rhythmLabel"></span>
                            </div>
                            <div x-show="turnStates[turn.id].mirrorUrl" class="amter-wave-box">
                                <button type="button" @click="playMirror(turn.id)" style="width: 30px; height: 30px; border-radius: 50%; border: none; background: #10b981; color: #0f172a; font-weight: 900; cursor: pointer;">&#9654;</button>
                                <div class="amter-wave-bars">
                                    <template x-for="bar in [35,65,90,50,80,45,95,60,75,40,85,55,70,45]" :key="bar">
                                        <span class="amter-wave-bar" :style="`height: ${bar}%;`"></span>
                                    </template>
                                </div>
                                <button type="button" @click="playAcousticMirrorSequence(turn)" style="border: none; background: transparent; color: #a7f3d0; font-size: 11px; font-weight: 800; cursor: pointer;">Mirror</button>
                            </div>
                            <div x-show="turnStates[turn.id].diff?.length" class="amter-diff-wrap">
                                <template x-for="(chip, cIdx) in turnStates[turn.id].diff" :key="cIdx">
                                    <button
                                        type="button"
                                        @click="chip.status === 'red' ? playTts(chip.word, 0.75) : null"
                                        class="amter-diff-chip"
                                        :class="{
                                            'amter-diff-green': chip.status === 'green',
                                            'amter-diff-amber': chip.status === 'amber',
                                            'amter-diff-red': chip.status === 'red'
                                        }"
                                        x-text="chip.word"
                                    ></button>
                                </template>
                            </div>
                            <p x-show="turnStates[turn.id].spoken" style="margin: 6px 0 0 0; font-size: 11px; opacity: 0.9;">
                                Heard: "<span x-text="turnStates[turn.id].spoken"></span>"
                            </p>
                        </div>
                    </div>
                </template>

                {{-- Tutor Congratulatory / Encouragement Chat Bubble --}}
                <template x-if="turn.kind === 'tts_drill' && turnStates[turn.id]?.tutorReply">
                    <div class="amter-msg-row is-tutor">
                        <div class="amter-avatar">
                            <img src="{{ asset('images/amter-tutor-avatar.jpg') }}" alt="Amter Tutor">
                        </div>
                        <div
                            class="amter-bubble is-tutor"
                            :style="turnStates[turn.id].tutorReplyPassed ? 'background: #ecfdf5; border-color: #6ee7b7;' : 'background: #fffbeb; border-color: #fcd34d;'"
                        >
                            <p style="margin: 0; font-size: 13px; font-weight: 800;" :style="turnStates[turn.id].tutorReplyPassed ? 'color: #065f46;' : 'color: #92400e;'" x-text="turnStates[turn.id].tutorReply"></p>
                        </div>
                    </div>
                </template>

                {{-- Student Prompt + Right-Aligned Student Response --}}
                <template x-if="turn.role === 'student'">
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <div class="amter-msg-row is-tutor" x-show="turn.kind === 'voice_match' || turn.kind === 'dictation' || turn.kind === 'quiz'">
                            <div class="amter-avatar">
                                <img src="{{ asset('images/amter-tutor-avatar.jpg') }}" alt="Amter Tutor">
                            </div>
                            <div class="amter-bubble is-prompt">
                                <div class="amter-bubble-label">
                                    <span x-text="turn.title || 'Your Turn'"></span>
                                    <button
                                        type="button"
                                        x-show="turn.kind === 'voice_match'"
                                        @click="toggleReveal(turn.id, 'en')"
                                        style="border: none; background: transparent; color: #6366f1; font-size: 10px; font-weight: 800; cursor: pointer; text-decoration: underline;"
                                        x-text="isEnglishHidden(turn) ? 'Show English' : 'Hide English'"
                                    ></button>
                                </div>

                                {{-- Voice Match Prompt --}}
                                <template x-if="turn.kind === 'voice_match'">
                                    <div>
                                        <p x-show="turn.data.instructions" style="margin: 0 0 6px 0; font-size: 12px; color: #475569;" x-text="turn.data.instructions"></p>
                                        <p
                                            x-show="!isEnglishHidden(turn)"
                                            style="margin: 0 0 8px 0; font-size: 15px; font-weight: 800; color: #0f172a;"
                                            x-text="(turn.data.phrases || []).join(' / ')"
                                        ></p>
                                        <div class="amter-btn-row">
                                            <button type="button" @click="playTts((turn.data.phrases || [''])[0], slowSpeed ? 0.75 : 1.0)" class="amter-pill-action amter-pill-slate">
                                                &#9654; Tutor Audio
                                            </button>
                                            <button type="button" @click="toggleVoicePractice(turn)" class="amter-pill-action amter-pill-indigo" x-text="activeTurnId === turn.id && isListening ? 'Stop Mic' : (turn.data.button_label || 'Speak')"></button>
                                        </div>
                                    </div>
                                </template>

                                {{-- Dictation Prompt --}}
                                <template x-if="turn.kind === 'dictation'">
                                    <div>
                                        <p style="margin: 0 0 8px 0; font-size: 12px; color: #475569;" x-text="turn.data.instructions"></p>
                                        <button type="button" @click="playTts(turn.data.text, slowSpeed ? 0.75 : 1.0)" class="amter-pill-action amter-pill-cyan" style="margin-bottom: 8px;">
                                            &#9654; Play Dictation
                                        </button>
                                        <div style="display: flex; gap: 6px;">
                                            <input
                                                type="text"
                                                x-model="dictationInputs[turn.id]"
                                                placeholder="Type what you hear..."
                                                style="flex: 1; border-radius: 12px; border: 1.5px solid #cbd5e1; padding: 8px 12px; font-size: 13px; color: #0f172a;"
                                            />
                                            <button type="button" @click="checkDictation(turn)" class="amter-pill-action amter-pill-indigo">Check</button>
                                        </div>
                                    </div>
                                </template>

                                {{-- Interactive Quiz Prompt --}}
                                <template x-if="turn.kind === 'quiz'">
                                    <div>
                                        <p style="margin: 0; font-size: 15px; font-weight: 800; color: #0f172a;" x-text="turn.data.question"></p>
                                        <div class="amter-quiz-list">
                                            <template x-for="(opt, oIdx) in (turn.data.options || [])" :key="oIdx">
                                                <button
                                                    type="button"
                                                    @click="selectQuizOption(turn, oIdx)"
                                                    class="amter-quiz-opt"
                                                    :class="quizSelections[turn.id] === oIdx
                                                        ? (isQuizOptionCorrect(turn, opt, oIdx) ? 'is-correct' : 'is-wrong')
                                                        : ''"
                                                >
                                                    <span class="amter-quiz-badge" x-text="String.fromCharCode(65 + oIdx)"></span>
                                                    <span style="flex: 1;" x-text="typeof opt === 'object' ? (opt.text || opt.option_text || '') : opt"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Right Bubble: Student Voice Response --}}
                        <template x-if="turnStates[turn.id]">
                            <div class="amter-msg-row is-student">
                                <div class="amter-bubble is-student">
                                    <div class="amter-bubble-label">
                                        <span>Your Attempt</span>
                                        <span x-show="turnStates[turn.id].rhythmLabel" style="background: rgba(255,255,255,0.18); padding: 2px 8px; border-radius: 999px; font-size: 10px;" x-text="turnStates[turn.id].rhythmLabel"></span>
                                    </div>

                                    <div x-show="turnStates[turn.id].mirrorUrl" class="amter-wave-box">
                                        <button type="button" @click="playMirror(turn.id)" style="width: 30px; height: 30px; border-radius: 50%; border: none; background: #10b981; color: #0f172a; font-weight: 900; cursor: pointer;">&#9654;</button>
                                        <div class="amter-wave-bars">
                                            <template x-for="bar in [35,65,90,50,80,45,95,60,75,40,85,55,70,45]" :key="bar">
                                                <span class="amter-wave-bar" :style="`height: ${bar}%;`"></span>
                                            </template>
                                        </div>
                                        <button type="button" @click="playAcousticMirrorSequence(turn)" style="border: none; background: transparent; color: #a7f3d0; font-size: 11px; font-weight: 800; cursor: pointer;">Mirror</button>
                                    </div>

                                    <div x-show="turnStates[turn.id].diff?.length" class="amter-diff-wrap">
                                        <template x-for="(chip, cIdx) in turnStates[turn.id].diff" :key="cIdx">
                                            <button
                                                type="button"
                                                @click="chip.status === 'red' ? playTts(chip.word, 0.75) : null"
                                                class="amter-diff-chip"
                                                :class="{
                                                    'amter-diff-green': chip.status === 'green',
                                                    'amter-diff-amber': chip.status === 'amber',
                                                    'amter-diff-red': chip.status === 'red'
                                                }"
                                                x-text="chip.word"
                                            ></button>
                                        </template>
                                    </div>

                                    <p x-show="turnStates[turn.id].spoken" style="margin: 6px 0 0 0; font-size: 11px; opacity: 0.9;">
                                        Heard: "<span x-text="turnStates[turn.id].spoken"></span>"
                                    </p>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </template>
    </div>

    {{-- "Before vs. After" Mastery Comparison Card --}}
    <template x-if="isSessionComplete && (beforeAttempt || afterAttempt)">
        <div class="amter-mastery-card">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.1em; color: #047857;">Mastery Unlocked</span>
                <span style="font-size: 12px; font-weight: 800; color: #0f172a;">Before vs. After</span>
            </div>
            <p style="margin: 6px 0 0 0; font-size: 15px; font-weight: 800;" x-text="leadSentence"></p>
            <div class="amter-mastery-grid">
                <div class="amter-mastery-col">
                    <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 6px;">
                        <span style="font-weight: 700; color: #475569;">Attempt #1 (Before)</span>
                        <span style="font-weight: 900; color: #d97706;" x-text="`${beforeAttempt?.score ?? 0}%`"></span>
                    </div>
                    <button type="button" x-show="beforeAttempt?.mirrorUrl" @click="playAudioUrl(beforeAttempt.mirrorUrl)" class="amter-pill-action amter-pill-slate" style="width: 100%; justify-content: center;">
                        &#9654; Play Attempt #1
                    </button>
                </div>
                <div class="amter-mastery-col" style="border-color: #34d399;">
                    <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 6px;">
                        <span style="font-weight: 700; color: #047857;">Mastered (After)</span>
                        <span style="font-weight: 900; color: #059669;" x-text="`${afterAttempt?.score ?? 0}%`"></span>
                    </div>
                    <button type="button" x-show="afterAttempt?.mirrorUrl" @click="playAudioUrl(afterAttempt.mirrorUrl)" class="amter-pill-action amter-pill-emerald" style="width: 100%; justify-content: center;">
                        &#9654; Play Mastered Voice
                    </button>
                </div>
            </div>

            @if($completeUrl)
                <form method="POST" action="{{ $completeUrl }}" style="margin-top: 12px;">
                    @csrf
                    <input type="hidden" name="group_unit_ids" value="{{ $groupUnitIdsCsv }}">
                    @if($trackId)
                        <input type="hidden" name="track_id" value="{{ $trackId }}">
                    @endif
                    <button type="submit" class="amter-pill-action amter-pill-cyan" style="width: 100%; justify-content: center; padding: 11px;">
                        {{ $nextGroupUrl ? 'Complete Lesson & Continue' : 'Complete Lesson' }} &rarr;
                    </button>
                </form>
            @elseif($nextGroupUrl)
                <a href="{{ $nextGroupUrl }}" class="amter-pill-action amter-pill-cyan" style="width: 100%; justify-content: center; padding: 11px; margin-top: 12px; text-decoration: none; box-sizing: border-box;">
                    Next Lesson &rarr;
                </a>
            @endif
        </div>
    </template>

    {{-- Inline Continue Button (replaces permanent fixed bottom dock) --}}
    <div x-show="!showAll && visibleCount < turns.length" style="display: flex; justify-content: center; margin-top: 18px;">
        <button
            type="button"
            @click="advanceStep()"
            class="amter-pill-action amter-pill-indigo"
            style="padding: 11px 24px; font-size: 14px; box-shadow: 0 10px 24px -6px rgba(99, 102, 241, 0.45);"
        >
            Continue &rarr;
        </button>
    </div>

    {{-- Next Video Lesson CTA when all turns in this clip are done --}}
    @if($completeUrl || $nextGroupUrl)
        <div x-show="isSessionComplete && !(beforeAttempt || afterAttempt)" style="display: flex; justify-content: center; margin-top: 18px;">
            @if($completeUrl)
                <form method="POST" action="{{ $completeUrl }}">
                    @csrf
                    <input type="hidden" name="group_unit_ids" value="{{ $groupUnitIdsCsv }}">
                    @if($trackId)
                        <input type="hidden" name="track_id" value="{{ $trackId }}">
                    @endif
                    <button type="submit" class="amter-pill-action amter-pill-cyan" style="padding: 12px 24px; font-size: 14px;">
                        {{ $nextGroupUrl ? 'Next Video Lesson' : 'Complete Lesson' }} &rarr;
                    </button>
                </form>
            @else
                <a href="{{ $nextGroupUrl }}" class="amter-pill-action amter-pill-cyan" style="padding: 12px 24px; font-size: 14px; text-decoration: none;">
                    Next Video Lesson &rarr;
                </a>
            @endif
        </div>
    @endif
</div>

<script>
function amterGuidedChat(config) {
    return {
        turns: config.turns || [],
        ttsEndpoint: config.ttsEndpoint || '/tts-stream',
        leadVideoUrl: config.leadVideoUrl || null,
        leadSentence: config.leadSentence || '',
        storageKey: config.storageKey || null,
        visibleCount: Math.min(2, (config.turns || []).length),
        showAll: false,
        isDocked: false,
        slowSpeed: false,
        miniMuted: false,
        videoPlaying: false,
        loopVideo: false,
        nativeDurationMs: 4500,
        activeTurnId: null,
        isListening: false,
        holdTimer: null,
        speechStartedAt: 0,
        recognition: null,
        mediaRecorder: null,
        recordedChunks: [],
        turnStates: {},
        reveals: {},
        dictationInputs: {},
        quizSelections: {},
        beforeAttempt: null,
        afterAttempt: null,
        ttsAudio: null,

        get visibleTurns() {
            return this.showAll ? this.turns : this.turns.slice(0, this.visibleCount);
        },

        get isSessionComplete() {
            return this.turns.length === 0 || this.visibleCount >= this.turns.length;
        },

        get hasProgress() {
            const initial = Math.min(2, this.turns.length);
            return this.visibleCount > initial
                || this.showAll
                || Object.keys(this.turnStates).length > 0
                || Object.keys(this.quizSelections).length > 0;
        },

        init() {
            this.loadProgress();
        },

        loadProgress() {
            if (!this.storageKey) return;
            try {
                const raw = window.localStorage.getItem(this.storageKey);
                if (!raw) return;
                const saved = JSON.parse(raw);
                if (!saved || typeof saved !== 'object') return;

                const minVisible = Math.min(2, this.turns.length);
                if (typeof saved.visibleCount === 'number' && this.turns.length > 0) {
                    this.visibleCount = Math.max(minVisible, Math.min(saved.visibleCount, this.turns.length));
                }
                if (typeof saved.showAll === 'boolean') {
                    this.showAll = saved.showAll;
                    if (this.showAll) {
                        this.visibleCount = this.turns.length;
                    }
                }
                if (saved.reveals && typeof saved.reveals === 'object') {
                    this.reveals = saved.reveals;
                }
                if (saved.dictationInputs && typeof saved.dictationInputs === 'object') {
                    this.dictationInputs = saved.dictationInputs;
                }
                if (saved.quizSelections && typeof saved.quizSelections === 'object') {
                    this.quizSelections = saved.quizSelections;
                }
                if (saved.turnStates && typeof saved.turnStates === 'object') {
                    this.turnStates = saved.turnStates;
                }
                if (saved.beforeAttempt && typeof saved.beforeAttempt === 'object') {
                    this.beforeAttempt = { score: saved.beforeAttempt.score ?? 0 };
                }
                if (saved.afterAttempt && typeof saved.afterAttempt === 'object') {
                    this.afterAttempt = { score: saved.afterAttempt.score ?? 0 };
                }
                if (this.visibleCount > 2 && this.leadVideoUrl) {
                    this.isDocked = true;
                }
            } catch (_) {}
        },

        saveProgress() {
            if (!this.storageKey) return;
            try {
                const serializableTurnStates = {};
                for (const [id, st] of Object.entries(this.turnStates || {})) {
                    if (!st || typeof st !== 'object') continue;
                    const { mirrorUrl, ...rest } = st;
                    serializableTurnStates[id] = rest;
                }
                const payload = {
                    visibleCount: this.visibleCount,
                    showAll: this.showAll,
                    reveals: this.reveals,
                    dictationInputs: this.dictationInputs,
                    quizSelections: this.quizSelections,
                    turnStates: serializableTurnStates,
                    beforeAttempt: this.beforeAttempt ? { score: this.beforeAttempt.score ?? 0 } : null,
                    afterAttempt: this.afterAttempt ? { score: this.afterAttempt.score ?? 0 } : null,
                };
                window.localStorage.setItem(this.storageKey, JSON.stringify(payload));
            } catch (_) {}
        },

        resetProgress() {
            this.visibleCount = Math.min(2, this.turns.length);
            this.showAll = false;
            this.isDocked = false;
            this.turnStates = {};
            this.reveals = {};
            this.dictationInputs = {};
            this.quizSelections = {};
            this.beforeAttempt = null;
            this.afterAttempt = null;
            if (this.storageKey) {
                try { window.localStorage.removeItem(this.storageKey); } catch (_) {}
            }
        },

        toggleShowAll() {
            this.showAll = !this.showAll;
            if (this.showAll) {
                this.visibleCount = this.turns.length;
            }
            this.saveProgress();
        },

        get activeSpeakingTurn() {
            const visible = this.visibleTurns;
            if (!visible.length) return null;
            const latest = visible[visible.length - 1];
            if (latest.kind === 'tts_drill' || latest.kind === 'voice_match') {
                return latest;
            }
            return null;
        },

        get primaryDockLabel() {
            if (this.isListening) return 'Listening... Tap to Stop';
            if (this.activeSpeakingTurn) return 'Tap to Speak';
            return 'Continue \u2192';
        },

        handlePrimaryDockAction() {
            const speakTurn = this.activeSpeakingTurn;
            if (speakTurn) {
                this.toggleVoicePractice(speakTurn);
            } else {
                this.advanceStep();
            }
        },

        pauseAllVideos() {
            if (this.$refs.heroVideo && !this.$refs.heroVideo.paused) {
                this.$refs.heroVideo.pause();
            }
            if (this.$refs.dockedVideo && !this.$refs.dockedVideo.paused) {
                this.$refs.dockedVideo.pause();
            }
            this.videoPlaying = false;
        },

        toggleVideoPlay() {
            const v = this.isDocked ? this.$refs.dockedVideo : this.$refs.heroVideo;
            if (!v) return;
            if (v.paused) {
                if (v.currentTime >= 5) v.currentTime = 0;
                v.muted = false;
                this.miniMuted = false;
                v.play().catch(() => {});
            } else {
                v.pause();
            }
        },

        handleWindowScroll() {
            if (window.scrollY > 180 && this.leadVideoUrl) {
                this.isDocked = true;
            }
        },

        captureNativeDuration(event) {
            const d = event.target?.duration;
            if (d && isFinite(d) && d > 0) {
                this.nativeDurationMs = Math.min(d, 5) * 1000;
            }
        },

        handleLoopTimeUpdate(event) {
            const video = event.target;
            if (video && video.currentTime >= 5) {
                if (this.loopVideo) {
                    video.currentTime = 0;
                    video.play().catch(() => {});
                } else {
                    video.pause();
                    video.currentTime = 0;
                    this.videoPlaying = false;
                }
            }
        },

        toggleSlowSpeed() {
            this.slowSpeed = !this.slowSpeed;
            const rate = this.slowSpeed ? 0.75 : 1.0;
            if (this.$refs.heroVideo) this.$refs.heroVideo.playbackRate = rate;
            if (this.$refs.dockedVideo) this.$refs.dockedVideo.playbackRate = rate;
        },

        toggleMiniMute() {
            this.miniMuted = !this.miniMuted;
            if (this.$refs.heroVideo) this.$refs.heroVideo.muted = this.miniMuted;
            if (this.$refs.dockedVideo) this.$refs.dockedVideo.muted = this.miniMuted;
        },

        advanceStep() {
            if (this.visibleCount < this.turns.length) {
                this.visibleCount += 1;
                if (this.visibleCount > 2 && this.leadVideoUrl) {
                    this.isDocked = true;
                }
                this.saveProgress();
            }
        },

        toggleReveal(turnId, lang) {
            const key = `${turnId}:${lang}`;
            this.reveals[key] = !this.reveals[key];
            this.saveProgress();
        },

        isRevealed(turnId, lang) {
            return !!this.reveals[`${turnId}:${lang}`];
        },

        isEnglishHidden(turn) {
            const key = `${turn.id}:en`;
            if (key in this.reveals) {
                return !this.reveals[key];
            }
            return !!turn.data?.hide_english;
        },

        playAudioUrl(url, rate = 1.0) {
            if (!url) return Promise.resolve();
            return new Promise((resolve) => {
                if (this.ttsAudio) {
                    this.ttsAudio.pause();
                }
                const audio = new Audio(url);
                audio.playbackRate = rate;
                audio.onended = () => resolve();
                audio.onerror = () => resolve();
                this.ttsAudio = audio;
                audio.play().catch(() => resolve());
            });
        },

        async listenToTurn(turn) {
            await this.playTts(turn.data.text, this.slowSpeed ? 0.75 : 1.0);
            this.turnStates[turn.id] = {
                ...(this.turnStates[turn.id] || {}),
                listened: true,
            };
            this.saveProgress();
        },

        playTts(text, rate = 1.0) {
            const clean = (text || '').trim();
            if (!clean) return Promise.resolve();
            this.pauseAllVideos();

            if (typeof window.speakAmterText === 'function') {
                return new Promise((resolve) => {
                    window.speakAmterText(clean, 'en-US', 'any', rate, {
                        onEnd: () => resolve(),
                    });
                });
            }

            const url = `${this.ttsEndpoint.replace(/\/$/, '')}?text=${encodeURIComponent(clean)}&tl=en-US`;
            return this.playAudioUrl(url, rate);
        },

        playMirror(turnId) {
            const url = this.turnStates[turnId]?.mirrorUrl;
            if (url) this.playAudioUrl(url, 1.0);
        },

        async playAcousticMirrorSequence(turn) {
            const mirrorUrl = this.turnStates[turn.id]?.mirrorUrl;
            const phrase = (turn.data?.phrases && turn.data.phrases[0]) || this.leadSentence;
            if (phrase) {
                await this.playTts(phrase, this.slowSpeed ? 0.75 : 1.0);
            }
            await new Promise((r) => setTimeout(r, 300));
            if (mirrorUrl) {
                await this.playAudioUrl(mirrorUrl, 1.0);
            }
        },

        replayActiveTutor() {
            const visible = this.visibleTurns;
            for (let i = visible.length - 1; i >= 0; i--) {
                const t = visible[i];
                if ((t.kind === 'tts' || t.kind === 'tts_drill') && t.data?.text) {
                    this.playTts(t.data.text, this.slowSpeed ? 0.75 : 1.0);
                    return;
                }
                if (t.kind === 'voice_match' && t.data?.phrases?.[0]) {
                    this.playTts(t.data.phrases[0], this.slowSpeed ? 0.75 : 1.0);
                    return;
                }
            }
            if (this.leadSentence) {
                this.playTts(this.leadSentence, this.slowSpeed ? 0.75 : 1.0);
            }
        },

        speakOnActiveTurn() {
            const visible = this.visibleTurns;
            for (let i = visible.length - 1; i >= 0; i--) {
                if (visible[i].kind === 'voice_match') {
                    this.toggleVoicePractice(visible[i]);
                    return;
                }
            }
            if (this.turns.length) {
                this.advanceStep();
            }
        },

        startHoldToSpeak() {
            this.holdTimer = setTimeout(() => {
                if (!this.isListening) {
                    this.speakOnActiveTurn();
                }
            }, 220);
        },

        stopHoldToSpeak() {
            if (this.holdTimer) {
                clearTimeout(this.holdTimer);
                this.holdTimer = null;
            }
            if (this.isListening) {
                this.stopVoicePractice();
            }
        },

        async toggleVoicePractice(turn) {
            if (this.isListening && this.activeTurnId === turn.id) {
                this.stopVoicePractice();
                return;
            }
            this.pauseAllVideos();
            this.stopVoicePractice();
            this.activeTurnId = turn.id;
            this.isListening = true;
            this.speechStartedAt = performance.now();

            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                try {
                    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                    this.recordedChunks = [];
                    this.mediaRecorder = new MediaRecorder(stream);
                    this.mediaRecorder.ondataavailable = (e) => {
                        if (e.data && e.data.size > 0) this.recordedChunks.push(e.data);
                    };
                    this.mediaRecorder.onstop = () => {
                        stream.getTracks().forEach((t) => t.stop());
                        if (this.recordedChunks.length) {
                            const blob = new Blob(this.recordedChunks, { type: 'audio/webm' });
                            const mirrorUrl = URL.createObjectURL(blob);
                            const prev = this.turnStates[turn.id] || {};
                            this.turnStates[turn.id] = { ...prev, mirrorUrl };

                            if (turn.data?.is_baseline && !this.beforeAttempt) {
                                this.beforeAttempt = { mirrorUrl, score: prev.score ?? 0 };
                            }
                            if (turn.data?.is_mastery) {
                                this.afterAttempt = { mirrorUrl, score: prev.score ?? 0 };
                            }
                            this.playAcousticMirrorSequence(turn);
                        }
                    };
                    this.mediaRecorder.start();
                } catch (_) {}
            }

            const SpeechRec = window.SpeechRecognition || window.webkitSpeechRecognition;
            if (!SpeechRec) {
                setTimeout(() => {
                    if (this.isListening && this.activeTurnId === turn.id) {
                        this.stopVoicePractice();
                    }
                }, 4000);
                return;
            }

            this.recognition = new SpeechRec();
            this.recognition.lang = 'en-US';
            this.recognition.interimResults = true;
            this.recognition.onresult = (event) => {
                let transcript = '';
                for (let i = 0; i < event.results.length; i++) {
                    transcript += event.results[i][0].transcript + ' ';
                }
                const target = (turn.data.phrases && turn.data.phrases[0]) || '';
                const { diff, score } = this.computeWordDiff(target, transcript.trim());
                const elapsedMs = Math.max(400, performance.now() - this.speechStartedAt);
                const rhythmLabel = this.computeRhythmBadge(elapsedMs, target);

                const passed = score >= 75;
                const praises = [
                    'Spot on! 🎉 Great job!',
                    'Nice rhythm! Keep going 💪',
                    'You nailed that! ✨',
                    'Awesome pronunciation! 🌟',
                ];
                const tutorReply = passed
                    ? praises[Math.floor(Math.random() * praises.length)]
                    : 'Almost there! Tap any red word to hear it at 0.75x, or try once more.';

                this.turnStates[turn.id] = {
                    ...(this.turnStates[turn.id] || {}),
                    spoken: transcript.trim(),
                    diff,
                    score,
                    rhythmLabel,
                    tutorReply,
                    tutorReplyPassed: passed,
                };

                if (turn.data?.is_baseline) {
                    this.beforeAttempt = { ...(this.beforeAttempt || {}), score };
                }
                if (turn.data?.is_mastery) {
                    this.afterAttempt = { ...(this.afterAttempt || {}), score };
                }

                this.saveProgress();

                if (event.results[event.results.length - 1]?.isFinal && score >= 75) {
                    setTimeout(() => this.advanceStep(), 900);
                }
            };
            this.recognition.onend = () => this.stopVoicePractice();
            this.recognition.onerror = () => this.stopVoicePractice();
            this.recognition.start();
        },

        stopVoicePractice() {
            this.isListening = false;
            if (this.recognition) {
                try { this.recognition.stop(); } catch (_) {}
                this.recognition = null;
            }
            if (this.mediaRecorder && this.mediaRecorder.state !== 'inactive') {
                try { this.mediaRecorder.stop(); } catch (_) {}
            }
        },

        computeRhythmBadge(spokenMs, targetText) {
            const words = (targetText || '').trim().split(/\s+/).filter(Boolean).length;
            const expectedMs = Math.max(1200, Math.min(this.nativeDurationMs, words * 450));
            const ratio = Math.min(spokenMs, expectedMs) / Math.max(spokenMs, expectedMs);
            const pct = Math.round(ratio * 100);
            if (pct >= 80) return `${pct}% Rhythm Match`;
            if (spokenMs > expectedMs) return 'Try slightly faster to match native pace';
            return 'Slow down slightly to match native cadence';
        },

        checkDictation(turn) {
            const spoken = (this.dictationInputs[turn.id] || '').trim();
            const { diff, score } = this.computeWordDiff(turn.data.text || '', spoken);
            this.turnStates[turn.id] = { spoken, diff, score, rhythmLabel: `${score}% Accuracy` };
            this.saveProgress();
            if (score >= 80) {
                this.advanceStep();
            }
        },

        isQuizOptionCorrect(turn, opt, idx) {
            if (typeof opt === 'object' && opt !== null && 'is_correct' in opt) {
                return !!opt.is_correct;
            }
            return idx === turn.data?.correct_index;
        },

        selectQuizOption(turn, idx) {
            this.quizSelections[turn.id] = idx;
            this.saveProgress();
            this.advanceStep();
        },

        levenshtein(a, b) {
            const dp = Array.from({ length: a.length + 1 }, () => Array(b.length + 1).fill(0));
            for (let i = 0; i <= a.length; i++) dp[i][0] = i;
            for (let j = 0; j <= b.length; j++) dp[0][j] = j;
            for (let i = 1; i <= a.length; i++) {
                for (let j = 1; j <= b.length; j++) {
                    dp[i][j] = a[i - 1] === b[j - 1]
                        ? dp[i - 1][j - 1]
                        : 1 + Math.min(dp[i - 1][j], dp[i][j - 1], dp[i - 1][j - 1]);
                }
            }
            return dp[a.length][b.length];
        },

        computeWordDiff(targetText, spokenText) {
            const norm = (s) => (s || '').toLowerCase().replace(/[^a-z0-9'\s]/g, ' ').trim().split(/\s+/).filter(Boolean);
            const targetWords = norm(targetText);
            const spokenWords = norm(spokenText);
            const n = targetWords.length;
            const m = spokenWords.length;
            const dp = Array.from({ length: n + 1 }, () => Array(m + 1).fill(0));
            for (let i = 1; i <= n; i++) {
                for (let j = 1; j <= m; j++) {
                    dp[i][j] = targetWords[i - 1] === spokenWords[j - 1]
                        ? dp[i - 1][j - 1] + 1
                        : Math.max(dp[i - 1][j], dp[i][j - 1]);
                }
            }

            const diff = [];
            let i = n, j = m, matchedCount = 0;
            while (i > 0 || j > 0) {
                if (i > 0 && j > 0 && targetWords[i - 1] === spokenWords[j - 1]) {
                    diff.unshift({ word: targetWords[i - 1], status: 'green' });
                    matchedCount++;
                    i--; j--;
                } else if (i > 0 && j > 0) {
                    const dist = this.levenshtein(targetWords[i - 1], spokenWords[j - 1]);
                    const maxLen = Math.max(targetWords[i - 1].length, spokenWords[j - 1].length, 1);
                    const sim = 1 - dist / maxLen;
                    diff.unshift({ word: targetWords[i - 1], status: sim >= 0.5 ? 'amber' : 'red' });
                    i--; j--;
                } else if (j > 0) {
                    j--;
                } else {
                    diff.unshift({ word: targetWords[i - 1], status: 'red' });
                    i--;
                }
            }
            const score = n > 0 ? Math.round((matchedCount / n) * 100) : 0;
            return { diff, score };
        }
    };
}
</script>
