@extends('layouts.public')
@section('title', 'Amter - Learn Spoken English (Malayalam to English)')
@section('meta_description', 'Master English with Amter. The best app for Malayalis to learn spoken English naturally using AI voice matching. No boring grammar, just results.')
@section('meta_keywords', 'learn english malayalam, spoken english app, malayalam to english, english speaking course, amter app')

@section('content')
<div class="relative overflow-hidden bg-slate-900" style="background: linear-gradient(135deg, #1e293b, #0f172a);">
    <!-- Blob Background -->
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[1000px] h-[600px] bg-gradient-to-r from-primary-500/10 to-blue-500/10 rounded-full blur-3xl opacity-50 -z-10 mt-[-200px]"></div>

    <!-- Hero Section -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-20 lg:pt-32 lg:pb-32">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            
            <!-- Text Content -->
            <div class="text-center lg:text-left z-10">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary-500/10 text-primary-400 font-bold text-sm mb-6 border border-primary-500/20">
                    <span class="w-2 h-2 rounded-full bg-primary-500 animate-pulse"></span>
                    മലയാളിയുടെ English Guru
                </div>
                <h1 class="text-5xl lg:text-7xl font-extrabold text-white tracking-tight mb-6 leading-tight">
                   Speak English<br/>
                    <span class="bg-clip-text text-transparent bg-gradient-to-r from-primary-500 to-cyan-300">via amter.</span>
                </h1>

                <!-- Buttons Moved Up -->
                <div class="flex flex-col sm:flex-row justify-center lg:justify-start gap-4 mb-8">
                    @auth
                        <a href="{{ route('filament.student.pages.dashboard') }}" class="bg-primary-500 text-slate-950 px-8 py-4 rounded-full font-bold text-lg hover:bg-primary-400 transition-all hover:scale-105 shadow-lg shadow-primary-500/20 animate-shine text-center">
                            Go to Classes
                        </a>
                    @else
                        <a href="{{ route('public.library') }}" class="bg-primary-500 text-slate-950 px-8 py-4 rounded-full font-bold text-lg hover:bg-primary-400 transition-all hover:scale-105 shadow-lg shadow-primary-500/20 animate-shine text-center">
                            Go to Classes
                        </a>
                    @endauth
                    <a href="{{ route('pricing') }}" class="inline-flex justify-center items-center bg-white/10 text-white border border-white/20 px-8 py-4 rounded-full font-bold text-lg hover:bg-white/20 transition-all hover:scale-105 shadow-lg text-center">
                        See Pricing
                    </a>
                </div>

                <!-- Subtitle / Details (Below Buttons) -->
                <div class="text-xl text-gray-300 mb-10 leading-relaxed max-w-2xl mx-auto lg:mx-0">
                    <p class="mb-4">
                        <span class="font-extrabold text-white relative">
                            English സംസാരിക്കാൻ
                            <span class="absolute -bottom-1 left-0 w-full h-2 bg-primary-500/20 -z-10"></span>
                        </span> വൈദഗ്ദ്ധ്യം നേടൂ: 
                        ചെറിയ ഓഡിയോ പാഠങ്ങളിലൂടെ, 
                        <span class="font-bold text-primary-400">വോയ്‌സ് മാച്ചിംഗ് സാങ്കേദ്യവിദ്യയിലൂടെ…</span>
                    </p>
                    <div class="mt-6 mb-8 p-5 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md shadow-2xl relative overflow-hidden group hover:border-primary-500/30 transition-all duration-300">
                        <!-- Shiny hover effect across card -->
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000 ease-out"></div>
                        
                        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4 relative z-10 text-center sm:text-left">
                            <!-- Animated Phone Icon Container -->
                            <div class="w-12 h-12 shrink-0 rounded-xl bg-gradient-to-tr from-primary-500 to-blue-600 flex items-center justify-center text-white shadow-[0_0_15px_rgba(6,182,212,0.3)] group-hover:shadow-[0_0_25px_rgba(6,182,212,0.6)] group-hover:scale-110 transition-all duration-300 animate-phone-pulse">
                                <!-- SVG Mobile Phone Icon -->
                                <svg class="w-6 h-6 animate-phone-wiggle" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <rect x="5" y="2" width="14" height="20" rx="3" />
                                    <path d="M12 18h.01" stroke-linecap="round" />
                                </svg>
                            </div>
                            
                            <!-- Text and Badges -->
                            <div class="flex-grow">
                                <h3 class="text-lg sm:text-xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-cyan-200 via-sky-200 to-blue-300 tracking-wide mb-3">
                                    The 100% self-learning system designed for Malayali
                                </h3>
                                <div class="flex flex-wrap gap-2 justify-center sm:justify-start">
                                    <span class="px-3.5 py-1 rounded-full bg-primary-500/10 text-primary-300 font-bold text-sm border border-primary-500/20 shadow-sm transition-all hover:bg-primary-500 hover:text-white cursor-default">കേൾക്കാം</span>
                                    <span class="px-3.5 py-1 rounded-full bg-blue-500/10 text-blue-300 font-bold text-sm border border-blue-500/20 shadow-sm transition-all hover:bg-blue-500 hover:text-white cursor-default">പഠിക്കാം</span>
                                    <span class="px-3.5 py-1 rounded-full bg-indigo-500/10 text-indigo-300 font-bold text-sm border border-indigo-500/20 shadow-sm transition-all hover:bg-indigo-500 hover:text-white cursor-default">സംസാരിക്കാം</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Visual Content -->
            <div class="relative mt-12 lg:mt-0 lg:block">
                <!-- Decorative Blobs -->
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[300px] h-[300px] lg:w-[600px] lg:h-[600px] bg-gradient-to-bl from-primary-500/15 to-blue-500/15 rounded-full blur-3xl -z-10"></div>
                
                <!-- Hero Image -->
                <div class="relative z-10 transform rotate-2 hover:rotate-0 transition-all duration-500">
                    <img src="{{ asset('images/hero-chips.jpg') }}" alt="Learn English like eating chips" class="rounded-[2.5rem] shadow-[0_20px_50px_rgba(0,0,0,0.1)] border-4 border-white w-full max-w-md mx-auto">
                    
                    <!-- Floating Badge -->
                    <div class="absolute -bottom-6 -right-6 bg-white p-4 rounded-2xl shadow-xl animate-bounce delay-1000 border border-gray-50">
                        <div class="flex items-center gap-3">
                            <span class="text-3xl">😋</span>
                            <div>
                                <p class="text-sm font-bold text-gray-900">Learn like eating chips</p>
                                <p class="text-xs text-gray-500">Just one bite at a time!</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Infinite Ticker Section -->
    <style>
        @keyframes phoneWiggle {
            0%, 100% { transform: rotate(0deg) scale(1); }
            10% { transform: rotate(-10deg) scale(1.08); }
            20% { transform: rotate(8deg) scale(1.08); }
            30% { transform: rotate(-6deg) scale(1.08); }
            40% { transform: rotate(4deg) scale(1.08); }
            50% { transform: rotate(-2deg) scale(1.08); }
            60% { transform: rotate(0deg) scale(1.08); }
        }
        .animate-phone-wiggle {
            animation: phoneWiggle 3.2s ease-in-out infinite;
            transform-origin: bottom center;
        }

        @keyframes phonePulse {
            0%, 100% { box-shadow: 0 0 15px rgba(236, 72, 153, 0.4); }
            50% { box-shadow: 0 0 35px rgba(236, 72, 153, 0.7), 0 0 15px rgba(168, 85, 247, 0.5); }
        }
        .animate-phone-pulse {
            animation: phonePulse 2.5s ease-in-out infinite;
        }

        @keyframes shine {
            0% { left: -100%; }
            20% { left: 100%; }
            100% { left: 100%; }
        }
        .animate-shine {
            position: relative;
            overflow: hidden;
        }
        .animate-shine::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 50%;
            height: 100%;
            background: linear-gradient(to right, transparent, rgba(255, 255, 255, 0.4), transparent);
            transform: skewX(-20deg);
            animation: shine 3s infinite;
        }

        .ticker-wrapper {
            mask-image: linear-gradient(to right, transparent, black 10%, black 90%, transparent);
            -webkit-mask-image: linear-gradient(to right, transparent, black 10%, black 90%, transparent);
        }
        .ticker-track {
            display: flex;
            width: max-content;
            gap: 2rem; /* space-x-8 */
        }
        .animate-marquee {
            animation: marquee 60s linear infinite;
        }
        .animate-marquee-reverse {
            animation: marquee-reverse 60s linear infinite; /* Slightly different speed for visual interest */
        }
        .ticker-item {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
        }
        .ticker-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1.25rem;
            background-color: #F9FAFB; /* bg-gray-50 */
            border: 1px solid #F3F4F6; /* border-gray-100 */
            border-radius: 9999px;
            font-weight: 500;
            color: #4B5563; /* text-gray-600 */
            transition: all 0.3s ease;
        }
        .ticker-pill:hover {
            color: #00c2e8; /* primary-500 */
            border-color: #cffafe; /* primary-100 */
            background-color: #ecfeff; /* primary-50 */
            transform: scale(1.05);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .pill-dot {
            width: 0.375rem;
            height: 0.375rem;
            border-radius: 50%;
            background-color: #00c2e8; /* primary-500 */
        }
        
        @keyframes marquee {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        @keyframes marquee-reverse {
            0% { transform: translateX(-50%); }
            100% { transform: translateX(0); }
        }
        
        /* Pause on hover for readability */
        .ticker-group:hover .animate-marquee,
        .ticker-group:hover .animate-marquee-reverse {
            animation-play-state: paused;
        }
    </style>

    <div class="py-12 bg-gray-800 border-y border-gray-700 ticker-group overflow-hidden" style="backdrop-filter: blur(8px);">
        <!-- Row 1: Method & Tech (Left to Right) -->
        <div class="mb-6 ticker-wrapper relative flex overflow-hidden">
             <!-- Content Doubled for Infinite Loop -->
             <div class="ticker-track animate-marquee">
                <!-- Set 1 -->
                @foreach([
                    '24/7 Access • Learn at your pace',
                    'No Classrooms • No Boring Lectures',
                    'Zero Teachers • Zero Pressure',
                    'Anytime, Anywhere • Even on your commute',
                    'Fits your busy life • Pocket-sized learning',
                    '60-Second Bite-Sized Units',
                    'Real Video Clips • No Cartoons',
                    'AI-Powered Voice Match',
                    'Gamified Rewards • Earn 10 XP Daily',
                    'Watch. Listen. Speak.',
                    'Learn like a Native'
                ] as $item)
                    <div class="ticker-item">
                        <span class="ticker-pill shadow-sm">
                            <span class="pill-dot"></span>
                            {{ $item }}
                        </span>
                    </div>
                @endforeach
                
                <!-- Set 2 (Duplicate) -->
                @foreach([
                    '24/7 Access • Learn at your pace',
                    'No Classrooms • No Boring Lectures',
                    'Zero Teachers • Zero Pressure',
                    'Anytime, Anywhere • Even on your commute',
                    'Fits your busy life • Pocket-sized learning',
                    '60-Second Bite-Sized Units',
                    'Real Video Clips • No Cartoons',
                    'AI-Powered Voice Match',
                    'Gamified Rewards • Earn 10 XP Daily',
                    'Watch. Listen. Speak.',
                    'Learn like a Native'
                ] as $item)
                    <div class="ticker-item">
                        <span class="ticker-pill shadow-sm">
                            <span class="pill-dot"></span>
                            {{ $item }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Row 2: Malayali Specific & Trust (Right to Left) -->
        <div class="ticker-wrapper relative flex overflow-hidden">
            <div class="ticker-track animate-marquee-reverse">
                <!-- Set 1 -->
                @foreach([
                    'മലയാളികൾക്കായി പ്രത്യേകം (Specially for Malayalis)',
                    'Malayalam to English Mastery',
                    'NRI Survival English',
                    'Designed for Kerala Professionals',
                    'മലയാളത്തിലൂടെ ഇംഗ്ലീഷ് പഠിക്കാം',
                    'Confidence, Guaranteed',
                    'Stop Translating, Start Speaking',
                    'Unlock Your Global Career',
                    '20 Years of Expertise',
                    'Ad-Free Experience'
                ] as $item)
                    <div class="ticker-item">
                        <span class="ticker-pill shadow-sm hover:!bg-blue-50 hover:!text-blue-600 hover:!border-blue-200">
                             <!-- Blue accent for this row on hover -->
                             <span class="pill-dot !bg-blue-500"></span>
                            {{ $item }}
                        </span>
                    </div>
                @endforeach

                 <!-- Set 2 (Duplicate) -->
                @foreach([
                    'മലയാളികൾക്കായി പ്രത്യേകം (Specially for Malayalis)',
                    'Malayalam to English Mastery',
                    'NRI Survival English',
                    'Designed for Kerala Professionals',
                    'മലയാളത്തിലൂടെ ഇംഗ്ലീഷ് പഠിക്കാം',
                    'Confidence, Guaranteed',
                    'Stop Translating, Start Speaking',
                    'Unlock Your Global Career',
                    '20 Years of Expertise',
                    'Ad-Free Experience'
                ] as $item)
                    <div class="ticker-item">
                        <span class="ticker-pill shadow-sm hover:!bg-blue-50 hover:!text-blue-600 hover:!border-blue-200">
                            <span class="pill-dot !bg-blue-500"></span>
                            {{ $item }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>



    <!-- Why Section -->
    <section class="bg-slate-900 py-24 sm:py-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-white sm:text-4xl">Why Learners Choose Amter</h2>
                <p class="mt-4 text-lg text-gray-300">A curriculum designed for real results, not just passing tests.</p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-3 mb-16">
                <!-- Card 1: Method & AI -->
                <div class="bg-white/5 rounded-3xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all duration-300 border border-gray-600 hover:-translate-y-1 backdrop-blur" style="background: rgba(255,255,255,0.07);">
                    <div class="h-48 w-full mb-6 overflow-hidden rounded-2xl">
                        <img src="{{ asset('images/why-practical.jpg') }}" alt="Real Conversational Practice" class="w-full h-full object-cover">
                    </div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-purple-50 text-purple-600 font-bold text-xs mb-4">
                        <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                        Method & Tech
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Stop Translating, Start Speaking</h3>
                    <p class="text-gray-300 leading-relaxed">Directly learn to think in English with Real Video Clips and AI-Powered Voice Match. No more translating in your head.</p>
                </div>

                <!-- Card 2: Malayali Specific -->
                <div class="bg-white/5 rounded-3xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all duration-300 border border-gray-600 hover:-translate-y-1 backdrop-blur" style="background: rgba(255,255,255,0.07);">
                    <div class="h-48 w-full mb-6 overflow-hidden rounded-2xl">
                        <img src="{{ asset('images/why-fast.jpg') }}" alt="Designed for Kerala" class="w-full h-full object-cover">
                    </div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 text-blue-600 font-bold text-xs mb-4">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                        Malayali Focus
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Designed for Kerala Professionals</h3>
                    <p class="text-gray-300 leading-relaxed">A curriculum crafted specifically for Malayalis. Master 'NRI Survival English' and unlock your global career.</p>
                </div>

                <!-- Card 3: Ease & Access -->
                <div class="bg-white/5 rounded-3xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all duration-300 border border-gray-600 hover:-translate-y-1 backdrop-blur" style="background: rgba(255,255,255,0.07);">
                    <div class="h-48 w-full mb-6 overflow-hidden rounded-2xl">
                        <img src="{{ asset('images/why-students.jpg') }}" alt="Zero Pressure Learning" class="w-full h-full object-cover">
                    </div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-green-50 text-green-600 font-bold text-xs mb-4">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                        Ease & Access
                    </div>
                    <h3 class="text-xl font-bold text-white mb-3">Zero Pressure, 100% Results</h3>
                    <p class="text-gray-300 leading-relaxed">No boring lectures or teachers. Learn at your own pace with 60-second bite-sized units that fit your busy life.</p>
                </div>
            </div>

            <div class="text-center">
                @auth
                    <a href="{{ route('filament.student.pages.dashboard') }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-primary-500 to-blue-600 text-white px-8 py-4 rounded-full font-bold text-lg hover:scale-105 shadow-lg shadow-primary-500/20 transition-all">
                        Start Learning Now
                        <svg class="w-5 h-5 shrink-0" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                    </a>
                @else
                    <a href="{{ route('public.library') }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-primary-500 to-blue-600 text-white px-8 py-4 rounded-full font-bold text-lg hover:scale-105 shadow-lg shadow-primary-500/20 transition-all">
                        Start Learning Now
                        <svg class="w-5 h-5 shrink-0" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                    </a>
                @endauth
            </div>
        </div>
    </section>

    <!-- Student Reviews Section -->
    <section class="bg-gray-50/50 py-24 border-y border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-16">
                <div class="max-w-2xl">
                    <h2 class="text-3xl font-bold text-gray-900 sm:text-4xl tracking-tight mb-4">Loved by Hundreds of Students</h2>
                    <p class="text-lg text-gray-600">Real stories from real learners who transformed their fluency with Amter.</p>
                </div>
                
                @auth
                    <button x-data @click="$dispatch('open-modal', 'submit-review')" class="inline-flex items-center gap-2 bg-white text-gray-900 px-6 py-3 rounded-full font-bold shadow-sm border border-gray-200 hover:bg-gray-50 transition-all hover:scale-105">
                        <svg class="w-5 h-5 text-primary-500 shrink-0" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        Share Your Story
                    </button>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 bg-white text-gray-900 px-6 py-3 rounded-full font-bold shadow-sm border border-gray-200 hover:bg-gray-50 transition-all hover:scale-105">
                        <svg class="w-5 h-5 text-gray-400 shrink-0" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        Login to Review
                    </a>
                @endauth
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse($reviews as $review)
                    <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-gray-100 flex flex-col h-full transform transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                        <!-- Stars -->
                        <div class="flex gap-1 mb-6">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-5 h-5 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-200' }} shrink-0" width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            @endfor
                        </div>

                        <!-- Comment -->
                        <p class="text-gray-600 italic mb-8 flex-grow leading-relaxed">
                            "{{ $review->comment }}"
                        </p>

                        <!-- Student Info -->
                        <div class="flex items-center gap-4 mt-auto pt-6 border-t border-gray-50">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-primary-100 to-pink-100 flex items-center justify-center text-primary-600 font-bold text-lg">
                                {{ substr($review->user->name, 0, 1) }}
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 leading-none mb-1">{{ $review->user->name }}</h4>
                                <p class="text-xs text-gray-400">Verified Student</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <!-- Placeholder reviews if none exist yet -->
                    @foreach([
                        ['name' => 'Rahul K.', 'text' => "Amter transformed my confidence. The bite-sized lessons are so easy to follow during my commute.", 'rating' => 5],
                        ['name' => 'Sara M.', 'text' => "Finally, an app that understands Malayalis! The voice matching is like having a private tutor 24/7.", 'rating' => 5],
                        ['name' => 'Anandhu V.', 'text' => "Best English learning app. No boring grammar, just practical speaking practice. Highly recommended.", 'rating' => 5],
                    ] as $mock)
                    <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 opacity-60">
                        <div class="flex gap-1 mb-6">
                            @for($i = 0; $i < 5; $i++) <svg class="w-5 h-5 text-yellow-400 shrink-0" width="20" height="20" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg> @endfor
                        </div>
                        <p class="text-gray-400 italic mb-8">"{{ $mock['text'] }}"</p>
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 font-bold">{{ substr($mock['name'], 0, 1) }}</div>
                            <h4 class="font-bold text-gray-300">{{ $mock['name'] }}</h4>
                        </div>
                    </div>
                    @endforeach
                @endforelse
            </div>
        </div>
    </section>
    <!-- FAQ Section -->
    <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
        <div class="text-center mb-16">
            <h2 class="text-3xl font-bold text-gray-900 sm:text-4xl">Common Questions</h2>
            <p class="mt-4 text-lg text-gray-600">Everything you need to know about the Amter way of learning.</p>
        </div>

        <div class="space-y-4">
            @php
                $faqs = [
                    [
                        'question' => "What makes Amter different from traditional Spoken English classes?",
                        'answer' => "We ditched the boring grammar drills for a natural approach. Traditional classes focus on 'Grammar Translation' which makes you translate in your head. Amter focuses on 'Natural Acquisition'—listening and speaking through bite-sized audio lessons and advanced voice matching technology. It's just like learning your first language."
                    ],
                    [
                        'question' => "Is this course suitable for beginners with weak English?",
                        'answer' => "Absolutely. We have courses specifically designed for Malayalis (Malayalam Speakers) that start from the very basics. We use Malayalam to explain concepts initially, gradually transitioning you to full English mastery."
                    ],
                    [
                        'question' => "How does the 'Voice Match' technology help me speak better?",
                        'answer' => "Our AI-powered Voice Match technology listens to you speak and compares it with native speakers. It provides instant feedback, helping you correct your pronunciation and intonation immediately, effectively acting as a personal speech coach."
                    ],
                    [
                        'question' => "How much time do I need to spend daily?",
                        'answer' => "Consistency is key. Our 'One Bite at a Time' philosophy allows you to make significant progress with just 10-20 minutes a day. The units are bite-sized (60-second clips), fitting perfectly into a busy schedule."
                    ],
                    [
                        'question' => "Is there a personal tutor or live classes?",
                        'answer' => "No, Amter is a 100% self-paced learning platform. There are no awkward classrooms, scheduling conflicts, or pressure from teachers. You are in complete control, with our AI tools supporting you every step of the way."
                    ],
                    [
                        'question' => "Can I access the course on my mobile phone?",
                        'answer' => "Yes! Amter is a fully responsive web application. You can access your lessons on any device—smartphone, tablet, or laptop—anytime, anywhere. Perfect for learning during your commute or breaks."
                    ],
                    [
                        'question' => "Is there a free trial available?",
                        'answer' => "Yes, we offer 'Free Preview' access for our courses. You can try out the initial units and experience the learning method first-hand without any subscription or credit card requirement."
                    ],
                    [
                        'question' => "Will this help me with job interviews and expanding my career?",
                        'answer' => "Definitely. Fluency in English is a major confidence booster and a key requirement for many corporate jobs and NRI opportunities. Our 'NRI Survival English' and professional modules are crafted to help you unlock your global career potential."
                    ]
                ];
            @endphp

            @foreach($faqs as $index => $faq)
            <div x-data="{ open: false }" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden transition-all duration-300 hover:shadow-md">
                <button @click="open = !open" class="w-full px-8 py-6 text-left flex items-center justify-between focus:outline-none">
                    <span class="text-lg font-bold text-gray-900">{{ $faq['question'] }}</span>
                    <span class="transform transition-transform duration-300" :class="{'rotate-180': open}">
                        <svg class="w-6 h-6 text-primary-500 shrink-0" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </span>
                </button>
                <div x-show="open" x-collapse style="display: none;" class="px-8 pb-8 text-gray-600 leading-relaxed">
                    {{ $faq['answer'] }}
                </div>
            </div>
            @endforeach
        </div>
    </section>


    <!-- Review Submission Modal -->
    @auth
    <div x-data="{ open: false }" 
         x-init="if (window.location.search.includes('review=1')) { open = true; window.history.replaceState({}, document.title, window.location.pathname); }"
         @open-modal.window="if ($event.detail === 'submit-review') open = true"
         x-show="open" 
         class="fixed inset-0 z-[100] overflow-y-auto" 
         style="display: none;"
         x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="open" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0" 
                 @click="open = false"
                 class="fixed inset-0 transition-opacity bg-gray-900/60 backdrop-blur-sm" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="open" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 class="inline-block px-8 py-10 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-[2.5rem] shadow-2xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
                
                <div class="absolute top-6 right-6">
                    <button @click="open = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6 shrink-0" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary-50 text-primary-600 mb-4">
                        <svg class="w-8 h-8 shrink-0" width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.382-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" /></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900">Share Your Journey</h3>
                    <p class="mt-2 text-gray-600">How has Amter helped you speak better English?</p>
                </div>

                <form action="{{ route('reviews.store') }}" method="POST" class="space-y-6">
                    @csrf
                    <div x-data="{ rating: 5 }">
                        <label class="block text-sm font-bold text-gray-700 mb-2">How much did you like it?</label>
                        <div class="flex gap-2">
                            <template x-for="i in 5">
                                <button type="button" @click="rating = i" class="focus:outline-none transition-transform hover:scale-110">
                                    <svg class="w-10 h-10 shrink-0" width="40" height="40" :class="i <= rating ? 'text-yellow-400' : 'text-gray-200'" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                </button>
                            </template>
                        </div>
                        <input type="hidden" name="rating" :value="rating">
                    </div>

                    <div>
                        <label for="comment" class="block text-sm font-bold text-gray-700 mb-2">Your Story</label>
                        <textarea id="comment" name="comment" rows="4" required minlength="10"
                                  class="w-full px-5 py-4 bg-gray-50 border-2 border-gray-100 rounded-2xl focus:outline-none focus:border-primary-500 focus:bg-white transition-all text-gray-900 placeholder-gray-400"
                                  placeholder="What was your favorite part of learning with Amter?"></textarea>
                    </div>

                    <button type="submit" class="w-full bg-gradient-to-r from-primary-500 to-blue-600 text-white py-4 rounded-2xl font-bold text-lg shadow-lg hover:shadow-xl hover:scale-[1.02] transition-all duration-300">
                        Post My Review
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endauth

</div>
@endsection
