{{-- Cute Register & Login Modal for Prospects clicking Registered-Only Content --}}
<div x-data="{ 
        open: false, 
        targetUrl: '',
        openModal(url = '') {
            this.targetUrl = url;
            this.open = true;
            document.body.style.overflow = 'hidden';
        },
        closeModal() {
            this.open = false;
            document.body.style.overflow = '';
        }
    }"
    @open-register-modal.window="openModal($event.detail?.targetUrl || '')"
    @keydown.escape.window="closeModal()"
    x-cloak>

    {{-- Backdrop --}}
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[100] bg-slate-950/80 backdrop-blur-md"
         @click="closeModal()">
    </div>

    {{-- Modal Dialog --}}
    <div x-show="open" 
         class="fixed inset-0 z-[101] flex items-center justify-center p-4 sm:p-6 pointer-events-none"
         role="dialog" 
         aria-modal="true">

        <div x-show="open"
             x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-350"
             x-transition:enter-start="opacity-0 scale-90 translate-y-6"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-90 translate-y-6"
             @click.outside="closeModal()"
             class="pointer-events-auto relative w-full max-w-md bg-gradient-to-b from-slate-900 via-slate-900 to-slate-950 border border-white/15 rounded-[2.5rem] p-6 sm:p-8 shadow-[0_25px_70px_-15px_rgba(0,0,0,0.9)] overflow-hidden text-center">

            {{-- Top Rainbow Accent --}}
            <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-primary-400 via-pink-500 to-amber-400"></div>

            {{-- Close Button --}}
            <button @click="closeModal()" 
                    class="absolute top-4 right-4 sm:top-5 sm:right-5 w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 text-slate-300 hover:text-white flex items-center justify-center transition-colors focus:outline-none"
                    aria-label="Close modal">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            {{-- Cute Floating Gift / Sparkle Badge --}}
            <div class="mx-auto mb-4 w-16 h-16 sm:w-20 sm:h-20 rounded-3xl bg-gradient-to-tr from-primary-500/20 via-pink-500/20 to-amber-500/20 border border-white/20 flex items-center justify-center shadow-[0_0_30px_rgba(6,182,212,0.35)] relative">
                <span class="text-3xl sm:text-4xl animate-bounce">🎁</span>
                <span class="absolute -top-1.5 -right-1.5 px-2 py-0.5 rounded-full bg-gradient-to-r from-orange-500 to-pink-500 text-white text-[9px] font-black uppercase tracking-wider shadow-sm animate-pulse">
                    FREE
                </span>
            </div>

            {{-- Malayalam Main Text (Requested by user) --}}
            <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight leading-snug">
                കൂടുതൽ ഫ്രീ ക്ലാസ്സുകൾക്കായി<br/>
                <span class="bg-gradient-to-r from-primary-400 via-cyan-300 to-pink-400 bg-clip-text text-transparent">റജിസ്റ്റർ ചെയ്യൂ</span>
            </h2>

            {{-- English Subtitle (Requested by user) --}}
            <p class="mt-2 text-sm sm:text-base font-bold text-slate-300">
                Register for more free classes
            </p>

            {{-- Friendly Benefit Bullets --}}
            <div class="mt-5 mb-6 p-4 rounded-2xl bg-white/5 border border-white/10 text-left space-y-2.5">
                <div class="flex items-center gap-2.5 text-xs sm:text-sm text-slate-200">
                    <span class="flex-shrink-0 w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs">✓</span>
                    <span>കൂടുതൽ സംഭാഷണ പാഠങ്ങൾ സൗജന്യമായി കേൾക്കാം</span>
                </div>
                <div class="flex items-center gap-2.5 text-xs sm:text-sm text-slate-200">
                    <span class="flex-shrink-0 w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-xs">✓</span>
                    <span>പഠന പുരോഗതി (Progress) കൃത്യമായി അറിയാം</span>
                </div>
                <div class="flex items-center gap-2.5 text-xs sm:text-sm text-slate-300">
                    <span class="flex-shrink-0 w-5 h-5 rounded-full bg-primary-500/20 text-primary-400 flex items-center justify-center font-bold text-xs">⚡</span>
                    <span class="text-xs text-primary-300 font-semibold">100% സൗജന്യം • കാർഡോ പേയ്‌മെന്റോ ആവശ്യമില്ല</span>
                </div>
            </div>

            {{-- Two Buttons: Register & Login (As requested) --}}
            <div class="flex flex-col gap-3">
                {{-- Primary Register Button --}}
                <a :href="'{{ route('register') }}' + (targetUrl ? '?intended=' + encodeURIComponent(targetUrl) : '')"
                   class="w-full relative overflow-hidden group py-3.5 px-6 rounded-2xl bg-gradient-to-r from-primary-500 via-cyan-400 to-blue-500 shadow-[0_10px_25px_-5px_rgba(6,182,212,0.4)] text-base font-black text-slate-950 transition-all duration-300 hover:shadow-[0_15px_30px_-5px_rgba(6,182,212,0.6)] hover:scale-[1.02] active:scale-95 flex items-center justify-center gap-2">
                    <span>റജിസ്റ്റർ ചെയ്യൂ (Register Free)</span>
                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>

                {{-- Secondary Login Button --}}
                <a :href="'{{ route('login') }}' + (targetUrl ? '?intended=' + encodeURIComponent(targetUrl) : '')"
                   class="w-full py-3 px-6 rounded-2xl bg-white/10 hover:bg-white/15 border border-white/20 text-sm font-bold text-white transition-all duration-200 hover:scale-[1.01] active:scale-95 flex items-center justify-center gap-2">
                    <span class="text-slate-300">ഇതിനകം അക്കൗണ്ട് ഉണ്ടോ?</span>
                    <span class="text-primary-400 font-black underline underline-offset-2">Login ചെയ്യുക</span>
                </a>
            </div>

            {{-- Close / Maybe Later --}}
            <button @click="closeModal()" class="mt-4 text-xs font-semibold text-slate-500 hover:text-slate-300 transition-colors">
                ഒരുപക്ഷേ പിന്നീട് (Maybe later)
            </button>
        </div>
    </div>
</div>

<script>
    // Global helper so ANY button/link can trigger the cute popup
    window.openRegisterModal = function(targetUrl = '') {
        window.dispatchEvent(new CustomEvent('open-register-modal', { 
            detail: { targetUrl: targetUrl } 
        }));
    };
</script>
