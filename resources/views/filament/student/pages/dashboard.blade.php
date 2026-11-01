<x-filament-panels::page>
    @php
        $movieData = $this->getMovieLibraryData();
        $sessions = $movieData['sessions'] ?? collect();
        $heroSession = $movieData['heroSession'] ?? null;
        $hasHistory = $movieData['hasHistory'] ?? false;
        $sessionsModalData = $movieData['sessionsModalData'] ?? [];
        $user = auth()->user();
        $isPaid = $user->isPaid();

        $moduleNames = $sessions
            ->map(fn($s) => $s->module->name ?? 'Course')
            ->filter()
            ->unique()
            ->values();
    @endphp

    <div x-data="dashboardMovieHandler(@js($sessionsModalData))">
        <style>
            .fi-header { display: none !important; }
            .fi-header-icon { display: none !important; }
            svg { max-width: 100%; height: auto; }
            .fi-wi-stats-overview-stat-icon svg,
            .fi-wi-stats-overview-stat-description-icon svg,
            .fi-ta-icon svg,
            .fi-icon svg {
                width: 1.5rem !important;
                height: 1.5rem !important;
                max-width: 1.5rem !important;
                max-height: 1.5rem !important;
            }

            /* Hero Spotlight Layout */
            .amter-dash-hero {
                background: linear-gradient(135deg, #0f172a 0%, #1e293b 65%, #0f172a 100%);
                border-radius: 1.75rem;
                padding: 1.5rem;
                border: 2px solid rgba(56, 189, 248, 0.4);
                box-shadow: 0 20px 45px -15px rgba(15, 23, 42, 0.45);
                margin-bottom: 2rem;
                color: #ffffff;
                position: relative;
                overflow: hidden;
            }
            .amter-dash-hero-inner {
                display: flex;
                flex-direction: column;
                gap: 1.35rem;
            }
            @media (min-width: 768px) {
                .amter-dash-hero {
                    padding: 1.85rem 2rem;
                }
                .amter-dash-hero-inner {
                    flex-direction: row !important;
                    align-items: center !important;
                    gap: 2rem;
                }
            }
            .amter-dash-hero-poster {
                display: block;
                width: 100%;
                height: 180px;
                border-radius: 1.25rem;
                overflow: hidden;
                position: relative;
                flex-shrink: 0;
                background: #090d16;
                border: 1px solid rgba(255, 255, 255, 0.18);
                box-shadow: 0 12px 30px rgba(0, 0, 0, 0.45);
            }
            @media (min-width: 768px) {
                .amter-dash-hero-poster {
                    width: 290px !important;
                    height: 170px !important;
                }
            }
            .amter-dash-hero-poster picture,
            .amter-dash-hero-poster img,
            .amter-dash-card-poster picture,
            .amter-dash-card-poster img {
                width: 100% !important;
                height: 100% !important;
                object-fit: cover !important;
                display: block !important;
                transition: transform 0.45s ease;
            }
            .amter-dash-hero-poster:hover img,
            .amter-dash-card:hover .amter-dash-card-poster img {
                transform: scale(1.06);
            }

            /* Course Sessions Library Grid */
            .amter-dash-library {
                background: #0f172a;
                border-radius: 1.85rem;
                padding: 1.65rem 1.5rem;
                border: 1px solid #1e293b;
                margin-bottom: 2rem;
            }
            .amter-dash-pills {
                display: flex;
                flex-wrap: wrap;
                gap: 0.45rem;
                margin-bottom: 1.35rem;
            }
            .amter-dash-pill {
                padding: 0.4rem 0.9rem;
                border-radius: 9999px;
                font-size: 0.74rem;
                font-weight: 800;
                cursor: pointer;
                border: 1px solid rgba(255, 255, 255, 0.12);
                background: rgba(30, 41, 59, 0.8);
                color: #cbd5e1;
                transition: all 0.2s;
            }
            .amter-dash-pill:hover {
                background: rgba(51, 65, 85, 0.95);
                color: #ffffff;
            }
            .amter-dash-pill-active {
                background: linear-gradient(90deg, #00c2e8, #38bdf8) !important;
                color: #0f172a !important;
                border-color: transparent !important;
            }
            .amter-dash-grid {
                display: grid !important;
                grid-template-columns: repeat(1, minmax(0, 1fr));
                gap: 1.15rem;
            }
            @media (min-width: 540px) {
                .amter-dash-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                }
            }
            @media (min-width: 900px) {
                .amter-dash-grid {
                    grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
                }
            }
            @media (min-width: 1200px) {
                .amter-dash-grid {
                    grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
                }
            }
            .amter-dash-card {
                background: #1e293b;
                border-radius: 1.25rem;
                overflow: hidden;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                border: 1px solid rgba(255, 255, 255, 0.1);
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.35);
                transition: transform 0.25s ease, border-color 0.25s ease;
            }
            .amter-dash-card:hover {
                transform: translateY(-4px);
                border-color: rgba(56, 189, 248, 0.5);
            }
            .amter-dash-card-featured {
                border: 2px solid #00c2e8 !important;
                box-shadow: 0 12px 30px -6px rgba(0, 194, 232, 0.3);
            }
            .amter-dash-card-poster {
                display: block;
                position: relative;
                width: 100%;
                height: 150px;
                overflow: hidden;
                background: #090d16;
                text-decoration: none;
            }
        </style>

        {{-- Milestone Celebration Modal --}}
        @if(session('milestone_awarded'))
            @php $achievement = (object) session('milestone_awarded'); @endphp
            <div x-data="{ show: true }" x-show="show" class="fixed inset-0 z-[100] flex items-center justify-center pointer-events-none">
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm pointer-events-auto" @click="show = false"></div>
                <div class="relative bg-white dark:bg-slate-900 rounded-3xl p-8 max-w-sm w-full mx-4 shadow-2xl text-center pointer-events-auto"
                     style="border: 4px solid #fbbf24;">
                    <canvas id="confetti-canvas" class="absolute inset-0 pointer-events-none" style="z-index: 10;"></canvas>
                    <h2 class="text-2xl font-black text-slate-900 dark:text-white mb-2 uppercase tracking-wide pt-4">
                        {{ $achievement->title }}
                    </h2>
                    <p class="text-slate-600 dark:text-slate-300 mb-6 leading-relaxed">
                        {{ $achievement->message }}
                    </p>
                    <button @click="show = false"
                            style="background-color: #0f172a; color: #ffffff; border-radius: 0.75rem; cursor: pointer; border: none; padding: 0.75rem 1rem; width: 100%; font-weight: 700;">
                        Awesome!
                    </button>
                </div>
                <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
                <script>
                    var myCanvas = document.getElementById('confetti-canvas');
                    var myConfetti = confetti.create(myCanvas, { resize: true });
                    myConfetti({ particleCount: 150, spread: 100, origin: { y: 0.6 } });
                </script>
            </div>
        @endif

        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" style="margin-bottom: 1.5rem; background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.3); color: #065f46; padding: 1rem 1.25rem; border-radius: 1rem; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <span style="font-weight: 700; font-size: 0.9rem;">✓ {{ session('success') }}</span>
                </div>
                <button type="button" @click="show = false" style="background: transparent; border: none; color: #059669; font-weight: 800; cursor: pointer;">✕</button>
            </div>
        @endif

        {{-- Top Membership & Milestones Bar --}}
        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1.25rem;">
            <div>
                @if(!$isPaid)
                    <div style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); padding: 0.6rem 1.1rem; border-radius: 9999px; color: #92400e; display: inline-flex; align-items: center; gap: 0.75rem; border: 1px solid #fcd34d;">
                        <span style="font-weight: 800; font-size: 0.82rem;">Free Member</span>
                        <a href="{{ route('pricing') }}" style="background-color: #0f172a; color: white; padding: 0.35rem 0.9rem; border-radius: 9999px; font-weight: 800; font-size: 0.74rem; text-decoration: none;">
                            Unlock All Classes ➜
                        </a>
                    </div>
                @else
                    <div style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); padding: 0.6rem 1.1rem; border-radius: 9999px; color: #065f46; display: inline-flex; align-items: center; gap: 0.6rem; border: 1px solid #6ee7b7;">
                        <span style="font-weight: 800; font-size: 0.8rem; text-transform: uppercase;">★ Premium Member</span>
                        @if($user->subscription_expires_at)
                            <span style="font-size: 0.75rem; opacity: 0.75;">(Until {{ $user->subscription_expires_at->toFormattedDateString() }})</span>
                        @endif
                    </div>
                @endif
            </div>

            <div style="display: flex; align-items: center; gap: 0.65rem;">
                <a href="{{ route('public.library') }}"
                   style="display: inline-flex; align-items: center; gap: 0.4rem; color: #334155; font-weight: 700; font-size: 0.8rem; text-decoration: none; padding: 0.5rem 0.95rem; border-radius: 9999px; background: white; border: 1px solid #e2e8f0;">
                    <span>🎬 Full Cinema View</span>
                </a>
                <a href="{{ route('filament.student.pages.milestones') }}"
                   style="display: inline-flex; align-items: center; gap: 0.45rem; color: #334155; font-weight: 700; font-size: 0.8rem; text-decoration: none; padding: 0.5rem 0.95rem; border-radius: 9999px; background: white; border: 1px solid #e2e8f0;">
                    <svg style="width: 15px; height: 15px; color: #f59e0b;" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    <span>My Trophies</span>
                </a>
            </div>
        </div>

        {{-- 1. STREAK & MASTERY STATS --}}
        <div style="margin-bottom: 1.75rem;">
            @livewire(\App\Filament\Widgets\MasteryStats::class)
        </div>

        {{-- 2. MOVIE-PLATFORM HERO SPOTLIGHT: LAST CLASS ATTENDED --}}
        @if($heroSession)
            @php
                $heroUnit = $heroSession->resume_unit;
                $heroRawTitle = trim((string) ($heroUnit?->title ?? '1'));
                $heroLessonLabel = ctype_digit($heroRawTitle) ? "Lesson {$heroRawTitle}" : $heroRawTitle;
            @endphp
            <div class="amter-dash-hero">
                <div class="amter-dash-hero-inner">
                    {{-- Poster Thumbnail --}}
                    <a href="{{ $heroSession->target_url }}" @if($heroSession->target_onclick) onclick="{!! $heroSession->target_onclick !!}" @endif
                       class="amter-dash-hero-poster">
                        @if($heroSession->thumbnail_path)
                            <x-image :src="$heroSession->thumbnail_path" :width="400" :height="240" alt="{{ $heroSession->title }}" />
                        @else
                            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #64748b; font-weight: 900; text-transform: uppercase;">amter</div>
                        @endif
                        <div style="position: absolute; inset: 0; background: rgba(9, 13, 22, 0.28); display: flex; align-items: center; justify-content: center;">
                            <div style="width: 50px; height: 50px; border-radius: 9999px; background: #00c2e8; color: #0f172a; display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 25px rgba(0,194,232,0.5);">
                                <svg style="width: 22px; height: 22px; margin-left: 2px;" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg>
                            </div>
                        </div>
                        @if($heroSession->progress_percent > 0)
                            <div style="position: absolute; bottom: 0; left: 0; right: 0; height: 5px; background: rgba(15,23,42,0.85);">
                                <div style="height: 100%; width: {{ max(6, $heroSession->progress_percent) }}%; background: linear-gradient(90deg, #00c2e8, #10b981);"></div>
                            </div>
                        @endif
                    </a>

                    {{-- Details --}}
                    <div style="flex: 1; min-width: 0;">
                        <div style="display: inline-flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 0.6rem;">
                            <span style="background: rgba(0, 194, 232, 0.2); border: 1px solid rgba(0, 194, 232, 0.45); color: #38bdf8; font-size: 0.7rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.08em; padding: 0.25rem 0.75rem; border-radius: 9999px;">
                                {{ $hasHistory ? '● Last Class Attended' : '★ Featured Session' }}
                            </span>
                            <span style="background: rgba(255,255,255,0.1); color: #cbd5e1; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; padding: 0.25rem 0.65rem; border-radius: 9999px;">
                                {{ $heroSession->module->name ?? 'Course Session' }}
                            </span>
                        </div>

                        <h2 style="font-size: 1.65rem; font-weight: 900; color: white; margin: 0 0 0.35rem 0; line-height: 1.2;">
                            {{ $heroSession->title }}
                        </h2>

                        <p style="font-size: 0.925rem; color: #38bdf8; font-weight: 700; margin: 0 0 0.75rem 0;">
                            Up Next: <span style="color: white;">{{ $heroLessonLabel }}</span>
                            <span style="color: #94a3b8; font-weight: 600;">&bull; {{ $heroSession->completed_count }} / {{ $heroSession->units_count }} Mastered ({{ $heroSession->progress_percent }}%)</span>
                        </p>

                        {{-- Progress Bar --}}
                        <div style="max-width: 420px; height: 8px; background: rgba(255,255,255,0.12); border-radius: 9999px; overflow: hidden; margin-bottom: 1.1rem;">
                            <div style="height: 100%; width: {{ max(4, $heroSession->progress_percent) }}%; background: linear-gradient(90deg, #00c2e8, #10b981); border-radius: 9999px;"></div>
                        </div>

                        {{-- CTA Buttons --}}
                        <div style="display: flex; flex-wrap: wrap; gap: 0.75rem;">
                            <a href="{{ $heroSession->target_url }}" @if($heroSession->target_onclick) onclick="{!! $heroSession->target_onclick !!}" @endif
                               style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.6rem; background: linear-gradient(90deg, #00c2e8, #38bdf8); color: #0f172a; font-weight: 900; font-size: 0.825rem; text-transform: uppercase; letter-spacing: 0.05em; border-radius: 9999px; text-decoration: none; box-shadow: 0 8px 20px rgba(0, 194, 232, 0.35);">
                                <svg style="width: 15px; height: 15px;" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg>
                                <span>{{ $heroSession->cta_label }}</span>
                            </a>

                            <button type="button"
                                    @click="openSessionModal({{ $heroSession->id }})"
                                    style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.25rem; background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.2); font-weight: 800; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; border-radius: 9999px; cursor: pointer;">
                                <span>☰ All {{ $heroSession->units_count }} Lessons</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- 3. MOVIE-PLATFORM COURSE SESSIONS GRID --}}
        <div class="amter-dash-library">
            <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid rgba(255,255,255,0.08);">
                <div>
                    <span style="font-size: 0.7rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.1em; color: #38bdf8; display: block;">Your Course Library</span>
                    <h2 style="font-size: 1.45rem; font-weight: 900; color: white; margin: 0.15rem 0 0 0;">All Course Sessions</h2>
                </div>

                {{-- Search Input --}}
                <div style="position: relative; min-width: 210px;">
                    <input type="text"
                           x-model="searchQuery"
                           placeholder="Search sessions..."
                           style="width: 100%; padding: 0.45rem 0.85rem; border-radius: 9999px; background: rgba(30, 41, 59, 0.9); border: 1px solid rgba(255,255,255,0.15); color: #ffffff; font-size: 0.8rem; outline: none;">
                </div>
            </div>

            {{-- Filter Pills --}}
            <div class="amter-dash-pills">
                <button type="button" @click="selectedCategory = 'all'"
                        class="amter-dash-pill"
                        :class="selectedCategory === 'all' ? 'amter-dash-pill-active' : ''">
                    All Sessions ({{ $sessions->count() }})
                </button>
                <button type="button" @click="selectedCategory = 'started'"
                        class="amter-dash-pill"
                        :class="selectedCategory === 'started' ? 'amter-dash-pill-active' : ''">
                    ▶ In Progress
                </button>
                @foreach($moduleNames as $modName)
                    <button type="button" @click="selectedCategory = @js($modName)"
                            class="amter-dash-pill"
                            :class="selectedCategory === @js($modName) ? 'amter-dash-pill-active' : ''">
                        {{ $modName }}
                    </button>
                @endforeach
            </div>

            {{-- Responsive 4-Column Card Grid --}}
            <div class="amter-dash-grid">
                @foreach($sessions as $session)
                    @php
                        $resumeUnit = $session->resume_unit;
                        $resumeRawTitle = trim((string) ($resumeUnit?->title ?? '1'));
                        $resumeLessonText = ctype_digit($resumeRawTitle) ? "Lesson {$resumeRawTitle}" : $resumeRawTitle;
                        $modTitle = $session->module->name ?? 'Course';
                    @endphp
                    <div class="amter-dash-card {{ $session->is_last_attended ? 'amter-dash-card-featured' : '' }}"
                         x-show="matchesFilter(@js($session->title), @js($modTitle), {{ $session->is_started ? 'true' : 'false' }})">
                        {{-- Poster --}}
                        <a href="{{ $session->target_url }}" @if($session->target_onclick) onclick="{!! $session->target_onclick !!}" @endif
                           class="amter-dash-card-poster">
                            @if($session->thumbnail_path)
                                <x-image :src="$session->thumbnail_path" :width="360" :height="200" alt="{{ $session->title }}" />
                            @else
                                <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #475569; font-weight: 900; text-transform: uppercase;">amter</div>
                            @endif
                            <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(15,23,42,0.95), rgba(15,23,42,0.2), transparent);"></div>

                            @if($session->is_last_attended)
                                <div style="position: absolute; top: 0.6rem; left: 0.6rem; background: #00c2e8; color: #0f172a; font-size: 0.62rem; font-weight: 900; text-transform: uppercase; padding: 0.2rem 0.6rem; border-radius: 9999px;">
                                    ● Last Attended
                                </div>
                            @endif

                            <div style="position: absolute; bottom: 0.55rem; left: 0.6rem; right: 0.6rem; display: flex; justify-content: space-between; align-items: center; font-size: 0.7rem; font-weight: 800; color: white;">
                                <span style="background: rgba(9, 13, 22, 0.65); padding: 0.15rem 0.45rem; border-radius: 0.35rem;">{{ $session->is_started ? "Next: {$resumeLessonText}" : "{$session->units_count} Lessons" }}</span>
                                @if($session->completed_count > 0)
                                    <span style="color: #34d399; background: rgba(6, 78, 59, 0.85); padding: 0.12rem 0.45rem; border-radius: 0.35rem;">{{ $session->completed_count }}/{{ $session->units_count }}</span>
                                @endif
                            </div>

                            @if($session->is_started)
                                <div style="position: absolute; bottom: 0; left: 0; right: 0; height: 4px; background: #0f172a;">
                                    <div style="height: 100%; width: {{ max(6, $session->progress_percent) }}%; background: linear-gradient(90deg, #00c2e8, #10b981);"></div>
                                </div>
                            @endif
                        </a>

                        {{-- Body --}}
                        <div style="padding: 0.95rem; display: flex; flex-direction: column; flex-grow: 1; justify-content: space-between; gap: 0.75rem;">
                            <div>
                                <div style="font-size: 0.65rem; font-weight: 800; color: #38bdf8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.2rem;">
                                    {{ $modTitle }}
                                </div>
                                @if(!$session->is_hidden)
                                    <a href="{{ $session->target_url }}" @if($session->target_onclick) onclick="{!! $session->target_onclick !!}" @endif style="text-decoration: none;">
                                        <h3 style="font-size: 0.95rem; font-weight: 800; color: white; margin: 0; line-height: 1.3;">
                                            {{ $session->title }}
                                        </h3>
                                    </a>
                                @endif
                                @if($session->description)
                                    <p style="font-size: 0.73rem; color: #94a3b8; margin: {{ $session->is_hidden ? '0' : '0.25rem 0 0 0' }}; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                        {{ $session->description }}
                                    </p>
                                @endif
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 0.45rem;">
                                <a href="{{ $session->target_url }}" @if($session->target_onclick) onclick="{!! $session->target_onclick !!}" @endif
                                   style="display: inline-flex; justify-content: center; align-items: center; gap: 0.4rem; width: 100%; padding: 0.55rem 0.75rem; background: linear-gradient(90deg, #00c2e8, #38bdf8); color: #0f172a; border-radius: 0.75rem; font-size: 0.72rem; font-weight: 900; text-transform: uppercase; text-decoration: none;">
                                    <span>▶ {{ $session->cta_label }}</span>
                                </a>

                                <button type="button"
                                        @click="openSessionModal({{ $session->id }})"
                                        style="display: inline-flex; justify-content: center; align-items: center; gap: 0.35rem; width: 100%; padding: 0.45rem 0.75rem; background: rgba(255,255,255,0.08); color: #e2e8f0; border: 1px solid rgba(255,255,255,0.12); border-radius: 0.75rem; font-size: 0.68rem; font-weight: 800; text-transform: uppercase; cursor: pointer;">
                                    <span>☰ All {{ $session->units_count }} Lessons</span>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Review Prompt --}}
        <div style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 1.5rem 2rem; border-radius: 1.5rem; color: white; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem;">
            <div>
                <h3 style="font-size: 1.15rem; font-weight: 800; margin: 0; color: white;">Enjoying Amter English?</h3>
                <p style="font-size: 0.85rem; opacity: 0.9; margin: 0.2rem 0 0 0; color: white;">Your feedback helps other Malayalis find their path to fluency!</p>
            </div>
            <a href="{{ url('/?review=1#reviews') }}" style="background-color: white; color: #4f46e5; padding: 0.65rem 1.4rem; border-radius: 9999px; font-weight: 800; font-size: 0.825rem; text-decoration: none;">
                Write a Quick Review ➜
            </a>
        </div>

        {{-- Episodes Modal Drawer --}}
        <template x-teleport="body">
            <div x-show="modalOpen"
                 x-cloak
                 @keydown.escape.window="modalOpen = false"
                 style="position: fixed; inset: 0; z-index: 999999; display: flex; align-items: center; justify-content: center; padding: 1rem;">
                <div style="position: fixed; inset: 0; background: rgba(10, 15, 29, 0.85); backdrop-filter: blur(12px);" @click="modalOpen = false"></div>

                <div x-show="activeSession"
                     style="position: relative; z-index: 10; width: 100%; max-width: 40rem; background: #0f172a; border: 1px solid rgba(255,255,255,0.15); border-radius: 1.5rem; overflow: hidden; display: flex; flex-direction: column; max-height: 82vh; color: white; box-shadow: 0 25px 60px rgba(0,0,0,0.8);">
                    <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1); display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem;">
                        <div>
                            <div style="font-size: 0.7rem; font-weight: 900; text-transform: uppercase; color: #38bdf8;" x-text="activeSession?.module"></div>
                            <h3 style="font-size: 1.25rem; font-weight: 900; color: white; margin: 0.15rem 0;" x-text="activeSession?.title"></h3>
                            <div style="font-size: 0.75rem; color: #94a3b8;">
                                <span x-text="activeSession?.completed"></span> / <span x-text="activeSession?.total"></span> lessons completed (<span x-text="activeSession?.percent"></span>%)
                            </div>
                        </div>
                        <button type="button" @click="modalOpen = false" style="background: rgba(255,255,255,0.1); border: none; color: white; width: 32px; height: 32px; border-radius: 9999px; cursor: pointer; font-weight: 800;">✕</button>
                    </div>

                    <div style="padding: 0.75rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.08); background: rgba(30,41,59,0.5);">
                        <input type="text"
                               x-model="episodeFilter"
                               placeholder="Jump to lesson number (e.g. 56) or title..."
                               style="width: 100%; background: transparent; border: none; outline: none; color: white; font-size: 0.9rem;">
                    </div>

                    <div style="padding: 1rem; overflow-y: auto; flex: 1; display: flex; flex-direction: column; gap: 0.5rem;">
                        <template x-for="ep in filteredEpisodes" :key="ep.id">
                            <a :href="ep.url"
                               style="display: flex; align-items: center; justify-content: space-between; padding: 0.8rem 1rem; border-radius: 1rem; text-decoration: none; color: white; border: 1px solid rgba(255,255,255,0.08); background: rgba(255,255,255,0.04);">
                                <div style="display: flex; align-items: center; gap: 0.75rem; min-width: 0;">
                                    <div style="width: 32px; height: 32px; border-radius: 0.6rem; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 0.75rem; flex-shrink: 0;"
                                         :style="ep.completed ? 'background:#10b981;color:#0f172a;' : (ep.current ? 'background:#00c2e8;color:#0f172a;' : 'background:#1e293b;color:#cbd5e1;')">
                                        <span x-show="ep.completed">✓</span>
                                        <span x-show="!ep.completed" x-text="ep.order"></span>
                                    </div>
                                    <div style="min-width: 0;">
                                        <div style="font-size: 0.9rem; font-weight: 800; color: white; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" x-text="ep.title"></div>
                                        <div style="font-size: 0.7rem; color: #94a3b8;">
                                            <span x-show="ep.current" style="color: #38bdf8; font-weight: 800;">● Current Lesson</span>
                                            <span x-show="ep.completed" style="color: #34d399; font-weight: 800;">Mastered</span>
                                            <span x-show="!ep.completed && !ep.current">Interactive Lesson</span>
                                        </div>
                                    </div>
                                </div>
                                <span style="padding: 0.35rem 0.75rem; border-radius: 0.6rem; font-size: 0.7rem; font-weight: 900; text-transform: uppercase; background: #00c2e8; color: #0f172a;">
                                    Play ▶
                                </span>
                            </a>
                        </template>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <script>
        function dashboardMovieHandler(sessionsData) {
            return {
                sessions: sessionsData || {},
                modalOpen: false,
                activeSession: null,
                episodeFilter: '',
                selectedCategory: 'all',
                searchQuery: '',

                matchesFilter(title, modName, isStarted) {
                    if (this.selectedCategory === 'started' && !isStarted) {
                        return false;
                    }
                    if (this.selectedCategory !== 'all' && this.selectedCategory !== 'started' && modName !== this.selectedCategory) {
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
                }
            };
        }
    </script>
</x-filament-panels::page>
