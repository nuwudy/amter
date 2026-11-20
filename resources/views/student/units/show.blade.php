<x-layout>
    {{-- Milestone Celebration Modal --}}
    @if(session('milestone_awarded'))
        @php $achievement = (object) session('milestone_awarded'); @endphp
        <div x-data="{ show: true }"
             x-show="show"
             x-init="
                setTimeout(() => {
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
                    setTimeout(() => { show = false; }, 6000);
                }, 300);
             "
             class="fixed inset-0 z-[110] flex items-center justify-center p-4"
             style="display: none;">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
                 x-show="show"
                 @click="show = false"></div>
            <div class="relative w-full max-w-sm bg-white rounded-[2.5rem] p-8 text-center shadow-[0_20px_50px_rgba(0,0,0,0.15)] ring-1 ring-white/50 transform transition-all"
                 x-show="show">
                <div class="w-20 h-20 mx-auto rounded-full flex items-center justify-center mb-6 shadow-inner ring-4 ring-white"
                     style="background-color: #fef3c7;">
                    <span class="text-4xl">🏆</span>
                </div>
                <h2 class="text-2xl font-black text-slate-900 mb-2 uppercase tracking-wide">
                    {{ $achievement->title }}
                </h2>
                <div class="w-12 h-1 rounded-full mx-auto my-4" style="background-color: #fbbf24;"></div>
                <p class="text-slate-600 mb-8 leading-relaxed font-medium">
                    {{ $achievement->message }}
                </p>
                <button @click="show = false"
                        class="w-full py-4 rounded-2xl bg-slate-900 text-white font-bold uppercase tracking-widest hover:bg-black transition-all active:scale-95 shadow-xl border-0 cursor-pointer">
                    Awesome!
                </button>
            </div>
            @if(!app()->isProduction())
                <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
            @endif
        </div>
    @endif

    {{-- Lesson Mastered / XP Celebration Modal --}}
    @if(session('lesson_mastered') || session('course_completed'))
        <div x-data="{ show: true }"
             x-show="show"
             x-init="
                setTimeout(() => {
                    if (window.confetti) {
                        var duration = 3000;
                        var animationEnd = Date.now() + duration;
                        var defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 150 };
                        var random = function(min, max) { return Math.random() * (max - min) + min; };
                        var intervalConfetti = setInterval(function() {
                            var timeLeft = animationEnd - Date.now();
                            if (timeLeft <= 0) return clearInterval(intervalConfetti);
                            var particleCount = 50 * (timeLeft / duration);
                            confetti(Object.assign({}, defaults, { particleCount, origin: { x: random(0.1, 0.3), y: Math.random() - 0.2 } }));
                            confetti(Object.assign({}, defaults, { particleCount, origin: { x: random(0.7, 0.9), y: Math.random() - 0.2 } }));
                        }, 250);
                    }
                    setTimeout(() => { show = false; }, 6000);
                }, 300);
             "
             class="fixed inset-0 z-[100] flex items-center justify-center p-4"
             style="display: none;">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
                 x-show="show"
                 @click="show = false"></div>
            <div class="relative w-full max-w-sm bg-white rounded-[2.5rem] p-8 text-center shadow-[0_20px_50px_rgba(0,0,0,0.15)] ring-1 ring-white/50 transform transition-all"
                 x-show="show">
                <button @click="show = false" class="absolute top-4 right-4 p-2 text-slate-300 hover:text-slate-500 transition-colors rounded-full hover:bg-slate-100 border-0 bg-transparent cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
                <div class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full text-xl font-black shadow-xl mb-6 mx-auto bg-slate-900 text-amber-400">
                    <span>+{{ session('xp_gained', 100) }} XP</span>
                </div>
                <div class="space-y-4">
                    <h2 class="text-3xl font-black text-slate-800 uppercase tracking-tight leading-none">
                        {{ session('course_completed') ? 'Course Completed!' : 'Lesson Mastered!' }}
                    </h2>
                    <div class="w-12 h-1 bg-amber-400 rounded-full mx-auto"></div>
                    <p class="text-base font-medium text-slate-500 leading-relaxed px-4">
                        {{ session('success') }}
                    </p>
                </div>
                <button @click="show = false"
                        class="mt-8 w-full py-4 rounded-2xl bg-amber-400 text-slate-900 font-black uppercase tracking-widest hover:brightness-110 active:scale-95 border-0 cursor-pointer shadow-lg">
                    Continue Learning
                </button>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
        </div>
    @endif

    {{-- Full-Screen Distraction-Free Vertical Step Lesson Player --}}
    @include('filament.components.unit-chat-player', [
        'unit' => $unit,
        'currentTrack' => $currentTrack ?? null,
        'isFullscreen' => true,
    ])
</x-layout>
