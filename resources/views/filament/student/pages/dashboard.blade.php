<x-filament-panels::page>
    {{-- WRAPPER DIV for Livewire --}}
    <div>
        <style>
            /* Hide Default Header Elements */
            .fi-header { display: none !important; }
            .fi-header-icon { display: none !important; }
            
            /* Global SVG Constraints */
            svg { max-width: 100%; height: auto; }
            
            /* Aggressive Constraints for any remaining Widget Icons */
            .fi-wi-stats-overview-stat-icon svg,
            .fi-wi-stats-overview-stat-description-icon svg,
            .fi-ta-icon svg, 
            .fi-icon svg {
                width: 1.5rem !important;
                height: 1.5rem !important;
                max-width: 1.5rem !important;
                max-height: 1.5rem !important;
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
                            style="background-color: #0f172a; color: #ffffff; border-radius: 0.75rem; cursor: pointer; border: none; padding: 0.75rem 1rem; width: 100%; font-weight: 700; transition: background-color 0.2s;">
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
            <div x-data="{ show: true }" x-show="show" class="mb-6 bg-emerald-500/10 border border-emerald-500/20 text-emerald-800 dark:text-emerald-300 px-6 py-4 rounded-2xl flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="font-semibold text-sm">{{ session('success') }}</span>
                </div>
                <button type="button" @click="show = false" class="text-emerald-500 hover:text-emerald-700 font-bold ml-4">✕</button>
            </div>
        @endif

        {{-- Custom Hero/Header Section --}}
        <div style="display: flex; flex-direction: column; gap: 1.5rem; justify-content: space-between; align-items: flex-end; margin-bottom: 2.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid #f1f5f9;">
            {{-- Title Section --}}
            <div style="width: 100%; display: flex; flex-direction: column; gap: 0.5rem;">
                <h1 style="font-size: 1.875rem; line-height: 2.25rem; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -0.02em;">
                    Dashboard
                </h1>
                <p style="font-size: 1.125rem; line-height: 1.75rem; color: #64748b; font-weight: 500; margin: 0;">
                    Track your progress and continue learning the Master Track.
                </p>
            </div>

            {{-- Actions Section --}}
            <div style="display: flex; flex-direction: column; gap: 1rem; width: 100%; align-items: center;">
                <style>
                    @media (min-width: 768px) {
                        .dashboard-header-container { flex-direction: row !important; align-items: flex-end !important; }
                        .dashboard-actions-container { flex-direction: row !important; width: auto !important; }
                        .dashboard-search-container { width: 22rem !important; }
                    }
                </style>
                
                {{-- Search Box --}}
                <div class="dashboard-search-container" style="position: relative; width: 100%; display: flex; align-items: center; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 9999px; padding: 0.625rem 1rem; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05); transition: border-color 0.2s;">
                    <svg style="width: 18px !important; height: 18px !important; color: #6366f1; margin-right: 0.75rem; flex-shrink: 0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Search title, unit, or step..." 
                        style="background-color: transparent; border: none; padding: 0; width: 100%; font-size: 0.875rem; font-weight: 500; color: #334155; outline: none;"
                    >
                    @if($this->search)
                        <button type="button" wire:click="clearSearch" style="background: transparent; border: none; color: #94a3b8; font-weight: 700; cursor: pointer; padding: 0 4px; font-size: 0.9rem;">
                            ✕
                        </button>
                    @endif
                </div>

                {{-- Resume Button --}}
                @if($resumeUrl = $this->getResumeUrl())
                    <a href="{{ $resumeUrl }}" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 2rem; background: linear-gradient(to right, #f59e0b, #f97316); color: white; font-weight: 700; font-size: 0.875rem; border-radius: 9999px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); text-decoration: none; width: 100%; justify-content: center; white-space: nowrap; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                         <svg style="width: 20px !important; height: 20px !important;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; position: relative; z-index: 10;">Resume Learning</span>
                    </a>
                @endif
            </div>

            {{-- Script to apply responsive classes manually --}}
            <script>
                document.currentScript.parentElement.classList.add('dashboard-header-container');
                document.currentScript.parentElement.querySelector('div:nth-child(2)').classList.add('dashboard-actions-container');
            </script>
        </div>

        {{-- LIVE SEARCH RESULTS (When user is searching) --}}
        @if(!empty(trim($this->search)))
            @php
                $searchResults = $this->getSearchResults();
                $completedUnitIds = auth()->user()->completedUnits()->pluck('units.id')->flip()->toArray();
            @endphp
            <div style="background-color: white; border-radius: 1.5rem; padding: 2rem; border: 1px solid #e2e8f0; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); margin-bottom: 2.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; padding-bottom: 0.75rem; border-bottom: 1px solid #f1f5f9;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span style="font-weight: 800; font-size: 1.125rem; color: #0f172a;">Search Results</span>
                        <span style="background: #eef2ff; color: #4338ca; font-size: 0.75rem; font-weight: 800; padding: 0.2rem 0.6rem; border-radius: 9999px;">{{ $searchResults->count() }} found</span>
                    </div>
                    <button type="button" wire:click="clearSearch" style="background: none; border: none; color: #64748b; font-size: 0.85rem; font-weight: 700; cursor: pointer;">
                        Clear Search ✕
                    </button>
                </div>

                @if($searchResults->isNotEmpty())
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($searchResults as $step)
                            @php
                                $unit = $step->unit;
                                $isCompleted = isset($completedUnitIds[$step->unit_id]);
                                $stepUrl = route('student.units.show', ['unit' => $step->unit_id, 'track_id' => $step->learning_track_id]);
                            @endphp
                            <a href="{{ $stepUrl }}" 
                               style="display: flex; align-items: center; justify-content: space-between; padding: 1rem; border-radius: 1rem; border: 1px solid #f1f5f9; background: #fafafa; text-decoration: none; transition: all 0.2s;"
                               onmouseover="this.style.background='#f8fafc'; this.style.borderColor='#cbd5e1'; this.style.transform='translateY(-1px)';"
                               onmouseout="this.style.background='#fafafa'; this.style.borderColor='#f1f5f9'; this.style.transform='translateY(0)';">
                                <div style="display: flex; align-items: center; gap: 0.75rem; min-width: 0;">
                                    <div style="width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 0.75rem; flex-shrink: 0; {{ $isCompleted ? 'background: #d1fae5; color: #059669;' : 'background: #e0e7ff; color: #4338ca;' }}">
                                        {{ $isCompleted ? '✓' : $step->step_number }}
                                    </div>
                                    <div style="min-width: 0;">
                                        <div style="font-size: 0.7rem; font-weight: 800; color: #6366f1; text-transform: uppercase; letter-spacing: 0.05em;">
                                            {{ $unit?->courseSession?->title ?? 'Master Track' }}
                                        </div>
                                        <div style="font-size: 0.925rem; font-weight: 800; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            {{ $step->title_override ?: ($unit?->title ?? 'Untitled Lesson') }}
                                        </div>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0; margin-left: 0.75rem;">
                                    @if($isCompleted)
                                        <span style="font-size: 0.7rem; font-weight: 800; color: #059669; background: #ecfdf5; padding: 0.2rem 0.5rem; border-radius: 9999px;">Mastered</span>
                                    @else
                                        <span style="font-size: 0.7rem; font-weight: 800; color: #4338ca; background: #eef2ff; padding: 0.2rem 0.5rem; border-radius: 9999px;">Start ➔</span>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div style="text-align: center; padding: 2.5rem 1rem; color: #94a3b8;">
                        <p style="font-weight: 700; font-size: 1rem; margin-bottom: 0.25rem;">No lessons match "{{ $this->search }}"</p>
                        <p style="font-size: 0.85rem;">Try searching by title keyword, session topic, or step number (e.g. "Day 1", "Grammar", "5").</p>
                    </div>
                @endif
            </div>
        @endif

        {{-- Membership Status Banner --}}
        @php
            $user = auth()->user();
            $isPaid = $user->isPaid();
        @endphp

        <div style="margin-bottom: 2rem;">
            @if(!$isPaid)
                <div style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); padding: 1.25rem 1.5rem; border-radius: 1.25rem; color: #92400e; display: flex; align-items: center; justify-content: space-between; gap: 1rem; border: 1px solid #fcd34d; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="background-color: #fcd34d; color: #92400e; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <p style="font-weight: 800; margin: 0; font-size: 1rem; letter-spacing: -0.01em;">Registered Account (Free)</p>
                            <p style="font-weight: 500; margin: 0; font-size: 0.875rem; opacity: 0.8;">Upgrade to Premium to unlock all 500+ interactive lessons and AI Tutor.</p>
                        </div>
                    </div>
                    <a href="{{ route('pricing') }}" style="background-color: #0f172a; color: white; padding: 0.75rem 1.5rem; border-radius: 9999px; font-weight: 800; font-size: 0.875rem; text-decoration: none; white-space: nowrap; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                        Unlock Premium ➜
                    </a>
                </div>
            @else
                <div style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); padding: 1rem 1.25rem; border-radius: 1.25rem; color: #065f46; display: inline-flex; align-items: center; gap: 0.75rem; border: 1px solid #6ee7b7; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.1);">
                    <div style="background-color: #10b981; color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <svg style="width: 18px; height: 18px;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                    </div>
                    <div>
                        <span style="font-weight: 800; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.05em;">Premium Member</span>
                        @if($user->subscription_expires_at)
                            <span style="font-size: 0.75rem; font-weight: 500; opacity: 0.7; margin-left: 0.5rem;">(Active until {{ $user->subscription_expires_at->toFormattedDateString() }})</span>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <div class="space-y-8">
            {{-- 1. MASTERY STATS (STREAK) --}}
            <div>
                @livewire(\App\Filament\Widgets\MasteryStats::class)
            </div>

            {{-- 2. MASTER TRACK PROGRESSION CARD (Replaces old Course Library CTA) --}}
            @php
                $trackStats = $this->getTrackStats();
                $track = $trackStats['track'];
                $currentStep = $trackStats['currentStep'];
            @endphp

            @if($track)
                <div style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border-radius: 2rem; padding: 2.5rem 2rem; border: 1px solid #e2e8f0; box-shadow: 0 15px 35px -5px rgba(0, 0, 0, 0.06); position: relative; overflow: hidden;">
                    {{-- Decorative top glow --}}
                    <div style="position: absolute; top: 0; left: 0; right: 0; height: 5px; background: linear-gradient(90deg, #f59e0b, #6366f1, #a855f7);"></div>

                    <div style="display: flex; flex-direction: column; gap: 1.75rem;">
                        {{-- Header Row --}}
                        <div style="display: flex; flex-direction: column; md-flex-direction: row; justify-content: space-between; align-items: flex-start; gap: 1rem;">
                            <div>
                                <div style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.3rem 0.85rem; border-radius: 9999px; background: rgba(99, 102, 241, 0.1); color: #4f46e5; font-weight: 800; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 0.5rem;">
                                    <span style="width: 6px; height: 6px; border-radius: 9999px; background: #4f46e5;"></span>
                                    Primary Learning Path
                                </div>
                                <h2 style="font-size: 1.75rem; font-weight: 900; color: #0f172a; margin: 0; letter-spacing: -0.02em;">
                                    {{ $track->title }}
                                </h2>
                                <p style="color: #64748b; font-size: 0.95rem; margin-top: 0.25rem; font-weight: 500;">
                                    Step-by-step interactive lessons built specifically for Malayalis.
                                </p>
                            </div>

                            <div style="display: flex; align-items: center; gap: 0.5rem; background: #f1f5f9; padding: 0.5rem 1rem; border-radius: 9999px;">
                                <span style="font-size: 0.85rem; font-weight: 800; color: #334155;">{{ $trackStats['percent'] }}% Complete</span>
                                <span style="font-size: 0.75rem; color: #64748b;">({{ $trackStats['completedSteps'] }} / {{ $trackStats['totalSteps'] }} Steps)</span>
                            </div>
                        </div>

                        {{-- Progress Bar --}}
                        <div style="width: 100%; height: 10px; background: #e2e8f0; border-radius: 9999px; overflow: hidden;">
                            <div style="height: 100%; width: {{ $trackStats['percent'] }}%; background: linear-gradient(90deg, #f59e0b, #6366f1); border-radius: 9999px; transition: width 0.5s cubic-bezier(0.4, 0, 0.2, 1);"></div>
                        </div>

                        {{-- Current Lesson Spotlight Card --}}
                        <div style="background: white; border-radius: 1.25rem; padding: 1.5rem; border: 1px solid #e2e8f0; display: flex; flex-direction: column; md-flex-direction: row; justify-content: space-between; align-items: center; gap: 1.25rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03);">
                            <div style="display: flex; align-items: center; gap: 1rem; width: 100%;">
                                <div style="width: 48px; height: 48px; background: #eef2ff; color: #4f46e5; border-radius: 1rem; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1.1rem; flex-shrink: 0;">
                                    {{ $currentStep ? $currentStep->step_number : '✓' }}
                                </div>
                                <div style="min-width: 0;">
                                    <div style="font-size: 0.75rem; font-weight: 800; color: #6366f1; text-transform: uppercase; letter-spacing: 0.06em;">
                                        @if($currentStep)
                                            Current Lesson &bull; {{ $currentStep->unit?->courseSession?->title ?? 'Amter Module' }}
                                        @else
                                            Track Mastered!
                                        @endif
                                    </div>
                                    <div style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin-top: 0.15rem;">
                                        @if($currentStep)
                                            {{ $currentStep->title_override ?: ($currentStep->unit?->title ?? 'Next Step') }}
                                        @else
                                            You have completed all lessons in this Master Track!
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div style="display: flex; gap: 0.75rem; width: 100%; md-width: auto; justify-content: flex-end;">
                                @if($resumeUrl = $trackStats['resumeUrl'])
                                    <a href="{{ $resumeUrl }}" 
                                       style="display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.85rem 2rem; background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: white; font-weight: 800; font-size: 0.95rem; border-radius: 9999px; text-decoration: none; transition: all 0.2s; box-shadow: 0 10px 20px -5px rgba(15, 23, 42, 0.3); white-space: nowrap; width: 100%; md-width: auto;"
                                       onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 15px 25px -5px rgba(15, 23, 42, 0.4)';"
                                       onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 20px -5px rgba(15, 23, 42, 0.3)';">
                                        <span>Continue Step {{ $currentStep ? $currentStep->step_number : 1 }}</span>
                                        <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                                    </a>
                                @endif
                            </div>
                        </div>

                        {{-- Footer Links: Milestones & Admin Library link --}}
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; pt-2">
                            <a href="{{ route('filament.student.pages.milestones') }}" 
                               style="display: inline-flex; align-items: center; gap: 0.5rem; color: #475569; font-weight: 700; font-size: 0.875rem; text-decoration: none; padding: 0.5rem 1rem; border-radius: 9999px; background: white; border: 1px solid #e2e8f0; transition: all 0.2s;"
                               onmouseover="this.style.background='#f8fafc'; this.style.borderColor='#cbd5e1';"
                               onmouseout="this.style.background='white'; this.style.borderColor='#e2e8f0';">
                                <svg style="width: 16px; height: 16px; color: #fbbf24;" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                <span>View My Milestones & Trophies</span>
                            </a>

                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('filament.student.pages.library') }}" 
                                   style="display: inline-flex; align-items: center; gap: 0.4rem; color: #6366f1; font-weight: 700; font-size: 0.8rem; text-decoration: none; padding: 0.35rem 0.75rem; border-radius: 9999px; background: #eef2ff;">
                                    <span>Library (Admin View) ➔</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- Review Prompt --}}
            <div style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 1.5rem 2rem; border-radius: 1.5rem; color: white; display: flex; flex-direction: column; md-flex-direction: row; align-items: center; justify-content: space-between; gap: 1rem; box-shadow: 0 10px 25px -5px rgba(79, 70, 229, 0.4);">
                <div style="text-align: center; md-text-align: left;">
                    <h3 style="font-size: 1.25rem; font-weight: 800; margin: 0; color: white;">Enjoying Amter English?</h3>
                    <p style="font-size: 0.875rem; opacity: 0.9; margin: 0.25rem 0 0 0; color: white;">Your feedback helps other Malayalis find their path to fluency!</p>
                </div>
                <a href="{{ url('/?review=1#reviews') }}" style="background-color: white; color: #4f46e5; padding: 0.75rem 1.5rem; border-radius: 9999px; font-weight: 800; font-size: 0.875rem; text-decoration: none; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                    Write a Quick Review ➜
                </a>
            </div>
        </div>
    </div>
</x-filament-panels::page>
