@extends('layouts.public', ['nav_theme' => 'dark'])
@section('title', 'Spoken English Course Library (Malayalam to Fluent English) | Amter')
@section('meta_description', 'Explore Amter Spoken English course sessions: Basic English, Great Speeches, Documentaries, and daily fluency lessons designed for Malayalam speakers to speak as fluently as native English speakers.')
@section('meta_keywords', 'amter, amter spoken english, amter course library, spoken english malayalam classes, free spoken english lessons malayalam, learn english speaking kerala')

@section('structured_data')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "ItemList",
  "name": "Amter Spoken English Course Sessions (Malayalam to Fluent English)",
  "description": "Interactive Spoken English sessions designed for Malayalis to achieve native-like English fluency.",
  "numberOfItems": {{ $sessions->count() }},
  "itemListElement": [
    @foreach($sessions as $idx => $s)
    {
      "@@type": "ListItem",
      "position": {{ $idx + 1 }},
      "item": {
        "@@type": "Course",
        "name": @json($s->title),
        "description": @json($s->description ?: "Interactive Spoken English session ({$s->units_count} lessons) on Amter Spoken English."),
        "provider": {
          "@@id": "{{ url('/') }}/#organization"
        }
      }
    }{{ !$loop->last ? ',' : '' }}
    @endforeach
  ]
}
</script>
@endsection

@section('content')
@php
    $moduleNames = $sessions
        ->map(fn($s) => $s->module->name ?? 'Course')
        ->filter()
        ->unique()
        ->values();
@endphp

<style>
    .amter-lib-page {
        background-color: #090d16;
        background-image:
            radial-gradient(circle at 20% 0%, rgba(0, 194, 232, 0.14) 0%, transparent 45%),
            radial-gradient(circle at 80% 5%, rgba(99, 102, 241, 0.14) 0%, transparent 45%),
            linear-gradient(180deg, #0f172a 0%, #090d16 100%);
        min-height: 100vh;
        padding-top: 5.75rem;
        padding-bottom: 5rem;
        color: #f8fafc;
    }
    .amter-lib-container {
        max-width: 1280px;
        margin: 0 auto;
        padding: 0 1rem;
    }
    @media (min-width: 640px) {
        .amter-lib-container { padding: 0 1.5rem; }
    }
    @media (min-width: 1024px) {
        .amter-lib-container { padding: 0 2rem; }
    }

    /* Hero Spotlight Card */
    .amter-lib-hero {
        position: relative;
        border-radius: 1.75rem;
        overflow: hidden;
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 65%, #0f172a 100%);
        border: 1.5px solid rgba(56, 189, 248, 0.35);
        box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.7), 0 0 40px -10px rgba(0, 194, 232, 0.2);
        margin-bottom: 2.25rem;
    }
    .amter-lib-hero-inner {
        position: relative;
        z-index: 2;
        padding: 1.35rem;
        display: flex;
        flex-direction: column;
        gap: 1.35rem;
    }
    @media (min-width: 768px) {
        .amter-lib-hero-inner {
            flex-direction: row;
            align-items: center;
            padding: 2rem 2.25rem;
            gap: 2.25rem;
        }
    }
    .amter-lib-hero-poster {
        display: block;
        position: relative;
        width: 100%;
        height: 190px;
        border-radius: 1.25rem;
        overflow: hidden;
        flex-shrink: 0;
        background: #090d16;
        border: 1px solid rgba(255, 255, 255, 0.18);
        box-shadow: 0 15px 35px -10px rgba(0, 0, 0, 0.65);
        text-decoration: none;
    }
    @media (min-width: 768px) {
        .amter-lib-hero-poster {
            width: 320px;
            height: 185px;
        }
    }
    @media (min-width: 1024px) {
        .amter-lib-hero-poster {
            width: 350px;
            height: 200px;
        }
    }
    .amter-lib-hero-poster picture,
    .amter-lib-hero-poster img,
    .amter-lib-card-poster picture,
    .amter-lib-card-poster img {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
        display: block !important;
        transition: transform 0.5s ease;
    }
    .amter-lib-hero-poster:hover img,
    .amter-lib-card:hover .amter-lib-card-poster img {
        transform: scale(1.06);
    }
    .amter-lib-play-circle {
        width: 54px;
        height: 54px;
        border-radius: 9999px;
        background: linear-gradient(135deg, #00c2e8 0%, #38bdf8 100%);
        color: #090d16;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 25px rgba(0, 194, 232, 0.55);
        transition: transform 0.25s ease;
    }
    .amter-lib-hero-poster:hover .amter-lib-play-circle {
        transform: scale(1.1);
    }

    /* Filter Pills & Toolbar */
    .amter-lib-toolbar {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.09);
    }
    @media (min-width: 768px) {
        .amter-lib-toolbar {
            flex-direction: row;
            align-items: flex-end;
            justify-content: space-between;
        }
    }
    .amter-lib-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1.35rem;
    }
    .amter-lib-pill {
        padding: 0.45rem 0.95rem;
        border-radius: 9999px;
        font-size: 0.76rem;
        font-weight: 800;
        cursor: pointer;
        border: 1px solid rgba(255, 255, 255, 0.12);
        background: rgba(30, 41, 59, 0.7);
        color: #cbd5e1;
        transition: all 0.2s;
    }
    .amter-lib-pill:hover {
        background: rgba(51, 65, 85, 0.9);
        color: #ffffff;
    }
    .amter-lib-pill-active {
        background: linear-gradient(90deg, #00c2e8, #38bdf8) !important;
        color: #090d16 !important;
        border-color: transparent !important;
        box-shadow: 0 4px 14px rgba(0, 194, 232, 0.35);
    }

    /* Responsive 2 / 3 / 4 Column Movie Poster Grid */
    .amter-lib-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.85rem;
    }
    @media (min-width: 640px) {
        .amter-lib-grid {
            gap: 1.25rem;
        }
    }
    @media (min-width: 768px) {
        .amter-lib-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }
    @media (min-width: 1100px) {
        .amter-lib-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1.35rem;
        }
    }

    /* Movie Card */
    .amter-lib-card {
        background: #131c2e;
        border-radius: 1.25rem;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 12px 30px -8px rgba(0, 0, 0, 0.5);
        transition: transform 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
    }
    .amter-lib-card:hover {
        transform: translateY(-4px);
        border-color: rgba(56, 189, 248, 0.5);
        box-shadow: 0 18px 38px -10px rgba(0, 194, 232, 0.22);
    }
    .amter-lib-card-featured {
        border: 2px solid #00c2e8;
        box-shadow: 0 14px 35px -8px rgba(0, 194, 232, 0.3);
    }
    .amter-lib-card-poster {
        display: block;
        position: relative;
        width: 100%;
        height: 130px;
        overflow: hidden;
        background: #090d16;
        text-decoration: none;
    }
    @media (min-width: 640px) {
        .amter-lib-card-poster {
            height: 158px;
        }
    }
    .amter-lib-card-body {
        padding: 0.85rem;
        display: flex;
        flex-direction: column;
        flex: 1;
        justify-content: space-between;
        gap: 0.75rem;
    }
    @media (min-width: 640px) {
        .amter-lib-card-body {
            padding: 1rem 1.05rem;
        }
    }
    .amter-lib-btn-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        width: 100%;
        padding: 0.6rem 0.75rem;
        border-radius: 0.75rem;
        background: linear-gradient(90deg, #00c2e8 0%, #38bdf8 100%);
        color: #090d16;
        font-size: 0.72rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        text-decoration: none;
        transition: filter 0.2s, transform 0.15s;
    }
    .amter-lib-btn-primary:hover {
        filter: brightness(1.08);
    }
    .amter-lib-btn-secondary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.35rem;
        width: 100%;
        padding: 0.48rem 0.75rem;
        border-radius: 0.75rem;
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid rgba(255, 255, 255, 0.12);
        color: #e2e8f0;
        font-size: 0.68rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        cursor: pointer;
        transition: background 0.2s;
    }
    .amter-lib-btn-secondary:hover {
        background: rgba(255, 255, 255, 0.14);
    }
</style>

<div x-data="movieLibraryHandler(@js($sessionsModalData ?? []))" class="amter-lib-page">
    <div class="amter-lib-container">

        {{-- 1. HERO SPOTLIGHT BANNER: Last Class Attended (or Featured Free Session for First-Comers) --}}
        @if(isset($heroSession) && $heroSession)
            @php
                $heroUnit = $heroSession->resume_unit;
                $heroRawTitle = trim((string) ($heroUnit?->title ?? '1'));
                $heroLessonLabel = ctype_digit($heroRawTitle) ? "Lesson {$heroRawTitle}" : $heroRawTitle;
            @endphp
            <div class="amter-lib-hero">
                <div class="amter-lib-hero-inner">
                    {{-- Compact 16:9 Widescreen Poster --}}
                    <a href="{{ $heroSession->target_url }}" @if($heroSession->target_onclick) onclick="{!! $heroSession->target_onclick !!}" @endif class="amter-lib-hero-poster">
                        @if($heroSession->thumbnail_path)
                            <x-image :src="$heroSession->thumbnail_path" :width="480" :height="270" alt="{{ $heroSession->title }}" />
                        @else
                            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #475569; font-weight: 900; text-transform: uppercase; letter-spacing: 0.1em; font-size: 1.5rem;">amter</div>
                        @endif

                        {{-- Play Button Overlay --}}
                        <div style="position: absolute; inset: 0; background: rgba(9, 13, 22, 0.28); display: flex; align-items: center; justify-content: center;">
                            <div class="amter-lib-play-circle">
                                <svg style="width: 24px; height: 24px; margin-left: 2px;" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg>
                            </div>
                        </div>

                        {{-- Bottom Progress Strip --}}
                        @if($heroSession->progress_percent > 0)
                            <div style="position: absolute; bottom: 0; left: 0; right: 0; height: 5px; background: rgba(9, 13, 22, 0.85);">
                                <div style="height: 100%; width: {{ max(6, $heroSession->progress_percent) }}%; background: linear-gradient(90deg, #00c2e8, #10b981);"></div>
                            </div>
                        @endif
                    </a>

                    {{-- Spotlight Details --}}
                    <div style="flex: 1; min-width: 0;">
                        <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem; margin-bottom: 0.65rem;">
                            @if($hasHistory)
                                <span style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.28rem 0.8rem; border-radius: 9999px; background: rgba(0, 194, 232, 0.18); border: 1px solid rgba(56, 189, 248, 0.45); color: #38bdf8; font-size: 0.7rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.06em;">
                                    <span style="width: 7px; height: 7px; border-radius: 9999px; background: #00c2e8;"></span>
                                    Continue Where You Left Off
                                </span>
                            @else
                                <span style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.28rem 0.8rem; border-radius: 9999px; background: rgba(16, 185, 129, 0.18); border: 1px solid rgba(52, 211, 153, 0.45); color: #6ee7b7; font-size: 0.7rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.06em;">
                                    <span style="width: 7px; height: 7px; border-radius: 9999px; background: #10b981;"></span>
                                    Featured Free Session &bull; Start Here
                                </span>
                            @endif

                            <span style="padding: 0.28rem 0.75rem; border-radius: 9999px; background: rgba(255, 255, 255, 0.09); color: #cbd5e1; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;">
                                {{ $heroSession->module->name ?? 'Course Session' }}
                            </span>
                        </div>

                        <h1 style="font-size: clamp(1.45rem, 3vw, 2.25rem); font-weight: 900; color: #ffffff; margin: 0 0 0.35rem 0; line-height: 1.2;">
                            {{ $heroSession->title }}
                        </h1>

                        <p style="font-size: 0.92rem; color: #38bdf8; font-weight: 700; margin: 0 0 0.65rem 0;">
                            Up Next: <span style="color: #ffffff;">{{ $heroLessonLabel }}</span>
                            <span style="color: #94a3b8; font-weight: 600;">&bull; {{ $heroSession->units_count }} Interactive Lessons</span>
                        </p>

                        @if($heroSession->description)
                            <p style="font-size: 0.84rem; color: #cbd5e1; margin: 0 0 1rem 0; max-width: 640px; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                {{ $heroSession->description }}
                            </p>
                        @endif

                        @if($heroSession->is_started)
                            <div style="max-width: 380px; margin-bottom: 1.1rem;">
                                <div style="display: flex; justify-content: space-between; font-size: 0.72rem; font-weight: 800; color: #cbd5e1; margin-bottom: 0.35rem;">
                                    <span>Your Progress</span>
                                    <span style="color: #38bdf8;">{{ $heroSession->completed_count }} / {{ $heroSession->units_count }} Mastered ({{ $heroSession->progress_percent }}%)</span>
                                </div>
                                <div style="height: 7px; background: rgba(255,255,255,0.1); border-radius: 9999px; overflow: hidden;">
                                    <div style="height: 100%; width: {{ max(5, $heroSession->progress_percent) }}%; background: linear-gradient(90deg, #00c2e8, #10b981); border-radius: 9999px;"></div>
                                </div>
                            </div>
                        @endif

                        {{-- Action Buttons --}}
                        <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem;">
                            <a href="{{ $heroSession->target_url }}" @if($heroSession->target_onclick) onclick="{!! $heroSession->target_onclick !!}" @endif
                               style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.8rem 1.65rem; border-radius: 9999px; background: linear-gradient(90deg, #00c2e8 0%, #38bdf8 100%); color: #090d16; font-size: 0.82rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; text-decoration: none; box-shadow: 0 10px 25px rgba(0, 194, 232, 0.35);">
                                <svg style="width: 15px; height: 15px;" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg>
                                <span>{{ $heroSession->cta_label }}</span>
                            </a>

                            <button type="button"
                                    @click="openSessionModal({{ $heroSession->id }})"
                                    style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.8rem 1.3rem; border-radius: 9999px; background: rgba(255, 255, 255, 0.09); border: 1px solid rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 0.78rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; cursor: pointer;">
                                <svg style="width: 15px; height: 15px; color: #38bdf8;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"/></svg>
                                <span>All {{ $heroSession->units_count }} Lessons</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- 2. LIBRARY HEADER, SEARCH & CATEGORY FILTER PILLS --}}
        <div id="courses">
            <div class="amter-lib-toolbar">
                <div>
                    <span style="font-size: 0.72rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.1em; color: #38bdf8; display: block; margin-bottom: 0.2rem;">Choose Any Session or Topic</span>
                    <h2 style="font-size: 1.65rem; font-weight: 900; color: #ffffff; margin: 0;">Course Sessions Library</h2>
                </div>

                <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.65rem;">
                    {{-- Quick Search Box --}}
                    <div style="position: relative; min-width: 210px;">
                        <input type="text"
                               x-model="searchQuery"
                               placeholder="Search sessions..."
                               style="width: 100%; padding: 0.5rem 0.9rem 0.5rem 2.1rem; border-radius: 9999px; background: rgba(30, 41, 59, 0.85); border: 1px solid rgba(255,255,255,0.14); color: #ffffff; font-size: 0.8rem; outline: none;">
                        <svg style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); width: 14px; height: 14px; color: #94a3b8;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>

                    @auth
                        <a href="{{ route('filament.student.pages.dashboard') }}" style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 1rem; background: rgba(30, 41, 59, 0.9); color: #e2e8f0; border-radius: 9999px; font-weight: 800; font-size: 0.78rem; border: 1px solid rgba(255,255,255,0.15); text-decoration: none;">
                            <span>My Dashboard</span>
                        </a>
                    @endauth

                    <a href="{{ url('/?review=1#reviews') }}" style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.5rem 1rem; background: rgba(30, 41, 59, 0.9); color: #cbd5e1; border-radius: 9999px; font-weight: 800; font-size: 0.78rem; border: 1px solid rgba(255,255,255,0.15); text-decoration: none;">
                        <span style="color: #fbbf24;">★</span> Reviews
                    </a>
                </div>
            </div>

            {{-- Category Filter Pills --}}
            <div class="amter-lib-pills">
                <button type="button" @click="selectedCategory = 'all'"
                        class="amter-lib-pill"
                        :class="selectedCategory === 'all' ? 'amter-lib-pill-active' : ''">
                    All Sessions ({{ $sessions->count() }})
                </button>
                <button type="button" @click="selectedCategory = 'free'"
                        class="amter-lib-pill"
                        :class="selectedCategory === 'free' ? 'amter-lib-pill-active' : ''">
                    🎁 Free Classes
                </button>
                @foreach($moduleNames as $modName)
                    <button type="button" @click="selectedCategory = @js($modName)"
                            class="amter-lib-pill"
                            :class="selectedCategory === @js($modName) ? 'amter-lib-pill-active' : ''">
                        {{ $modName }}
                    </button>
                @endforeach
            </div>

            {{-- 3. SESSIONS GRID (2 columns mobile, 3 tablet, 4 desktop) --}}
            <div class="amter-lib-grid">
                @forelse($sessions as $session)
                    @php
                        $sessionFreeCount = $session->units->filter(fn($u) => $u->is_published && ($u->is_free_sample || $u->is_registered_only))->count();
                        $hasGuestFree = $session->units->contains('is_free_sample', true);
                        $resumeUnit = $session->resume_unit;
                        $resumeRawTitle = trim((string) ($resumeUnit?->title ?? '1'));
                        $resumeLessonText = ctype_digit($resumeRawTitle) ? "Lesson {$resumeRawTitle}" : $resumeRawTitle;
                        $modTitle = $session->module->name ?? 'Course';
                    @endphp
                    <div class="amter-lib-card {{ $session->is_last_attended ? 'amter-lib-card-featured' : '' }}"
                         x-show="matchesFilter(@js($session->title), @js($modTitle), {{ $sessionFreeCount > 0 ? 'true' : 'false' }})">

                        {{-- Poster Thumbnail --}}
                        <a href="{{ $session->target_url }}" @if($session->target_onclick) onclick="{!! $session->target_onclick !!}" @endif class="amter-lib-card-poster">
                            @if($session->thumbnail_path)
                                <x-image :src="$session->thumbnail_path"
                                         :width="360"
                                         :height="200"
                                         alt="{{ $session->title }}" />
                            @else
                                <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #475569; font-weight: 900; text-transform: uppercase;">
                                    amter
                                </div>
                            @endif

                            {{-- Gradient Overlay --}}
                            <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(9, 13, 22, 0.92) 0%, rgba(9, 13, 22, 0.25) 55%, rgba(9, 13, 22, 0.15) 100%);"></div>

                            {{-- Top Left Badge --}}
                            @if($session->is_last_attended)
                                <div style="position: absolute; top: 0.55rem; left: 0.55rem; background: #00c2e8; color: #090d16; padding: 0.2rem 0.6rem; border-radius: 9999px; font-size: 0.62rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.04em; box-shadow: 0 4px 10px rgba(0,0,0,0.4);">
                                    ● Last Attended
                                </div>
                            @elseif($sessionFreeCount > 0)
                                <div style="position: absolute; top: 0.55rem; left: 0.55rem; display: inline-flex; align-items: center; gap: 0.25rem; background: {{ $hasGuestFree ? '#10b981' : '#2563eb' }}; color: #ffffff; padding: 0.18rem 0.55rem; border-radius: 9999px; font-size: 0.62rem; font-weight: 900; text-transform: uppercase; box-shadow: 0 4px 10px rgba(0,0,0,0.4);">
                                    <span style="background: #ffffff; color: {{ $hasGuestFree ? '#047857' : '#1d4ed8' }}; padding: 0 0.3rem; border-radius: 9999px; font-weight: 900;">{{ $sessionFreeCount }}</span>
                                    <span>Free</span>
                                </div>
                            @else
                                <div style="position: absolute; top: 0.55rem; left: 0.55rem; background: rgba(15, 23, 42, 0.88); color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.35); padding: 0.18rem 0.55rem; border-radius: 9999px; font-size: 0.62rem; font-weight: 800; text-transform: uppercase;">
                                    ★ Premium
                                </div>
                            @endif

                            {{-- Top Right Module Badge --}}
                            <div style="position: absolute; top: 0.55rem; right: 0.55rem; background: rgba(9, 13, 22, 0.8); border: 1px solid rgba(255,255,255,0.16); color: #e2e8f0; padding: 0.18rem 0.55rem; border-radius: 9999px; font-size: 0.58rem; font-weight: 800; text-transform: uppercase;">
                                {{ $modTitle }}
                            </div>

                            {{-- Bottom Lesson Count / Next Lesson --}}
                            <div style="position: absolute; bottom: 0.5rem; left: 0.6rem; right: 0.6rem; display: flex; justify-content: space-between; align-items: center; font-size: 0.68rem; font-weight: 800; color: #ffffff;">
                                <span style="background: rgba(9, 13, 22, 0.65); padding: 0.15rem 0.5rem; border-radius: 0.4rem; border: 1px solid rgba(255,255,255,0.1); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $session->is_started ? "Next: {$resumeLessonText}" : "{$session->units_count} Lessons" }}
                                </span>
                                @if($session->completed_count > 0)
                                    <span style="color: #34d399; background: rgba(6, 78, 59, 0.85); padding: 0.12rem 0.45rem; border-radius: 0.4rem; font-size: 0.64rem;">
                                        {{ $session->completed_count }}/{{ $session->units_count }}
                                    </span>
                                @endif
                            </div>

                            {{-- Progress Bar --}}
                            @if($session->is_started)
                                <div style="position: absolute; bottom: 0; left: 0; right: 0; height: 4px; background: #090d16;">
                                    <div style="height: 100%; width: {{ max(6, $session->progress_percent) }}%; background: linear-gradient(90deg, #00c2e8, #10b981);"></div>
                                </div>
                            @endif
                        </a>

                        {{-- Card Content --}}
                        <div class="amter-lib-card-body">
                            <div>
                                <a href="{{ $session->target_url }}" @if($session->target_onclick) onclick="{!! $session->target_onclick !!}" @endif style="text-decoration: none;">
                                    <h3 style="font-size: 0.95rem; font-weight: 800; color: #ffffff; margin: 0 0 0.3rem 0; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                        {{ $session->title }}
                                    </h3>
                                </a>

                                @if($session->description)
                                    <p style="font-size: 0.73rem; color: #94a3b8; margin: 0; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                        {{ $session->description }}
                                    </p>
                                @endif
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 0.42rem;">
                                <a href="{{ $session->target_url }}" @if($session->target_onclick) onclick="{!! $session->target_onclick !!}" @endif class="amter-lib-btn-primary">
                                    <span>▶ {{ $session->cta_label }}</span>
                                </a>

                                <button type="button"
                                        @click="openSessionModal({{ $session->id }})"
                                        class="amter-lib-btn-secondary">
                                    <span>☰ All {{ $session->units_count }} Lessons</span>
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div style="grid-column: 1 / -1; padding: 4rem 1.5rem; text-align: center; background: rgba(255,255,255,0.03); border-radius: 1.5rem; border: 1px solid rgba(255,255,255,0.08);">
                        <h3 style="font-size: 1.35rem; font-weight: 900; color: #ffffff; margin: 0;">New Courses Incoming</h3>
                        <p style="margin-top: 0.5rem; color: #94a3b8; font-size: 0.88rem;">We are preparing new interactive sessions. Check back shortly!</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- 4. NETFLIX-STYLE EPISODES / LESSONS MODAL DRAWER --}}
    <template x-teleport="body">
        <div x-show="modalOpen"
             x-cloak
             @keydown.escape.window="modalOpen = false"
             style="position: fixed; inset: 0; z-index: 99999; display: flex; align-items: center; justify-content: center; padding: 1rem;">
            {{-- Backdrop --}}
            <div style="position: fixed; inset: 0; background: rgba(9, 13, 22, 0.86); backdrop-filter: blur(10px);" @click="modalOpen = false"></div>

            {{-- Modal Box --}}
            <div x-show="activeSession"
                 style="position: relative; z-index: 10; width: 100%; max-width: 620px; background: #0f172a; border: 1px solid rgba(255,255,255,0.16); border-radius: 1.5rem; box-shadow: 0 25px 60px rgba(0,0,0,0.85); overflow: hidden; display: flex; flex-direction: column; max-height: 84vh; color: #ffffff;">

                {{-- Modal Header --}}
                <div style="padding: 1.25rem 1.5rem; background: #090d16; border-bottom: 1px solid rgba(255,255,255,0.1); display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem;">
                    <div>
                        <span style="font-size: 0.68rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.08em; color: #38bdf8; display: block;" x-text="activeSession?.module"></span>
                        <h3 style="font-size: 1.3rem; font-weight: 900; color: #ffffff; margin: 0.15rem 0;" x-text="activeSession?.title"></h3>
                        <p style="font-size: 0.76rem; color: #94a3b8; margin: 0;">
                            <span x-text="activeSession?.completed"></span> of <span x-text="activeSession?.total"></span> lessons completed (<span x-text="activeSession?.percent"></span>%)
                        </p>
                    </div>
                    <button type="button" @click="modalOpen = false" style="width: 34px; height: 34px; border-radius: 9999px; background: rgba(255,255,255,0.1); border: none; color: #cbd5e1; font-weight: 800; cursor: pointer;">
                        ✕
                    </button>
                </div>

                {{-- Filter / Jump Input --}}
                <div style="padding: 0.75rem 1.5rem; background: rgba(30, 41, 59, 0.55); border-bottom: 1px solid rgba(255,255,255,0.08); display: flex; align-items: center; gap: 0.6rem;">
                    <svg style="width: 15px; height: 15px; color: #94a3b8; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text"
                           x-model="episodeFilter"
                           placeholder="Jump to lesson number (e.g. 56) or title..."
                           style="width: 100%; background: transparent; border: none; outline: none; font-size: 0.88rem; color: #ffffff;">
                    <button x-show="episodeFilter" type="button" @click="episodeFilter = ''" style="background: transparent; border: none; font-size: 0.75rem; color: #38bdf8; cursor: pointer; font-weight: 700;">Clear</button>
                </div>

                {{-- Episodes List --}}
                <div style="padding: 1rem 1.25rem; overflow-y: auto; flex: 1; display: flex; flex-direction: column; gap: 0.5rem;">
                    <template x-for="ep in filteredEpisodes" :key="ep.id">
                        <div @click="handleEpisodeClick(ep)"
                             style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; padding: 0.8rem 1rem; border-radius: 1rem; cursor: pointer; transition: background 0.15s;"
                             :style="ep.current ? 'background: rgba(0, 194, 232, 0.15); border: 1px solid rgba(56, 189, 248, 0.5);' : 'background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.08);'">
                            <div style="display: flex; align-items: center; gap: 0.8rem; min-width: 0;">
                                <div style="width: 34px; height: 34px; border-radius: 0.65rem; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 0.76rem; flex-shrink: 0;"
                                     :style="ep.completed ? 'background: #10b981; color: #090d16;' : (ep.current ? 'background: #00c2e8; color: #090d16;' : 'background: #1e293b; color: #cbd5e1;')">
                                    <span x-show="ep.completed">✓</span>
                                    <span x-show="!ep.completed" x-text="ep.order"></span>
                                </div>
                                <div style="min-width: 0;">
                                    <div style="font-size: 0.9rem; font-weight: 800; color: #ffffff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" x-text="ep.title"></div>
                                    <div style="font-size: 0.7rem; color: #94a3b8; margin-top: 0.1rem;">
                                        <span x-show="ep.current" style="color: #38bdf8; font-weight: 800;">● Up Next</span>
                                        <span x-show="ep.completed" style="color: #34d399; font-weight: 800;">Mastered</span>
                                        <span x-show="!ep.completed && !ep.current">Interactive Lesson</span>
                                    </div>
                                </div>
                            </div>

                            <div style="display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0;">
                                <span x-show="ep.free_guest" style="padding: 0.18rem 0.5rem; border-radius: 9999px; background: rgba(16, 185, 129, 0.2); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.35); font-size: 0.62rem; font-weight: 900; text-transform: uppercase;">Free</span>
                                <span x-show="!ep.free_guest && ep.free_registered" style="padding: 0.18rem 0.5rem; border-radius: 9999px; background: rgba(59, 130, 246, 0.2); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.35); font-size: 0.62rem; font-weight: 900; text-transform: uppercase;">Free Account</span>
                                <span style="padding: 0.4rem 0.8rem; border-radius: 0.65rem; font-size: 0.7rem; font-weight: 900; text-transform: uppercase;"
                                      :style="ep.current ? 'background: #00c2e8; color: #090d16;' : 'background: rgba(255,255,255,0.1); color: #ffffff;'">
                                    Play ▶
                                </span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
    function movieLibraryHandler(sessionsData) {
        return {
            sessions: sessionsData || {},
            modalOpen: false,
            activeSession: null,
            episodeFilter: '',
            selectedCategory: 'all',
            searchQuery: '',

            matchesFilter(title, modName, hasFree) {
                if (this.selectedCategory === 'free' && !hasFree) {
                    return false;
                }
                if (this.selectedCategory !== 'all' && this.selectedCategory !== 'free' && modName !== this.selectedCategory) {
                    return false;
                }
                const q = this.searchQuery.trim().toLowerCase();
                if (q && !String(title).toLowerCase().includes(q) && !String(modName).toLowerCase().includes(q)) {
                    return false;
                }
                return true;
            },

            openSessionModal(sessionId) {
                this.activeSession = this.sessions[sessionId] || null;
                this.episodeFilter = '';
                if (this.activeSession) {
                    this.modalOpen = true;
                }
            },

            get filteredEpisodes() {
                if (!this.activeSession || !this.activeSession.episodes) return [];
                const q = this.episodeFilter.trim().toLowerCase();
                if (!q) return this.activeSession.episodes;
                return this.activeSession.episodes.filter(ep =>
                    String(ep.order) === q ||
                    ep.title.toLowerCase().includes(q)
                );
            },

            handleEpisodeClick(ep) {
                if (ep.action === 'register') {
                    this.modalOpen = false;
                    if (typeof openRegisterModal === 'function') {
                        openRegisterModal();
                    } else {
                        window.location.href = '{{ route("register") }}';
                    }
                    return;
                }
                if (ep.action === 'pricing') {
                    if (confirm('Select a plan and get unlimited access to all classes.')) {
                        window.location.href = ep.url;
                    }
                    return;
                }
                window.location.href = ep.url;
            }
        };
    }
</script>
@endsection
