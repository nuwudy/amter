<x-filament-panels::page>
    {{-- WRAPPER DIV for Livewire --}}
    <div>
        <style>
            /* Hide Default Header Elements */
            .fi-header { display: none !important; }
            .fi-header-icon { display: none !important; }
            
            /* Global SVG Constraints */
            svg { max-width: 100%; height: auto; }

            /* NUCLEAR OPTION: Hide any SVG that appears in the top header area */
            .fi-topbar svg, .fi-header svg { 
                display: none !important; 
                width: 0 !important; 
                height: 0 !important; 
            }
        </style>

        {{-- Custom Hero/Header Section (Inline Styled) --}}
        <div style="display: flex; flex-direction: column; gap: 1.5rem; justify-content: space-between; align-items: flex-end; margin-bottom: 2.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid #f1f5f9;">
            {{-- Title Section --}}
            <div style="width: 100%; display: flex; flex-direction: column; gap: 0.5rem;">
                <h1 style="font-size: 1.875rem; line-height: 2.25rem; font-weight: 700; color: #0f172a; margin: 0;">
                    My Course Library
                </h1>
                <p style="font-size: 1.125rem; line-height: 1.75rem; color: #64748b; font-weight: 500; margin: 0;">
                    Explore, learn, and master new skills.
                </p>
            </div>

            {{-- Actions Section --}}
            <div style="display: flex; flex-direction: column; gap: 1rem; width: 100%; align-items: center;">
                {{-- Responsive Spacer for desktop row layout --}}
                <style>
                    @media (min-width: 768px) {
                        .library-header-container { flex-direction: row !important; align-items: flex-end !important; }
                        .library-actions-container { flex-direction: row !important; width: auto !important; }
                        .library-search-container { width: 20rem !important; }
                    }
                </style>
                
                {{-- Search Box --}}
                <div class="library-search-container" style="position: relative; width: 100%; display: flex; align-items: center; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 9999px; padding: 0.625rem 1rem; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);">
                    <svg style="width: 20px !important; height: 20px !important; color: #94a3b8; margin-right: 0.75rem; flex-shrink: 0;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input 
                        type="text" 
                        wire:model.live.debounce.500ms="search" 
                        placeholder="Search your library..." 
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

            {{-- Script to apply responsive classes manually since media queries in style tags can be finicky in blade components --}}
            <script>
                document.currentScript.parentElement.classList.add('library-header-container');
                document.currentScript.parentElement.querySelector('div:nth-child(2)').classList.add('library-actions-container');
            </script>
        </div>

        {{-- Grid Section --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8"
             style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 2rem;">
            @forelse($this->getSessions() as $session)
                <x-library-card :record="$session" />
            @empty
                <div class="col-span-full text-center py-20 bg-slate-50 dark:bg-slate-900 rounded-3xl border border-dashed border-slate-200 dark:border-slate-700">
                    <h3 class="text-lg font-medium text-slate-900 dark:text-white">No titles found</h3>
                    <p class="text-slate-500 mt-1">Check back later for new content.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>
