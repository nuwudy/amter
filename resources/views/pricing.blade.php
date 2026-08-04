@extends('layouts.public', ['nav_theme' => 'dark'])
@section('title', 'Pricing Plans - Affordable Spoken English Courses')
@section('meta_description', 'Choose the best plan for your English learning journey. Simple, transparent pricing with no hidden fees.')

@section('content')
<div class="bg-slate-900 min-h-screen py-16 sm:py-32" style="background: linear-gradient(135deg, #1e293b, #0f172a);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center max-w-2xl mx-auto mb-10 sm:mb-16">
            <h2 class="text-3xl font-extrabold text-white sm:text-4xl mb-4">
                Simple, Transparent Pricing
            </h2>
            <p class="text-xl text-gray-300">
                Choose the plan that fits your learning pace. No hidden fees, no auto-renewals.
            </p>
        </div>

        @if(session('error'))
            <div class="max-w-4xl mx-auto mb-8">
                <div class="bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-xl flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif
        
        @if ($errors->any())
            <div class="max-w-4xl mx-auto mb-8">
                <div class="bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-xl">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8 max-w-5xl mx-auto relative z-10">
            @foreach($plans as $plan)
            <div class="relative bg-white/5 rounded-3xl shadow-lg border border-gray-600 backdrop-blur hover:shadow-xl transition-all duration-300 flex flex-col overflow-hidden group">
                <!-- Highlight Ribbon for Best Value -->
                @if($plan->is_best_value)
                <div class="absolute top-0 right-0 bg-gradient-to-r from-primary-500 to-pink-500 text-white text-xs font-bold px-3 py-1 rounded-bl-xl shadow-sm">
                    BEST VALUE
                </div>
                @endif

                <div class="p-6 sm:p-8 flex-1">
                    <h3 class="text-2xl font-bold text-white mb-2">{{ $plan->name }}</h3>
                    <div class="flex items-baseline gap-1 my-4 sm:my-6">
                        <span class="text-4xl font-extrabold text-white">₹{{ number_format($plan->price, 0) }}</span>
                        <span class="text-gray-400 font-medium">/ {{ $plan->duration_days }} days</span>
                    </div>
                    <p class="text-gray-300 leading-relaxed mb-4 sm:mb-6">
                        Full access to all language courses, voice matching, and progress tracking for {{ $plan->duration_days }} days.
                    </p>
                    <ul class="space-y-2.5 sm:space-y-3 mb-6 sm:mb-8">
                        <li class="flex items-center gap-3 text-gray-300">
                            <svg class="w-5 h-5 text-green-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Unlimited Access</span>
                        </li>
                        <li class="flex items-center gap-3 text-gray-300">
                            <svg class="w-5 h-5 text-green-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Voice Matching </span>
                        </li>
                        <li class="flex items-center gap-3 text-gray-300">
                            <svg class="w-5 h-5 text-green-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Certificate of Completion</span>
                        </li>
                    </ul>
                </div>

                <div class="p-6 sm:p-8 bg-white/5 border-t border-gray-600">
                    @auth
                        <form action="{{ route('checkout', $plan) }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label for="phone_{{ $plan->id }}" class="block text-sm font-medium text-gray-300 mb-1">Mobile Number</label>
                                <input type="tel" name="phone" id="phone_{{ $plan->id }}" 
                                    placeholder="10-digit number" 
                                    maxlength="10" 
                                    required 
                                    value="{{ auth()->user()->phone ?? '' }}"
                                    class="w-full px-4 py-2 border border-gray-600 bg-black/20 text-white rounded-lg focus:ring-2 focus:ring-green-400 focus:border-green-400 outline-none transition-all placeholder-gray-500"
                                    pattern="[0-9]{10}"
                                    title="Please enter a valid 10-digit mobile number">
                            </div>
                            <button type="submit" class="w-full bg-gradient-to-r from-green-400 to-green-600 text-white font-bold py-3 px-4 rounded-xl hover:scale-105 shadow-xl transition-all duration-200">
                                Buy Now
                            </button>
                        </form>
                    @else
                        <div class="text-center">
                            <p class="text-sm text-gray-400 mb-4">Login or Register to purchase</p>
                            <a href="{{ route('login') }}" class="block w-full bg-white/10 text-white border border-white/20 font-bold py-3 px-4 rounded-xl hover:bg-white/20 transition-all duration-200 backdrop-blur">
                                Login to Subscribe
                            </a>
                        </div>
                    @endauth
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-10 sm:mt-16 text-center">
            <p class="text-gray-400">Need help with payments? <a href="{{ route('contact') }}" class="text-green-400 font-medium hover:underline">Contact Support</a></p>
        </div>
    </div>
    
    <!-- Background Blobs (Optional) -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none z-0">
        <div class="absolute top-[-20%] left-[-10%] w-[50vw] h-[50vw] rounded-full bg-gradient-to-br from-green-500/20 to-purple-600/10 blur-3xl mix-blend-screen"></div>
        <div class="absolute bottom-[-20%] right-[-10%] w-[40vw] h-[40vw] rounded-full bg-gradient-to-tl from-pink-500/20 to-orange-600/10 blur-3xl mix-blend-screen"></div>
    </div>
</div>
@endsection
