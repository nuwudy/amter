<x-layout>
    {{-- Milestone Celebration Modal --}}
    @if(session('milestone_awarded'))
        @php $achievement = (object) session('milestone_awarded'); @endphp
        <div x-data="{ show: true }" 
             x-show="show" 
             x-init="
                setTimeout(() => {
                    // Trigger fireworks
                    if (window.confetti) {
                        var duration = 3000;
                        var animationEnd = Date.now() + duration;
                        var defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 150 };

                        var intervalConfetti = setInterval(function() {
                            var timeLeft = animationEnd - Date.now();
                            if (timeLeft <= 0) return clearInterval(intervalConfetti);
                            
                            var particleCount = 50 * (timeLeft / duration);
                            confetti(Object.assign({}, defaults, { particleCount, origin: { x: Math.random(), y: Math.random() - 0.2 } }));
                        }, 250);
                    }
                    
                    // Auto-dismiss after 6 seconds
                    setTimeout(() => { show = false; }, 6000);
                }, 300);
             "
             class="fixed inset-0 z-[110] flex items-center justify-center p-4"
             style="display: none;">
            
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
                 x-show="show"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="show = false"></div>

            {{-- Modal Card --}}
            <div class="relative w-full max-w-sm bg-white dark:bg-slate-800 rounded-[2.5rem] p-8 text-center shadow-[0_20px_50px_rgba(0,0,0,0.15)] ring-1 ring-white/50 transform transition-all"
                 x-show="show"
                 x-transition:enter="ease-out duration-500"
                 x-transition:enter-start="opacity-0 translate-y-10 scale-90"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="ease-in duration-300"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 translate-y-10 scale-90">
                
                {{-- Trophy Icon --}}
                <div class="w-20 h-20 mx-auto rounded-full flex items-center justify-center mb-6 shadow-inner ring-4 ring-white"
                     style="background-color: #fef3c7;">
                    <span class="text-4xl">🏆</span>
                </div>
                
                <h2 class="text-2xl font-black text-slate-900 dark:text-white mb-2 uppercase tracking-wide">
                    {{ $achievement->title }}
                </h2>
                
                <div class="w-12 h-1 rounded-full mx-auto my-4" style="background-color: #fbbf24;"></div>
                
                <p class="text-slate-600 dark:text-slate-300 mb-8 leading-relaxed font-medium">
                    {{ $achievement->message }}
                </p>
                
                <button @click="show = false" 
                        style="background-color: #0f172a; color: #ffffff; border-radius: 1rem; cursor: pointer; border: none; transition: all 0.2s; box-shadow: 0 10px 15px -3px rgba(15, 23, 42, 0.3);"
                        class="w-full py-4 text-white font-bold uppercase tracking-widest hover:bg-black transition-all hover:scale-105 active:scale-95 shadow-xl">
                    Awesome!
                </button>
            </div>

            {{-- Load Confetti via CDN if not present --}}
            @if(!app()->isProduction())
            <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
            @endif
        </div>
    @endif

    {{-- Lesson Mastered / XP Celebration Toast & Confetti --}}
    {{-- Lesson Mastered / XP Celebration Modal --}}
    @if(session('lesson_mastered') || session('course_completed'))
        <div x-data="{ show: true }" 
             x-show="show" 
             x-init="
                setTimeout(() => {
                    // Trigger deep confetti
                    if (window.confetti) {
                        var duration = 3000;
                        var animationEnd = Date.now() + duration;
                        var defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 150 };

                        var random = function(min, max) { return Math.random() * (max - min) + min; };

                        var intervalConfetti = setInterval(function() {
                            var timeLeft = animationEnd - Date.now();

                            if (timeLeft <= 0) {
                                return clearInterval(intervalConfetti);
                            }

                            var particleCount = 50 * (timeLeft / duration);
                            confetti(Object.assign({}, defaults, { particleCount, origin: { x: random(0.1, 0.3), y: Math.random() - 0.2 } }));
                            confetti(Object.assign({}, defaults, { particleCount, origin: { x: random(0.7, 0.9), y: Math.random() - 0.2 } }));
                        }, 250);
                    }
                    
                    // Auto-dismiss after 6 seconds
                    setTimeout(() => { show = false; }, 6000);
                }, 300);
             "
             class="fixed inset-0 z-[100] flex items-center justify-center p-4"
             style="display: none;">
            
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
                 x-show="show"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="show = false"></div>

            {{-- Modal Card --}}
            <div class="relative w-full max-w-sm bg-white dark:bg-slate-800 rounded-[2.5rem] p-8 text-center shadow-[0_20px_50px_rgba(0,0,0,0.15)] ring-1 ring-white/50 transform transition-all"
                 x-show="show"
                 x-transition:enter="ease-out duration-500"
                 x-transition:enter-start="opacity-0 translate-y-10 scale-90"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="ease-in duration-300"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 translate-y-10 scale-90">

                 {{-- Close button --}}
                 <button @click="show = false" class="absolute top-4 right-4 p-2 text-slate-300 hover:text-slate-500 transition-colors rounded-full hover:bg-slate-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                 </button>

                 {{-- XP Badge --}}
                 <div class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full text-xl font-black shadow-xl mb-6 mx-auto transform -translate-y-2"
                      style="background-color: #0f172a; color: #fbbf24; border: 1px solid #1e293b;">
                    <svg class="w-6 h-6 animate-pulse" fill="currentColor" viewBox="0 0 20 20" style="color: #fbbf24;"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    <span style="color: #fbbf24;">+{{ session('xp_gained', 100) }} XP</span>
                 </div>
                 
                 <div class="space-y-4">
                     <h2 class="text-3xl font-black text-slate-800 dark:text-white uppercase tracking-tight leading-none">
                        {{ session('course_completed') ? 'Course Completed!' : 'Lesson Mastered!' }}
                     </h2>
                     
                     <div class="w-12 h-1 bg-amber-400 rounded-full mx-auto"></div>

                     <p class="text-base font-medium text-slate-500 dark:text-slate-400 leading-relaxed px-4">
                        {{ session('success') }}
                     </p>
                 </div>
                 
                 {{-- Action Button --}}
                 <button @click="show = false" 
                         style="background-color: #fbbf24; color: #0f172a; border-radius: 1rem; cursor: pointer; border: none; transition: all 0.2s; box-shadow: 0 10px 15px -3px rgba(251, 191, 36, 0.3);"
                         class="mt-8 w-full py-4 font-black uppercase tracking-widest hover:brightness-110 active:scale-95">
                    Continue Learning
                 </button>
            </div>

            {{-- Load Confetti via CDN if not present --}}
            <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
        </div>
    @endif

    {{-- Premium Renderer (Handles its own background/navigation) --}}

    <div class="premium-container" style="width: 100%; margin: 0 auto; display: flex; flex-direction: column; align-items: center;">
        @if(is_array($unit->content_blocks))
            @include('filament.components.unit-renderer-student', ['blocks' => $unit->content_blocks, 'isPublic' => true, 'unit' => $unit])
        @endif
    </div>



</x-layout>
