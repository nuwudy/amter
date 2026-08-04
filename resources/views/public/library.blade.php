@extends('layouts.public', ['nav_theme' => 'dark'])
@section('title', 'Premium Course Library - Learn Spoken English with amter')
@section('meta_description', 'Unlock the power of spoken English with amter\'s premium library of interactive video lessons, AI roleplay, and voice-matching challenges.')

@section('content')
<div class="bg-slate-900 min-h-screen" style="background: linear-gradient(135deg, #1e293b, #0f172a);">
    {{-- Hero Section Redesign: Dark, Sleek, Conversion-Focused --}}
    <div class="relative pt-16 pb-2 overflow-hidden">

        {{-- Background Gradients --}}
        <div class="absolute inset-0 overflow-hidden pointer-events-none z-0">
            <div class="absolute top-[-20%] left-[-10%] w-[50vw] h-[50vw] rounded-full bg-gradient-to-br from-primary-500/20 to-blue-500/10 blur-3xl mix-blend-screen"></div>
            <div class="absolute bottom-[-20%] right-[-10%] w-[40vw] h-[40vw] rounded-full bg-gradient-to-tl from-blue-600/25 to-indigo-500/10 blur-3xl mix-blend-screen"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-white tracking-tight mb-4 leading-[1.3]">
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-500 via-cyan-400 to-blue-500">നിങ്ങളെ ഇംഗ്ലിഷ് സംസാരിപ്പിക്കും</span>
            </h1>
            <div class="text-base md:text-lg text-slate-300 mb-6 max-w-2xl mx-auto font-light leading-relaxed space-y-2 text-center">
                <p class="text-lg md:text-xl font-medium text-white">
                    നാരങ്ങ മിട്ടായി പോലെ മടുപ്പില്ലാതെ, നുണഞ്ഞിറക്കാവുന്ന ഈ ചെറിയ പാഠങ്ങൾ 
                </p>
                <p class="text-xl sm:text-2xl font-bold text-primary-400 pt-2 animate-pulse">
                    തുടങ്ങിയാലോ?
                </p>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                @auth
                    @php
                        $nextUnit = auth()->user()->getNextIncompleteUnit();
                        $resumeUrl = $nextUnit ? route('student.units.show', $nextUnit) : route('filament.student.pages.dashboard');
                    @endphp
                    <a href="{{ $resumeUrl }}" class="w-full sm:w-auto px-8 py-4 bg-primary-500 text-slate-950 font-black text-sm uppercase tracking-widest rounded-full shadow-[0_10px_30px_-10px_rgba(0,194,232,0.4)] hover:bg-primary-400 hover:shadow-[0_20px_40px_-10px_rgba(0,194,232,0.6)] transition-all transform hover:-translate-y-1 active:scale-95 flex justify-center items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Resume Your Journey
                    </a>
                @else
                    <a href="{{ route('pricing') }}" class="hidden sm:inline-flex px-8 py-4 bg-white/10 text-white border border-white/20 hover:bg-white/20 font-extrabold text-sm uppercase tracking-widest rounded-full backdrop-blur-md transition-all justify-center items-center gap-2 transform hover:-translate-y-1 active:scale-95">
                        Plans
                    </a>
                @endauth
            </div>
        </div>
    </div>

    {{-- Main Content Grid --}}
    <div id="courses" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        
        <div class="mb-6 text-center sm:text-left flex flex-col sm:flex-row justify-between items-center sm:items-end border-b border-slate-700 pb-4 gap-4">
            <div>
                <h2 class="text-3xl font-black text-white tracking-tight">The Library</h2>
            </div>
            
            <a href="{{ url('/?review=1#reviews') }}" class="hidden sm:inline-flex items-center gap-2 px-5 py-2.5 bg-slate-800 text-slate-300 rounded-full font-bold text-sm border border-slate-700 hover:bg-slate-700 transition-colors shadow-sm transform hover:scale-105">
                <span class="text-primary-500 text-lg">★</span> Read Success Stories
            </a>
        </div>

        {{-- Sessions Grid: 2 columns on mobile for abundance --}}
        <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-6">
            @forelse($sessions as $session)
                @php
                     // Get first unit logic
                     $firstUnit = $session->units->first();
                     $sessionFreeCount = $session->units->filter(function($u) {
                         return $u->is_published && ($u->is_free_sample || $u->is_registered_only);
                     })->count();
                     $hasGuestFree = $session->units->where('is_published', true)->contains('is_free_sample', true);

                     // Determine Target URL and Onclick handler
                     $targetUrl = '#';
                     $onclick = '';
                     if ($firstUnit) {
                         if (auth()->check()) {
                             $targetUrl = route('student.units.show', $firstUnit);
                         } else {
                             if ($firstUnit->is_free_sample) {
                                 $targetUrl = route('public.unit.show', ['course' => $session->module->course_id ?? 1, 'unit' => $firstUnit]);
                             } elseif ($firstUnit->is_registered_only) {
                                 $targetUrl = route('login');
                                 $onclick = "if(!confirm('Please create an account to get more free classes.')) return false;";
                             } else {
                                 $targetUrl = route('pricing');
                                 $onclick = "if(!confirm('Select a plan and Get unlimited access to the classes.')) return false;";
                             }
                         }
                     }
                @endphp
                <div class="group relative bg-white/5 rounded-2xl shadow-[0_4px_12px_-5px_rgba(0,0,0,0.1)] hover:shadow-[0_16px_32px_-10px_rgba(0,194,232,0.15)] transition-all duration-500 hover:-translate-y-1.5 overflow-hidden flex flex-col w-full border border-gray-700/50 backdrop-blur h-auto">
                    
                    {{-- Thumbnail Section (Clickable) --}}
                    <a href="{{ $targetUrl }}" @if($onclick) onclick="{!! $onclick !!}" @endif class="block relative h-28 sm:h-40 overflow-hidden bg-slate-800">
                        @if($session->thumbnail_path)
                            <x-image :src="$session->thumbnail_path" 
                                     :width="360" 
                                     :height="200" 
                                     alt="{{ $session->title }}"
                                     class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-108" />
                        @else
                            <div class="w-full h-full flex items-center justify-center text-slate-500 font-black tracking-widest uppercase text-xl opacity-30">
                                amter
                            </div>
                        @endif
                        
                        {{-- Gradient Overlay --}}
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/90 via-slate-900/30 to-transparent"></div>
                        
                        {{-- Module Badge --}}
                        <div class="absolute top-2.5 right-2.5 bg-white/95 backdrop-blur-md px-2 py-0.5 rounded-full text-[8px] sm:text-[9px] font-black tracking-wider text-slate-800 uppercase shadow-md border border-white/20">
                            {{ $session->module->name ?? 'Premium' }}
                        </div>

                         {{-- Badges for Access Types --}}
                        @if($sessionFreeCount > 0)
                            @if($hasGuestFree)
                                <div class="absolute top-2.5 left-2.5 flex items-center gap-1 bg-emerald-500 px-2 py-0.5 rounded-full shadow-md shadow-emerald-500/30 border border-white/85 z-10 transition-transform duration-300 hover:scale-105">
                                    <span class="bg-white text-emerald-600 px-1 rounded-md text-[9px] sm:text-[10px] font-black leading-none">
                                        {{ $sessionFreeCount }}
                                    </span>
                                    <span class="text-white text-[8px] sm:text-[9px] font-black uppercase tracking-wider leading-none pr-0.5">
                                        Free
                                    </span>
                                </div>
                            @else
                                <div class="absolute top-2.5 left-2.5 flex items-center gap-1 bg-blue-600 px-2 py-0.5 rounded-full shadow-md shadow-blue-600/30 border border-white/85 z-10 transition-transform duration-300 hover:scale-105">
                                    <span class="bg-white text-blue-700 px-1 rounded-md text-[9px] sm:text-[10px] font-black leading-none">
                                        {{ $sessionFreeCount }}
                                    </span>
                                    <span class="text-white text-[8px] sm:text-[9px] font-black uppercase tracking-wider leading-none pr-0.5">
                                        Free
                                    </span>
                                </div>
                            @endif
                        @else
                            @if($firstUnit)
                                <div class="absolute top-2.5 left-2.5 bg-slate-800/80 backdrop-blur-sm px-2 py-0.5 rounded-full text-[8px] sm:text-[9px] font-black tracking-widest text-white uppercase shadow-md border border-white/10 flex items-center gap-1">
                                    <svg style="width: 8px; height: 8px;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>
                                    Premium
                                </div>
                            @endif
                        @endif
                    </a>

                    {{-- Content Section --}}
                    <div class="p-3 sm:p-4.5 flex flex-col flex-grow justify-between bg-transparent relative">
                        <div class="mb-3">
                            {{-- Title (Clickable) --}}
                            <a href="{{ $targetUrl }}" @if($onclick) onclick="{!! $onclick !!}" @endif>
                                <h3 class="text-[13px] sm:text-base font-extrabold text-white leading-tight mb-2 line-clamp-2 hover:text-primary-400 transition-colors">
                                    {{ $session->title }}
                                </h3>
                            </a>
                            
                            {{-- Interactive Lessons Badge (Clickable & Color Aligned) --}}
                            <a href="{{ $targetUrl }}" @if($onclick) onclick="{!! $onclick !!}" @endif class="inline-flex items-center gap-1.5 bg-white/10 w-fit px-2 py-1 rounded-md border border-white/20 hover:bg-white/20 transition-all hover:border-primary-500/30">
                                <svg class="w-3.5 h-3.5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                <span class="text-[8px] sm:text-[10px] text-slate-300 font-extrabold uppercase tracking-wider">
                                    {{ $session->units_count }} Lessons
                                </span>
                            </a>
                            
                            @if($session->description)
                                <p class="text-[10px] sm:text-xs text-slate-300 line-clamp-2 mt-2 leading-relaxed font-medium">
                                    {{ $session->description }}
                                </p>
                            @endif
                        </div>

                        {{-- Action Button --}}
                        <div class="mt-auto pt-1 flex flex-col gap-1.5">
                            @if($firstUnit)
                                @auth
                                    {{-- Logged in: Go directly to student view --}}
                                    <a href="{{ $targetUrl }}" 
                                        class="inline-flex justify-center items-center gap-1.5 w-full py-2 sm:py-2.5 bg-slate-900 text-white rounded-xl text-[9px] sm:text-xs font-black uppercase tracking-widest shadow-md hover:bg-black transition-all transform active:scale-95 group/btn">
                                        Start Learning
                                        <svg class="w-3.5 h-3.5 transition-transform group-hover/btn:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                    </a>
                                @else
                                    {{-- Guest Logic --}}
                                    @if($firstUnit->is_free_sample)
                                        {{-- Free No Login Required -> Public View --}}
                                        <a href="{{ $targetUrl }}" 
                                            class="inline-flex justify-center items-center gap-1.5 w-full py-2 sm:py-2.5 bg-primary-500 text-slate-950 rounded-xl text-[9px] sm:text-xs font-black uppercase tracking-widest shadow-md shadow-primary-500/20 hover:bg-primary-400 transition-all transform active:scale-95 group/btn">
                                            Try For Free
                                            <svg class="w-3.5 h-3.5 transition-transform group-hover/btn:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                        </a>
                                    @elseif($firstUnit->is_registered_only)
                                        {{-- Free Login Required --}}
                                        <a href="{{ $targetUrl }}" @if($onclick) onclick="{!! $onclick !!}" @endif
                                            class="inline-flex justify-center items-center gap-1.5 w-full py-2 sm:py-2.5 bg-white text-slate-700 border border-slate-200 rounded-xl text-[9px] sm:text-xs font-black uppercase tracking-widest hover:border-primary-500 hover:text-primary-600 transition-all text-center">
                                            Login For Access
                                        </a>
                                    @else
                                        {{-- Paid Login Required --}}
                                        <a href="{{ $targetUrl }}" @if($onclick) onclick="{!! $onclick !!}" @endif
                                            class="inline-flex justify-center items-center gap-1.5 w-full py-2 sm:py-2.5 bg-gradient-to-r from-primary-500 to-blue-600 text-white font-black rounded-xl text-[9px] sm:text-xs uppercase tracking-widest hover:from-primary-600 hover:to-blue-700 transition-all shadow-md shadow-primary-500/20 transform active:scale-95 group/btn border border-primary-400 text-center">
                                            Premium Unlock
                                        </a>
                                    @endif
                                @endauth
                            @else
                                <button disabled class="inline-flex justify-center items-center w-full py-2 sm:py-2.5 bg-slate-800 text-slate-500 rounded-xl text-[9px] sm:text-xs font-black uppercase tracking-widest cursor-not-allowed">
                                    Dropping Soon
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-32 text-center bg-white/5 rounded-3xl border border-slate-700 backdrop-blur shadow-[0_10px_30px_-15px_rgba(0,0,0,0.1)]">
                    <div class="w-24 h-24 bg-white/10 rounded-full flex items-center justify-center mx-auto mb-6 transform transition-transform hover:rotate-12 border border-white/20">
                        <svg class="w-12 h-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    </div>
                    <h3 class="text-3xl font-black text-white tracking-tight">New Courses Incoming</h3>
                    <p class="mt-3 text-slate-300 font-medium text-lg">We are preparing incredible new interactive content. Check back shortly!</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<style>
    @keyframes fade-in-up {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-up {
        animation: fade-in-up 0.8s ease-out forwards;
    }
</style>
@endsection
