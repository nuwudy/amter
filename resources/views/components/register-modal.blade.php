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
         style="position: fixed; inset: 0; z-index: 99990; background: rgba(9, 13, 22, 0.85); backdrop-filter: blur(10px);"
         @click="closeModal()">
    </div>

    {{-- Modal Dialog --}}
    <div x-show="open" 
         style="position: fixed; inset: 0; z-index: 99991; display: flex; align-items: center; justify-content: center; padding: 1rem; pointer-events: none;"
         role="dialog" 
         aria-modal="true">

        <div x-show="open"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             @click.outside="closeModal()"
             style="pointer-events: auto; position: relative; width: 100%; max-width: 420px; background: linear-gradient(180deg, #0f172a 0%, #090d16 100%); border: 1px solid rgba(255,255,255,0.18); border-radius: 2rem; padding: 2rem 1.75rem 1.65rem; box-shadow: 0 25px 70px -15px rgba(0,0,0,0.9); overflow: hidden; text-align: center; color: #ffffff;">

            {{-- Top Rainbow Accent --}}
            <div style="position: absolute; top: 0; left: 0; right: 0; height: 5px; background: linear-gradient(90deg, #00c2e8 0%, #ec4899 50%, #f59e0b 100%);"></div>

            {{-- Close Button --}}
            <button @click="closeModal()" 
                    style="position: absolute; top: 1rem; right: 1rem; width: 34px; height: 34px; border-radius: 9999px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15); color: #cbd5e1; display: flex; align-items: center; justify-content: center; cursor: pointer;"
                    aria-label="Close modal">
                ✕
            </button>

            {{-- Cute Floating Gift / Sparkle Badge --}}
            <div style="margin: 0 auto 1rem; width: 68px; height: 68px; border-radius: 1.4rem; background: rgba(0, 194, 232, 0.14); border: 1px solid rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; position: relative; box-shadow: 0 0 30px rgba(0, 194, 232, 0.25);">
                <span style="font-size: 2rem;">🎁</span>
                <span style="position: absolute; top: -6px; right: -8px; padding: 2px 7px; border-radius: 9999px; background: linear-gradient(90deg, #f97316, #ec4899); color: #fff; font-size: 9px; font-weight: 900; text-transform: uppercase;">
                    FREE
                </span>
            </div>

            {{-- Malayalam Main Text --}}
            <h2 style="font-size: 1.5rem; font-weight: 900; color: #ffffff; line-height: 1.35; margin: 0;">
                കൂടുതൽ ഫ്രീ ക്ലാസ്സുകൾക്കായി<br/>
                <span style="background: linear-gradient(90deg, #38bdf8 0%, #f472b6 55%, #fbbf24 100%); -webkit-background-clip: text; background-clip: text; color: transparent;">റജിസ്റ്റർ ചെയ്യൂ</span>
            </h2>

            {{-- English Subtitle --}}
            <p style="margin: 0.35rem 0 0; font-size: 0.88rem; font-weight: 700; color: #cbd5e1;">
                Register for more free classes
            </p>

            {{-- Friendly Benefit Bullets --}}
            <div style="margin: 1.15rem 0 1.35rem; padding: 0.95rem 1rem; border-radius: 1.15rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); text-align: left; display: flex; flex-direction: column; gap: 0.55rem;">
                <div style="display: flex; align-items: center; gap: 0.6rem; font-size: 0.8rem; color: #e2e8f0;">
                    <span style="width: 20px; height: 20px; border-radius: 9999px; background: rgba(16,185,129,0.2); color: #34d399; display: inline-flex; align-items: center; justify-content: center; font-weight: 900; font-size: 0.7rem; flex-shrink: 0;">✓</span>
                    <span>കൂടുതൽ സംഭാഷണ പാഠങ്ങൾ സൗജന്യമായി കേൾക്കാം</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.6rem; font-size: 0.8rem; color: #e2e8f0;">
                    <span style="width: 20px; height: 20px; border-radius: 9999px; background: rgba(16,185,129,0.2); color: #34d399; display: inline-flex; align-items: center; justify-content: center; font-weight: 900; font-size: 0.7rem; flex-shrink: 0;">✓</span>
                    <span>പഠന പുരോഗതി (Progress) കൃത്യമായി അറിയാം</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.6rem; font-size: 0.78rem; color: #38bdf8; font-weight: 700;">
                    <span style="width: 20px; height: 20px; border-radius: 9999px; background: rgba(0,194,232,0.2); color: #38bdf8; display: inline-flex; align-items: center; justify-content: center; font-weight: 900; font-size: 0.7rem; flex-shrink: 0;">⚡</span>
                    <span>100% സൗജന്യം • കാർഡോ പേയ്‌മെന്റോ ആവശ്യമില്ല</span>
                </div>
            </div>

            {{-- Two Buttons: Register & Login --}}
            <div style="display: flex; gap: 0.65rem;">
                <a :href="'{{ route('register') }}' + (targetUrl ? '?intended=' + encodeURIComponent(targetUrl) : '')"
                   style="flex: 1; padding: 0.85rem 1rem; border-radius: 1rem; background: linear-gradient(135deg, #f97316 0%, #ec4899 100%); box-shadow: 0 8px 20px -4px rgba(236,72,153,0.5); font-size: 0.88rem; font-weight: 800; color: #ffffff; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 0.35rem;">
                    <span>✨ Register</span>
                </a>

                <a :href="'{{ route('login') }}' + (targetUrl ? '?intended=' + encodeURIComponent(targetUrl) : '')"
                   style="flex: 1; padding: 0.85rem 1rem; border-radius: 1rem; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); font-size: 0.88rem; font-weight: 700; color: #ffffff; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 0.35rem;">
                    <span>🔑 Login</span>
                </a>
            </div>

            <button @click="closeModal()" style="margin-top: 0.9rem; background: transparent; border: none; font-size: 0.75rem; font-weight: 600; color: #94a3b8; cursor: pointer;">
                ഒരുപക്ഷേ പിന്നീട് (Maybe later)
            </button>
        </div>
    </div>
</div>

<script>
    window.openRegisterModal = function(targetUrl = '') {
        window.dispatchEvent(new CustomEvent('open-register-modal', { 
            detail: { targetUrl: targetUrl } 
        }));
    };
</script>
