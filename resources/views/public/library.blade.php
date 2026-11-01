@extends('layouts.public', ['nav_theme' => 'dark'])
@section('title', 'Course Library - Learn Spoken English with amter')
@section('meta_description', 'Unlock the power of spoken English with amter\'s library of interactive video lessons, AI roleplay, and voice-matching challenges.')

@section('content')
<div x-data="movieLibraryHandler(@js($sessionsModalData ?? []))" class="bg-slate-950 min-h-screen pb-20" style="background: radial-gradient(circle at 50% 0%, #1e293b 0%, #090d16 70%);">

    {{-- 1. MOVIE-PLATFORM HERO SPOTLIGHT: Last Class Attended (or Featured Free Session for First-Comers) --}}
    <div class="relative pt-8 pb-6 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">

            @if(isset($heroSession) && $heroSession)
                @php
                    $heroUnit = $heroSession->resume_unit;
                    $heroRawTitle = trim((string) ($heroUnit?->title ?? '1'));
                    $heroLessonLabel = ctype_digit($heroRawTitle) ? "Lesson {$heroRawTitle}" : $heroRawTitle;
                @endphp
                <div class="relative rounded-3xl overflow-hidden border {{ $hasHistory ? 'border-cyan-400/40 shadow-[0_20px_60px_-15px_rgba(0,194,232,0.25)]' : 'border-white/15 shadow-2xl' }} bg-slate-900/90">
                    {{-- Ambient Backdrop Image --}}
                    @if($heroSession->thumbnail_path)
                        <div class="absolute inset-0 opacity-25 pointer-events-none">
                            <x-image :src="$heroSession->thumbnail_path" :width="800" :height="400" alt="{{ $heroSession->title }}" class="w-full h-full object-cover blur-md scale-110" />
                        </div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-r from-slate-950 via-slate-950/90 to-slate-900/60 pointer-events-none"></div>

                    <div class="relative z-10 p-6 sm:p-8 lg:p-10 flex flex-col md:flex-row items-center gap-6 lg:gap-10">
                        {{-- Poster Card --}}
                        <a href="{{ $heroSession->target_url }}" @if($heroSession->target_onclick) onclick="{!! $heroSession->target_onclick !!}" @endif class="w-full md:w-72 lg:w-80 shrink-0 rounded-2xl overflow-hidden border border-white/15 shadow-2xl relative group block aspect-video md:aspect-[16/10] bg-slate-800">
                            @if($heroSession->thumbnail_path)
                                <x-image :src="$heroSession->thumbnail_path" :width="480" :height="300" alt="{{ $heroSession->title }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
                            @else
                                <div class="w-full h-full flex items-center justify-center text-slate-400 font-black uppercase tracking-widest text-2xl">amter</div>
                            @endif
                            <div class="absolute inset-0 bg-black/30 group-hover:bg-black/10 transition-colors flex items-center justify-center">
                                <div class="w-14 h-14 rounded-full bg-primary-500 text-slate-950 flex items-center justify-center shadow-lg shadow-primary-500/50 transform group-hover:scale-110 transition-transform">
                                    <svg class="w-7 h-7 ml-0.5" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg>
                                </div>
                            </div>
                            @if($heroSession->progress_percent > 0)
                                <div class="absolute bottom-0 inset-x-0 h-1.5 bg-slate-950/80">
                                    <div class="h-full bg-gradient-to-r from-primary-400 to-emerald-400" style="width: {{ $heroSession->progress_percent }}%;"></div>
                                </div>
                            @endif
                        </a>

                        {{-- Spotlight Details --}}
                        <div class="flex-1 text-center md:text-left min-w-0">
                            <div class="inline-flex flex-wrap items-center justify-center md:justify-start gap-2 mb-3">
                                @if($hasHistory)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary-500/20 border border-primary-400/40 text-primary-300 text-xs font-black uppercase tracking-wider">
                                        <span class="w-2 h-2 rounded-full bg-primary-400 animate-ping"></span>
                                        Continue Where You Left Off
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/40 text-emerald-300 text-xs font-black uppercase tracking-wider">
                                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                        Featured Free Session &bull; Start Here
                                    </span>
                                @endif
                                <span class="px-2.5 py-1 rounded-full bg-white/10 text-slate-300 text-xs font-bold uppercase tracking-wider">
                                    {{ $heroSession->module->name ?? 'Course Session' }}
                                </span>
                            </div>

                            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black text-white tracking-tight leading-tight mb-2">
                                {{ $heroSession->title }}
                            </h1>

                            <p class="text-sm sm:text-base text-primary-300 font-bold mb-3">
                                Up Next: <span class="text-white">{{ $heroLessonLabel }}</span>
                                <span class="text-slate-400 font-medium">&bull; {{ $heroSession->units_count }} Interactive Lessons</span>
                            </p>

                            @if($heroSession->description)
                                <p class="text-xs sm:text-sm text-slate-300 line-clamp-2 max-w-2xl mb-4">
                                    {{ $heroSession->description }}
                                </p>
                            @endif

                            {{-- Progress Bar if started --}}
                            @if($heroSession->is_started)
                                <div class="max-w-md mx-auto md:mx-0 mb-5">
                                    <div class="flex justify-between text-xs font-bold text-slate-300 mb-1.5">
                                        <span>Session Progress</span>
                                        <span class="text-primary-400">{{ $heroSession->completed_count }} / {{ $heroSession->units_count }} Mastered ({{ $heroSession->progress_percent }}%)</span>
                                    </div>
                                    <div class="w-full h-2 bg-slate-800 rounded-full overflow-hidden border border-white/10">
                                        <div class="h-full bg-gradient-to-r from-primary-500 to-emerald-400 rounded-full transition-all duration-500" style="width: {{ max(4, $heroSession->progress_percent) }}%;"></div>
                                    </div>
                                </div>
                            @endif

                            {{-- Action Buttons --}}
                            <div class="flex flex-wrap items-center justify-center md:justify-start gap-3">
                                <a href="{{ $heroSession->target_url }}" @if($heroSession->target_onclick) onclick="{!! $heroSession->target_onclick !!}" @endif
                                   class="px-7 py-3.5 bg-gradient-to-r from-primary-500 to-cyan-400 hover:from-primary-400 hover:to-cyan-300 text-slate-950 font-black text-xs sm:text-sm uppercase tracking-wider rounded-full shadow-lg shadow-primary-500/30 transition-all transform hover:-translate-y-0.5 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg>
                                    <span>{{ $heroSession->cta_label }}</span>
                                </a>

                                <button type="button"
                                        @click="openSessionModal({{ $heroSession->id }})"
                                        class="px-5 py-3.5 bg-white/10 hover:bg-white/20 text-white border border-white/20 font-bold text-xs sm:text-sm uppercase tracking-wider rounded-full transition-all flex items-center gap-2 cursor-pointer">
                                    <svg class="w-4 h-4 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"/></svg>
                                    <span>All {{ $heroSession->units_count }} Lessons</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- 2. ALL COURSE SESSIONS GRID (Netflix / Movie Platform Style) --}}
    <div id="courses" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="mb-6 flex flex-col sm:flex-row justify-between items-center sm:items-end border-b border-slate-800 pb-4 gap-4">
            <div class="text-center sm:text-left">
                <span class="text-xs font-black uppercase tracking-widest text-primary-400 block mb-1">Choose Any Session or Topic</span>
                <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Course Sessions Library</h2>
            </div>

            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ route('filament.student.pages.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800/90 text-slate-200 rounded-full font-bold text-xs sm:text-sm border border-slate-700 hover:bg-slate-700 transition-colors">
                        <span>My Dashboard</span>
                    </a>
                @endauth
                <a href="{{ url('/?review=1#reviews') }}" class="hidden sm:inline-flex items-center gap-2 px-4 py-2 bg-slate-800/90 text-slate-300 rounded-full font-bold text-xs sm:text-sm border border-slate-700 hover:bg-slate-700 transition-colors">
                    <span class="text-primary-400">★</span> Reviews
                </a>
            </div>
        </div>

        {{-- Sessions Grid: 2 columns on mobile, 4 on desktop --}}
        <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3.5 sm:gap-6">
            @forelse($sessions as $session)
                @php
                    $sessionFreeCount = $session->units->filter(fn($u) => $u->is_published && ($u->is_free_sample || $u->is_registered_only))->count();
                    $hasGuestFree = $session->units->contains('is_free_sample', true);
                    $resumeUnit = $session->resume_unit;
                    $resumeRawTitle = trim((string) ($resumeUnit?->title ?? '1'));
                    $resumeLessonText = ctype_digit($resumeRawTitle) ? "Lesson {$resumeRawTitle}" : $resumeRawTitle;
                @endphp
                <div class="group relative rounded-2xl transition-all duration-300 hover:-translate-y-1.5 overflow-hidden flex flex-col w-full backdrop-blur h-auto {{ $session->is_last_attended ? 'bg-slate-900/95 border-2 border-primary-400/70 shadow-[0_12px_35px_-10px_rgba(0,194,232,0.35)]' : 'bg-white/5 border border-gray-700/60 hover:border-primary-500/40 shadow-lg' }}">

                    {{-- Poster Thumbnail Section --}}
                    <a href="{{ $session->target_url }}" @if($session->target_onclick) onclick="{!! $session->target_onclick !!}" @endif class="block relative h-32 sm:h-44 overflow-hidden bg-slate-800">
                        @if($session->thumbnail_path)
                            <x-image :src="$session->thumbnail_path"
                                     :width="360"
                                     :height="200"
                                     alt="{{ $session->title }}"
                                     class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" />
                        @else
                            <div class="w-full h-full flex items-center justify-center text-slate-500 font-black tracking-widest uppercase text-xl opacity-30">
                                amter
                            </div>
                        @endif

                        {{-- Dark Cinema Gradient Overlay --}}
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/30 to-transparent"></div>

                        {{-- Hover Play Icon --}}
                        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 bg-black/25">
                            <div class="w-11 h-11 rounded-full bg-primary-500 text-slate-950 flex items-center justify-center shadow-lg transform scale-90 group-hover:scale-100 transition-transform">
                                <svg class="w-5 h-5 ml-0.5" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg>
                            </div>
                        </div>

                        {{-- Top Left Badge: Last Attended OR Free/Premium --}}
                        @if($session->is_last_attended)
                            <div class="absolute top-2.5 left-2.5 flex items-center gap-1.5 bg-primary-500 text-slate-950 px-2.5 py-0.5 rounded-full shadow-md text-[8px] sm:text-[10px] font-black uppercase tracking-wider z-10">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-950 animate-ping"></span>
                                Last Attended
                            </div>
                        @elseif($sessionFreeCount > 0)
                            <div class="absolute top-2.5 left-2.5 flex items-center gap-1 {{ $hasGuestFree ? 'bg-emerald-500' : 'bg-blue-600' }} px-2 py-0.5 rounded-full shadow-md border border-white/80 z-10">
                                <span class="bg-white {{ $hasGuestFree ? 'text-emerald-600' : 'text-blue-700' }} px-1 rounded text-[9px] font-black leading-none">
                                    {{ $sessionFreeCount }}
                                </span>
                                <span class="text-white text-[8px] sm:text-[9px] font-black uppercase tracking-wider leading-none">
                                    Free
                                </span>
                            </div>
                        @else
                            <div class="absolute top-2.5 left-2.5 bg-slate-900/85 backdrop-blur-sm px-2 py-0.5 rounded-full text-[8px] sm:text-[9px] font-black tracking-widest text-amber-300 uppercase border border-amber-400/30 flex items-center gap-1">
                                <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>
                                Premium
                            </div>
                        @endif

                        {{-- Top Right Module Badge --}}
                        <div class="absolute top-2.5 right-2.5 bg-slate-950/80 backdrop-blur-md px-2 py-0.5 rounded-full text-[8px] sm:text-[9px] font-bold tracking-wider text-slate-200 uppercase border border-white/15">
                            {{ $session->module->name ?? 'Course' }}
                        </div>

                        {{-- Bottom Left Current Lesson Indicator --}}
                        <div class="absolute bottom-2.5 left-2.5 right-2.5 flex items-center justify-between gap-1 text-[10px] sm:text-xs font-extrabold text-white">
                            <span class="truncate bg-black/50 backdrop-blur-xs px-2 py-0.5 rounded-md border border-white/10">
                                {{ $session->is_started ? "Next: {$resumeLessonText}" : "{$session->units_count} Lessons" }}
                            </span>
                            @if($session->completed_count > 0)
                                <span class="shrink-0 text-[9px] sm:text-[10px] font-black text-emerald-300 bg-emerald-950/80 px-1.5 py-0.5 rounded border border-emerald-500/30">
                                    {{ $session->completed_count }}/{{ $session->units_count }}
                                </span>
                            @endif
                        </div>

                        {{-- Netflix-Style Bottom Progress Bar on Poster --}}
                        @if($session->is_started)
                            <div class="absolute bottom-0 inset-x-0 h-1 bg-slate-950">
                                <div class="h-full bg-gradient-to-r from-primary-400 to-emerald-400" style="width: {{ max(6, $session->progress_percent) }}%;"></div>
                            </div>
                        @endif
                    </a>

                    {{-- Card Content Section --}}
                    <div class="p-3 sm:p-4 flex flex-col flex-grow justify-between">
                        <div class="mb-3">
                            <a href="{{ $session->target_url }}" @if($session->target_onclick) onclick="{!! $session->target_onclick !!}" @endif>
                                <h3 class="text-xs sm:text-base font-extrabold text-white leading-snug mb-1.5 line-clamp-2 group-hover:text-primary-400 transition-colors">
                                    {{ $session->title }}
                                </h3>
                            </a>

                            @if($session->description)
                                <p class="text-[10px] sm:text-xs text-slate-400 line-clamp-2 leading-relaxed">
                                    {{ $session->description }}
                                </p>
                            @endif
                        </div>

                        {{-- Card Actions: Smart Resume Button + All Lessons Modal Trigger --}}
                        <div class="mt-auto pt-1 flex flex-col gap-1.5">
                            <a href="{{ $session->target_url }}" @if($session->target_onclick) onclick="{!! $session->target_onclick !!}" @endif
                               class="inline-flex justify-center items-center gap-1.5 w-full py-2 sm:py-2.5 rounded-xl text-[9px] sm:text-xs font-black uppercase tracking-wider shadow-md transition-all transform active:scale-95 {{ $session->is_last_attended ? 'bg-gradient-to-r from-primary-500 to-cyan-400 text-slate-950 hover:from-primary-400 hover:to-cyan-300' : 'bg-primary-500/90 hover:bg-primary-400 text-slate-950' }}">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg>
                                <span class="truncate">{{ $session->cta_label }}</span>
                            </a>

                            <button type="button"
                                    @click="openSessionModal({{ $session->id }})"
                                    class="inline-flex justify-center items-center gap-1.5 w-full py-1.5 sm:py-2 bg-slate-800/80 hover:bg-slate-700/90 text-slate-200 border border-slate-700 rounded-xl text-[9px] sm:text-[11px] font-bold uppercase tracking-wider transition-colors cursor-pointer">
                                <svg class="w-3 h-3 text-primary-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"/></svg>
                                <span>All {{ $session->units_count }} Lessons</span>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-24 text-center bg-white/5 rounded-3xl border border-slate-800">
                    <h3 class="text-2xl font-black text-white">New Courses Incoming</h3>
                    <p class="mt-2 text-slate-400 text-sm">We are preparing new interactive sessions. Check back shortly!</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- 3. NETFLIX-STYLE EPISODES / LESSONS MODAL DRAWER --}}
    <template x-teleport="body">
        <div x-show="modalOpen"
             x-cloak
             @keydown.escape.window="modalOpen = false"
             class="fixed inset-0 z-[99999] flex items-center justify-center p-3 sm:p-6">
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-slate-950/85 backdrop-blur-md" @click="modalOpen = false"></div>

            {{-- Modal Box --}}
            <div x-show="activeSession"
                 x-transition
                 class="relative z-10 w-full max-w-2xl bg-slate-900 border border-white/15 rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[85vh]">

                {{-- Modal Header --}}
                <div class="p-5 sm:p-6 bg-slate-950/80 border-b border-white/10 flex items-start justify-between gap-4">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-primary-400 block mb-1" x-text="activeSession?.module"></span>
                        <h3 class="text-xl sm:text-2xl font-black text-white leading-tight" x-text="activeSession?.title"></h3>
                        <p class="text-xs text-slate-400 mt-1">
                            <span x-text="activeSession?.completed"></span> of <span x-text="activeSession?.total"></span> lessons completed (<span x-text="activeSession?.percent"></span>%)
                        </p>
                    </div>
                    <button type="button" @click="modalOpen = false" class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 text-slate-300 flex items-center justify-center font-bold cursor-pointer">
                        ✕
                    </button>
                </div>

                {{-- Filter / Jump Input --}}
                <div class="px-5 py-3 bg-slate-900 border-b border-white/10 flex items-center gap-3">
                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text"
                           x-model="episodeFilter"
                           placeholder="Jump to lesson number (e.g. 56) or title..."
                           class="w-full bg-transparent text-sm text-white placeholder-slate-500 focus:outline-none">
                    <button x-show="episodeFilter" type="button" @click="episodeFilter = ''" class="text-xs text-slate-400 hover:text-white">Clear</button>
                </div>

                {{-- Episodes List --}}
                <div class="p-4 sm:p-5 overflow-y-auto space-y-2 flex-1">
                    <template x-for="ep in filteredEpisodes" :key="ep.id">
                        <div @click="handleEpisodeClick(ep)"
                             class="flex items-center justify-between gap-3 p-3.5 rounded-2xl border transition-all cursor-pointer"
                             :class="ep.current ? 'bg-primary-500/15 border-primary-400/50 hover:bg-primary-500/25' : 'bg-white/5 border-white/5 hover:bg-white/10 hover:border-white/15'">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center font-black text-xs shrink-0"
                                     :class="ep.completed ? 'bg-emerald-500 text-slate-950' : (ep.current ? 'bg-primary-500 text-slate-950' : 'bg-slate-800 text-slate-300')">
                                    <span x-show="ep.completed">✓</span>
                                    <span x-show="!ep.completed" x-text="ep.order"></span>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-sm font-bold text-white truncate" x-text="ep.title"></div>
                                    <div class="text-[11px] text-slate-400 flex items-center gap-2 mt-0.5">
                                        <span x-show="ep.current" class="text-primary-400 font-bold">● Up Next</span>
                                        <span x-show="ep.completed" class="text-emerald-400 font-bold">Mastered</span>
                                        <span x-show="!ep.completed && !ep.current">Interactive Lesson</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                <span x-show="ep.free_guest" class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-[10px] font-black uppercase">Free</span>
                                <span x-show="!ep.free_guest && ep.free_registered" class="px-2 py-0.5 rounded-full bg-blue-500/20 text-blue-300 border border-blue-500/30 text-[10px] font-black uppercase">Free Account</span>
                                <span x-show="!ep.accessible" class="text-amber-400 text-xs font-bold flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                                </span>
                                <span class="px-3 py-1.5 rounded-xl text-xs font-black uppercase tracking-wider"
                                      :class="ep.current ? 'bg-primary-500 text-slate-950' : 'bg-white/10 text-white'">
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
