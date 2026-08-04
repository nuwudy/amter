<x-filament-panels::page>
    {{-- WRAPPER DIV for Livewire --}}
    <div>
        <style>
            /* Hide Default Header Elements */
            .fi-header { display: none !important; }
            .fi-header-icon { display: none !important; }
            
            /* Global SVG Constraints */
            svg { max-width: 100%; height: auto; }
            
            /* Aggressive Constraints for any remaining Widget Icons (e.g. from Filament itself) */
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

        {{-- Milestone Celebration Modal (Text Only, No Giant Icons) --}}
        @if(session('milestone_awarded'))
            @php $achievement = (object) session('milestone_awarded'); @endphp
            <div x-data="{ show: true }" x-show="show" class="fixed inset-0 z-[100] flex items-center justify-center pointer-events-none">
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm pointer-events-auto" @click="show = false"></div>
                <div class="relative bg-white dark:bg-slate-900 rounded-3xl p-8 max-w-sm w-full mx-4 shadow-2xl text-center pointer-events-auto"
                     style="border: 4px solid #fbbf24;">
                    <canvas id="confetti-canvas" class="absolute inset-0 pointer-events-none" style="z-index: 10;"></canvas>

                    {{-- REMOVED ICON SECTION --}}
                    
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

        {{-- Custom Hero/Header Section (Inline Styled) --}}
        <div style="display: flex; flex-direction: column; gap: 1.5rem; justify-content: space-between; align-items: flex-end; margin-bottom: 2.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid #f1f5f9;">
            {{-- Title Section --}}
            <div style="width: 100%; display: flex; flex-direction: column; gap: 0.5rem;">
                <h1 style="font-size: 1.875rem; line-height: 2.25rem; font-weight: 700; color: #0f172a; margin: 0;">
                    Dashboard
                </h1>
                <p style="font-size: 1.125rem; line-height: 1.75rem; color: #64748b; font-weight: 500; margin: 0;">
                    Track your progress and continue learning.
                </p>
            </div>

            {{-- Actions Section --}}
            <div style="display: flex; flex-direction: column; gap: 1rem; width: 100%; align-items: center;">
                {{-- Responsive Spacer for desktop row layout --}}
                <style>
                    @media (min-width: 768px) {
                        .dashboard-header-container { flex-direction: row !important; align-items: flex-end !important; }
                        .dashboard-actions-container { flex-direction: row !important; width: auto !important; }
                        .dashboard-search-container { width: 20rem !important; }
                    }
                </style>
                
                {{-- Search Box --}}
                <div class="dashboard-search-container" style="position: relative; width: 100%; display: flex; align-items: center; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 9999px; padding: 0.625rem 1rem; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);">
                    <svg style="width: 20px !important; height: 20px !important; color: #94a3b8; margin-right: 0.75rem; flex-shrink: 0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input 
                        type="text" 
                        wire:model.live.debounce.500ms="search" 
                        placeholder="Search courses..." 
                        style="background-color: transparent; border: none; padding: 0; width: 100%; font-size: 0.875rem; font-weight: 500; color: #334155; outline: none;"
                    >
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
            {{-- 1. MASTERY STATS (STREAK) - Widget code handles its own icons, but we stripped them in the class --}}
            <div>
                @livewire(\App\Filament\Widgets\MasteryStats::class)
            </div>

            {{-- Review Prompt --}}
            <div style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 1.5rem; border-radius: 1.5rem; color: white; display: flex; flex-direction: column; md-flex-direction: row; align-items: center; justify-content: space-between; gap: 1rem; box-shadow: 0 10px 25px -5px rgba(79, 70, 229, 0.4);">
                <div style="text-align: center; md-text-align: left;">
                    <h3 style="font-size: 1.25rem; font-weight: 800; margin: 0; color: white;">Enjoying Amter English?</h3>
                    <p style="font-size: 0.875rem; opacity: 0.9; margin: 0.25rem 0 0 0; color: white;">Your feedback helps other Malayalis find their path to fluency!</p>
                </div>
                <a href="{{ url('/?review=1#reviews') }}" style="background-color: white; color: #4f46e5; padding: 0.75rem 1.5rem; border-radius: 9999px; font-weight: 800; font-size: 0.875rem; text-decoration: none; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                    Write a Quick Review ➜
                </a>
            </div>

            {{-- 2. COURSE CATALOG GRID --}}
            {{-- 2. COURSE LIBRARY CTA --}}
            <div style="background-color: white; border-radius: 1.5rem; padding: 3rem 2rem; text-align: center; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); display: flex; flex-direction: column; align-items: center;">
                <div style="width: 72px; height: 72px; background-color: #f0f9ff; color: #0ea5e9; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
                    <svg style="width: 36px; height: 36px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                </div>
                
                <h2 style="font-size: 1.75rem; font-weight: 800; color: #0f172a; margin-bottom: 0.75rem;">Continue Your Journey</h2>
                <p style="color: #64748b; font-size: 1.1rem; margin-bottom: 2.5rem; max-width: 400px;">Access all your courses, lessons, and practice materials in one place.</p>
                
                <div style="display: flex; gap: 1rem; flex-wrap: wrap; justify-content: center;">
                    <a href="{{ route('filament.student.pages.library') }}" 
                       style="display: inline-flex; align-items: center; gap: 0.75rem; padding: 1rem 2.5rem; background-color: #0f172a; color: white; font-weight: 700; font-size: 1.125rem; border-radius: 9999px; text-decoration: none; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 10px 20px -5px rgba(15, 23, 42, 0.3);"
                       onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 20px 25px -5px rgba(15, 23, 42, 0.4)'" 
                       onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 20px -5px rgba(15, 23, 42, 0.3)'">
                        Go to Course Library
                        <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                    </a>

                    <a href="{{ route('filament.student.pages.milestones') }}" 
                       style="display: inline-flex; align-items: center; gap: 0.75rem; padding: 1rem 2.5rem; background-color: white; color: #0f172a; border: 2px solid #e2e8f0; font-weight: 700; font-size: 1.125rem; border-radius: 9999px; text-decoration: none; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);"
                       onmouseover="this.style.transform='translateY(-3px)'; this.style.borderColor='#cbd5e1'; this.style.backgroundColor='#f8fafc';" 
                       onmouseout="this.style.transform='translateY(0)'; this.style.borderColor='#e2e8f0'; this.style.backgroundColor='white';">
                        <svg style="width: 20px; height: 20px; color: #fbbf24;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                        View Milestones
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
