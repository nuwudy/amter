@php
    // Placeholder for module details
@endphp
<x-layout>
    <div class="p-8 max-w-7xl mx-auto">
        {{-- Header --}}
        <div class="mb-10">
            <a href="{{ route('filament.student.pages.library') }}" class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-indigo-600 mb-4 transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to Library
            </a>
            <h1 class="text-3xl font-bold text-slate-900 mb-2">{{ $module->name }}</h1>
            <p class="text-lg text-slate-600">{{ $module->description }}</p>
        </div>
        
        <div class="space-y-8">
            @forelse($sessions as $session)
                {{-- Alpine Component for Pagination per Session --}}
                <div class="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden" 
                     x-data="{ 
                        search: '',
                        currentPage: 1, 
                        perPage: 10, 
                        total: {{ count($session->units) }},
                        get lastPage() { return Math.ceil(this.total / this.perPage) },
                        get paginatedUnits() {
                            return Array.from({length: this.total}, (_, i) => i).slice((this.currentPage - 1) * this.perPage, this.currentPage * this.perPage);
                        }
                     }">
                     
                    {{-- Session Header --}}
                    <div class="bg-slate-50/50 px-8 py-6 border-b border-slate-100 flex justify-between items-center">
                        <h2 class="font-bold text-xl text-slate-800">{{ $session->title }}</h2>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider" x-text="total + ' LEVELS'"></span>
                    </div>

                    {{-- Search Box --}}
                    <div class="px-8 py-3 border-b border-slate-100 bg-white/50">
                        <div class="relative">
                            <svg class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            <input type="text" 
                                   x-model="search" 
                                   placeholder="Search lessons..." 
                                   class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50/50 transition-all placeholder:text-slate-400">
                        </div>
                    </div>

                    {{-- Units List --}}
                    <div class="p-4 sm:p-6 space-y-3 min-h-[500px]">
                        @forelse($session->units as $index => $unit)
                            @php
                                $isUnlocked = $unit->isAccessibleBy(auth()->user());
                                $isPremium = !$unit->is_free_sample && !$unit->is_registered_only;
                            @endphp

                            {{-- Render all units but show tailored by Alpine --}}
                            <div class="group relative flex items-center gap-4 p-4 rounded-2xl transition-all duration-200 border {{ $isUnlocked ? 'bg-white hover:border-indigo-200 border-slate-100 hover:shadow-md cursor-pointer' : 'bg-slate-50 border-transparent opacity-90' }}"
                                 data-title="{{ strtolower($unit->title) }}"
                                 x-show="search === '' ? paginatedUnits.includes({{ $index }}) : $el.dataset.title.includes(search.toLowerCase())"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 translate-y-2"
                                 x-transition:enter-end="opacity-100 translate-y-0">
                                
                                {{-- Status Icon / Number --}}
                                <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center rounded-xl {{ $isUnlocked ? 'bg-indigo-50 text-indigo-600' : 'bg-slate-200 text-slate-400' }}">
                                    @if($isUnlocked)
                                        <span class="font-bold text-lg">{{ $unit->sort_order }}</span>
                                    @else
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                    @endif
                                </div>

                                {{-- Content Info --}}
                                <div class="flex-grow min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <h3 class="font-bold text-slate-900 truncate {{ !$isUnlocked ? 'text-slate-500' : '' }}">
                                            {{ $unit->title }}
                                        </h3>
                                        @if($isPremium && !$isUnlocked)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide bg-amber-100 text-amber-700">
                                                Premium
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-3 text-xs font-medium text-slate-500">
                                        <span class="flex items-center gap-1">
                                            @if($unit->video_id)
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                                Video Lesson
                                            @else
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                Interactive Lesson
                                            @endif
                                        </span>
                                    </div>
                                </div>

                                {{-- Action --}}
                                <div class="flex-shrink-0">
                                    {{-- Global Click Area: Applies to everything. Controller handles redirect if locked. --}}
                                    <a href="{{ route('student.units.show', $unit) }}" class="absolute inset-0 z-10 focus:outline-none">
                                        <span class="sr-only">{{ $isUnlocked ? 'Start Unit' : 'Unlock Unit' }}</span>
                                    </a>

                                    @if($isUnlocked)
                                        <svg class="w-5 h-5 text-indigo-400 group-hover:text-indigo-600 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    @else
                                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider px-3 py-1 bg-slate-100 rounded-lg group-hover:bg-amber-100 group-hover:text-amber-700 transition-colors">
                                            Locked
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @empty
                             <div class="text-center py-6 text-slate-400 text-sm">
                                 No units available in this session yet.
                             </div>
                        @endforelse
                    </div>

                    {{-- Pagination Controls --}}
                    <div class="px-8 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between" x-show="search === '' && total > perPage">
                        <button @click="currentPage--" :disabled="currentPage === 1" 
                                class="px-4 py-2 rounded-lg text-sm font-bold text-slate-600 hover:text-slate-900 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                            Prev
                        </button>
                        
                        <span class="text-xs font-bold text-slate-400 tracking-wider">
                            PAGE <span x-text="currentPage"></span> OF <span x-text="lastPage"></span>
                        </span>

                        <button @click="currentPage++" :disabled="currentPage === lastPage"
                                class="px-4 py-2 rounded-lg text-sm font-bold text-slate-600 hover:text-slate-900 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center gap-2">
                            Next
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </button>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 bg-white rounded-[2rem] border border-slate-100">
                     <p class="text-slate-500">No content available for this module yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-layout>
