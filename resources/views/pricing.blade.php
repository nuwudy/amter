@extends('layouts.public', ['nav_theme' => 'dark'])
@section('title', 'Pricing Plans - Affordable Spoken English Courses')
@section('meta_description', 'Choose the best plan for your English learning journey. Simple, transparent pricing with no hidden fees.')

@section('content')
<div x-data="pricingCheckout()" class="py-24 sm:py-32 bg-slate-900 min-h-screen relative overflow-hidden">
    <!-- Background Blobs -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none z-0">
        <div class="absolute top-[-20%] left-[-10%] w-[50vw] h-[50vw] rounded-full bg-gradient-to-br from-indigo-500/20 to-purple-600/10 blur-3xl mix-blend-screen"></div>
        <div class="absolute bottom-[-20%] right-[-10%] w-[40vw] h-[40vw] rounded-full bg-gradient-to-tl from-indigo-500/20 to-indigo-600/10 blur-3xl mix-blend-screen"></div>
    </div>

    <!-- Verification Loading Overlay -->
    <div x-show="isVerifying" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-md">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl p-8 max-w-sm w-full mx-4 text-center shadow-2xl">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-indigo-500/20 text-indigo-400 mb-4 animate-spin">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-white mb-2">Verifying Payment</h3>
            <p class="text-gray-300 text-sm" x-text="verifyingMessage"></p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        {{-- Header --}}
        <div class="max-w-3xl mx-auto text-center mb-8 sm:mb-12">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-xs font-bold uppercase tracking-wider text-indigo-300 mb-3">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                Progressive Fee Reduction Framework
            </div>
            <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-white font-sans">
                Learn English from <span class="bg-gradient-to-r from-emerald-400 via-teal-300 to-cyan-400 bg-clip-text text-transparent">₹4.93 / day</span>
            </h1>
            <p class="mx-auto mt-3 max-w-xl text-center text-sm sm:text-base text-slate-300">
                Anchored to a baseline <strong class="text-white">₹10/day</strong> fee. The longer you commit to your practice, the bigger your daily savings!
            </p>
        </div>
        
        <!-- Error Alert -->
        <div x-show="errorMessage" x-cloak class="max-w-4xl mx-auto mb-6 transition-all">
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 px-5 py-3.5 rounded-xl flex items-center justify-between gap-3 text-sm">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span x-text="errorMessage"></span>
                </div>
                <button type="button" @click="errorMessage = ''" class="text-red-400 hover:text-red-200">
                    <span class="sr-only">Dismiss</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </div>

        <!-- Success Alert -->
        <div x-show="successMessage" x-cloak class="max-w-4xl mx-auto mb-6 transition-all">
            <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-5 py-3.5 rounded-xl flex items-center justify-between gap-3 text-sm">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span x-text="successMessage"></span>
                </div>
                <button type="button" @click="successMessage = ''" class="text-emerald-400 hover:text-emerald-200">
                    <span class="sr-only">Dismiss</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </div>

        @auth
            {{-- Optional Unified WhatsApp / Mobile Number Input --}}
            <div class="max-w-sm mx-auto mb-8 bg-slate-800/80 border border-slate-700/80 rounded-2xl p-2 sm:p-2.5 flex items-center gap-3 backdrop-blur-md shadow-xl">
                <div class="w-8 h-8 rounded-xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <input type="tel" 
                           x-model="phone" 
                           placeholder="Phone for UPI & WhatsApp receipt" 
                           maxlength="10" 
                           class="w-full bg-transparent text-xs sm:text-sm text-white placeholder-slate-400 outline-none border-none p-0 focus:ring-0">
                </div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-2 py-0.5 rounded-full flex-shrink-0">
                    UPI Active
                </span>
            </div>
        @endauth

        @php
            // Metadata lookup keyed by duration_days
            $tierMetadata = [
                1 => [
                    'label' => '1 Day',
                    'tag' => 'Day Pass',
                    'badge' => '⚡ Micro Pass',
                    'sub' => 'Chai-price test drive',
                    'isPopular' => false,
                    'isBestValue' => false,
                    'accent' => 'border-slate-700/80 bg-slate-800/70',
                    'badgeClass' => 'bg-slate-700 text-slate-300 border-slate-600',
                ],
                7 => [
                    'label' => '1 Week',
                    'tag' => 'Sprint Pass',
                    'badge' => '🎯 7 Days',
                    'sub' => 'Short exam / interview sprint',
                    'isPopular' => false,
                    'isBestValue' => false,
                    'accent' => 'border-slate-700/80 bg-slate-800/70',
                    'badgeClass' => 'bg-sky-500/15 text-sky-300 border-sky-500/30',
                ],
                30 => [
                    'label' => '1 Month',
                    'tag' => 'Habit Builder',
                    'badge' => '🌱 30 Days',
                    'sub' => 'Overcome basic hesitation',
                    'isPopular' => false,
                    'isBestValue' => false,
                    'accent' => 'border-slate-700/80 bg-slate-800/70',
                    'badgeClass' => 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30',
                ],
                90 => [
                    'label' => '3 Months',
                    'tag' => 'Fluency Habit',
                    'badge' => '🚀 90 Days',
                    'sub' => 'Build speaking habit',
                    'isPopular' => false,
                    'isBestValue' => false,
                    'accent' => 'border-slate-700/80 bg-slate-800/70',
                    'badgeClass' => 'bg-purple-500/15 text-purple-300 border-purple-500/30',
                ],
                180 => [
                    'label' => '6 Months',
                    'tag' => 'Most Popular',
                    'badge' => '🔥 MOST POPULAR',
                    'sub' => 'Conversational pro (<₹1,000)',
                    'isPopular' => true,
                    'isBestValue' => false,
                    'accent' => 'border-indigo-500/80 bg-gradient-to-b from-indigo-950/60 to-slate-900 ring-2 ring-indigo-500/50 shadow-xl shadow-indigo-500/20',
                    'badgeClass' => 'bg-indigo-500 text-white font-extrabold shadow-md shadow-indigo-500/40',
                ],
                365 => [
                    'label' => '1 Year',
                    'tag' => 'Best Value',
                    'badge' => '👑 BEST VALUE',
                    'sub' => 'Unrestricted (<₹5/day)',
                    'isPopular' => false,
                    'isBestValue' => true,
                    'accent' => 'border-amber-400/80 bg-gradient-to-b from-amber-950/40 to-slate-900 ring-2 ring-amber-400/50 shadow-xl shadow-amber-500/20',
                    'badgeClass' => 'bg-gradient-to-r from-amber-400 to-amber-500 text-slate-950 font-black shadow-md shadow-amber-500/30',
                ],
            ];
        @endphp

        {{-- Small Cute Boxy Grid --}}
        <div class="isolate mx-auto grid w-full grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4 items-stretch">
            @foreach($plans as $plan)
                @php
                    $days = max(1, (int) $plan->duration_days);
                    $linearBase = $days * 10;
                    $perDay = round($plan->price / $days, 2);
                    $discount = $linearBase > $plan->price ? round((($linearBase - $plan->price) / $linearBase) * 100) : 0;
                    
                    $meta = $tierMetadata[$days] ?? [
                        'label' => $plan->name,
                        'tag' => $days . ' Days',
                        'badge' => $plan->is_best_value ? '💎 Best Value' : $days . ' Days',
                        'sub' => 'Full access to spoken English',
                        'isPopular' => false,
                        'isBestValue' => (bool)$plan->is_best_value,
                        'accent' => 'border-slate-700/80 bg-slate-800/70',
                        'badgeClass' => 'bg-slate-700 text-slate-300 border-slate-600',
                    ];
                @endphp

                <div class="relative rounded-2xl p-4 sm:p-5 flex flex-col justify-between transition-all duration-300 hover:-translate-y-1.5 hover:shadow-2xl border backdrop-blur-md group {{ $meta['accent'] }}">
                    
                    {{-- Cute Micro Badge --}}
                    <div class="flex items-center justify-between gap-1 mb-2.5">
                        <span class="text-[10px] sm:text-[11px] font-bold px-2 py-0.5 rounded-full border {{ $meta['badgeClass'] }}">
                            {{ $meta['badge'] }}
                        </span>
                        @if($discount > 0)
                            <span class="text-[10px] sm:text-[11px] font-black text-emerald-400 bg-emerald-500/15 border border-emerald-500/25 px-1.5 py-0.5 rounded-md">
                                -{{ $discount }}%
                            </span>
                        @endif
                    </div>

                    {{-- Duration Name --}}
                    <div>
                        <h3 class="text-base sm:text-lg font-black tracking-tight text-white leading-tight">
                            {{ $meta['label'] }}
                        </h3>

                        {{-- Per-day Pill (Hero Presentation Anchor) --}}
                        <div class="my-2.5 py-1.5 px-2.5 rounded-xl bg-slate-900/90 border border-slate-700/70 flex items-baseline justify-center gap-1 shadow-inner">
                            <span class="text-base sm:text-lg font-black text-emerald-400 font-mono">
                                ₹{{ number_format($perDay, $perDay < 10 ? 2 : 0) }}
                            </span>
                            <span class="text-[10px] sm:text-xs font-semibold text-slate-400 uppercase tracking-wider">
                                / day
                            </span>
                        </div>

                        {{-- Lump Sum Total & Linear Strikethrough --}}
                        <div class="flex items-baseline justify-center gap-1.5 flex-wrap">
                            <span class="text-xl sm:text-2xl font-black tracking-tight text-white">
                                ₹{{ number_format($plan->price, 0) }}
                            </span>
                            @if($linearBase > $plan->price)
                                <span class="text-[11px] sm:text-xs line-through text-slate-500 font-medium">
                                    ₹{{ number_format($linearBase, 0) }}
                                </span>
                            @endif
                        </div>

                        {{-- Subtext / Tagline --}}
                        <p class="mt-2 text-[11px] sm:text-xs text-slate-300 leading-snug text-center min-h-[2rem] flex items-center justify-center">
                            {{ $meta['sub'] }}
                        </p>
                    </div>

                    {{-- CTA Button --}}
                    <div class="mt-4 pt-2 border-t border-white/5">
                        @auth
                            <button type="button" 
                                    @click="startCheckout({{ $plan->id }}, '{{ addslashes($plan->name) }}')"
                                    :disabled="loadingPlanId === {{ $plan->id }}"
                                    :class="loadingPlanId === {{ $plan->id }} ? 'opacity-75 cursor-not-allowed' : ''"
                                    class="w-full font-bold py-2 sm:py-2.5 px-3 rounded-xl text-xs sm:text-sm transition-all duration-200 flex items-center justify-center gap-1.5 {{ $meta['isPopular'] ? 'bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/30' : ($meta['isBestValue'] ? 'bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 text-slate-950 font-black shadow-lg shadow-amber-500/25' : 'bg-white/10 hover:bg-white/20 border border-white/10 text-white') }}">
                                <template x-if="loadingPlanId === {{ $plan->id }}">
                                    <svg class="animate-spin h-3.5 w-3.5 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                </template>
                                <span x-text="loadingPlanId === {{ $plan->id }} ? 'Wait...' : 'Buy Pass'"></span>
                            </button>
                        @else
                            <a href="{{ route('login') }}" class="block w-full py-2 sm:py-2.5 px-3 rounded-xl text-xs sm:text-sm font-bold text-center transition-all duration-200 {{ $meta['isPopular'] ? 'bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/30' : ($meta['isBestValue'] ? 'bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 text-slate-950 font-black' : 'bg-white/10 hover:bg-white/20 border border-white/10 text-white') }}">
                                Login to Buy
                            </a>
                        @endauth
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Trust & Assurance Footer --}}
        <div class="mt-12 text-center space-y-3">
            <div class="inline-flex items-center gap-3 px-4 py-2 rounded-full bg-slate-800/80 border border-slate-700/80 text-xs text-slate-300 shadow-md">
                <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1a4.002 4.002 0 00-3.2 1.6 4 4 0 00-6.19 4.61 4 4 0 001.19 6.78 4 4 0 004.2 3.01h8a4 4 0 004-4 4 4 0 00-1-7.79 4.002 4.002 0 00-6.8-4.21zm2.707 7.707a1 1 0 00-1.414-1.414L9 9.586 7.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l3-3z" clip-rule="evenodd"/></svg>
                <span>Instant Track Activation &bull; Secured by <strong>Razorpay</strong> &bull; GPay, PhonePe, UPI & Cards</span>
            </div>
            <p class="text-slate-400 text-xs sm:text-sm">
                Multi-month plans are non-refundable &bull; Need payment assistance? <a href="{{ route('contact') }}" class="text-indigo-400 font-medium hover:underline">Contact Support</a>
            </p>
        </div>
    </div>
</div>

<!-- Razorpay Checkout Script -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<script>
    function pricingCheckout() {
        return {
            loadingPlanId: null,
            isVerifying: false,
            verifyingMessage: '',
            errorMessage: '{{ session('error') ?? '' }}',
            successMessage: '{{ session('success') ?? '' }}',
            phone: '{{ auth()->check() ? (auth()->user()->phone ?? '') : '' }}',
            phones: {},

            async startCheckout(planId, planName) {
                this.errorMessage = '';
                this.loadingPlanId = planId;

                const phone = this.phone || this.phones[planId] || '';

                try {
                    const initiateUrl = '{{ route('payment.initiate', ':plan') }}'.replace(':plan', planId);
                    const response = await fetch(initiateUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ phone: phone })
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        this.errorMessage = data.message || 'Unable to start checkout. Please try again.';
                        this.loadingPlanId = null;
                        return;
                    }

                    const self = this;
                    const options = {
                        key: data.key,
                        amount: data.amount,
                        currency: data.currency,
                        name: data.name,
                        description: data.description,
                        order_id: data.order_id,
                        prefill: data.prefill,
                        theme: data.theme,
                        handler: async function (paymentResponse) {
                            self.loadingPlanId = null;
                            self.isVerifying = true;
                            self.verifyingMessage = 'Verifying your payment with Razorpay...';

                            try {
                                const verifyResponse = await fetch('{{ route('payment.verify') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        razorpay_order_id: paymentResponse.razorpay_order_id,
                                        razorpay_payment_id: paymentResponse.razorpay_payment_id,
                                        razorpay_signature: paymentResponse.razorpay_signature
                                    })
                                });

                                const verifyData = await verifyResponse.json();

                                if (verifyResponse.ok && verifyData.success) {
                                    self.verifyingMessage = 'Payment confirmed! Redirecting to your dashboard...';
                                    window.location.href = verifyData.redirect_url;
                                } else {
                                    self.isVerifying = false;
                                    self.errorMessage = verifyData.message || 'Payment verification failed. Please contact support.';
                                }
                            } catch (err) {
                                self.isVerifying = false;
                                self.errorMessage = 'Network error during verification. If money was deducted, your subscription will be activated automatically shortly.';
                            }
                        },
                        modal: {
                            ondismiss: function () {
                                self.loadingPlanId = null;
                            }
                        }
                    };

                    const rzp = new Razorpay(options);
                    rzp.on('payment.failed', function (failResponse) {
                        self.loadingPlanId = null;
                        self.errorMessage = 'Payment failed: ' + (failResponse.error?.description || 'Transaction declined.');
                    });

                    rzp.open();

                } catch (error) {
                    this.loadingPlanId = null;
                    this.errorMessage = 'An error occurred while connecting to the checkout service.';
                    console.error(error);
                }
            }
        };
    }
</script>
@endsection
