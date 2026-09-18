<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>കൂടുതൽ ഫ്രീ ക്ലാസ്സുകൾക്കായി റജിസ്റ്റർ ചെയ്യൂ - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #0f172a;
            background-image: 
                radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), 
                radial-gradient(at 50% 0%, hsla(225,39%,30%,1) 0, transparent 50%), 
                radial-gradient(at 100% 0%, hsla(339,49%,30%,1) 0, transparent 50%);
            background-attachment: fixed;
            background-size: cover;
        }
        .animate-blob {
            animation: blob 7s infinite;
        }
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        .animation-delay-2000 { animation-delay: 2s; }
        .animation-delay-4000 { animation-delay: 4s; }
    </style>
</head>
<body class="h-full antialiased text-slate-600 overflow-x-hidden">
    <!-- Ambient Background Glows -->
    <div class="fixed inset-0 -z-10 h-full w-full pointer-events-none">
        <div class="absolute top-10 left-10 w-80 h-80 bg-purple-600/25 rounded-full blur-3xl animate-blob"></div>
        <div class="absolute top-32 right-10 w-80 h-80 bg-cyan-500/25 rounded-full blur-3xl animate-blob animation-delay-2000"></div>
        <div class="absolute bottom-10 left-1/3 w-80 h-80 bg-pink-500/20 rounded-full blur-3xl animate-blob animation-delay-4000"></div>
    </div>

    <div class="min-h-full flex flex-col justify-start pt-16 pb-12 sm:px-6 lg:px-8 relative">
        <div class="mx-auto w-full px-4" style="max-width: 440px;">
        
        <!-- Top Navigation -->
        <div class="mb-5 flex justify-between items-center">
            <a href="{{ route('public.library') }}" class="inline-flex items-center gap-2 text-xs font-bold text-white/80 hover:text-white transition-all bg-white/10 hover:bg-white/20 px-4 py-2 rounded-full backdrop-blur-md border border-white/10 shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Classes
            </a>

            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-[11px] font-black">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                100% Free Access
            </span>
        </div>

        <!-- Main Card -->
        <div class="bg-white/95 backdrop-blur-2xl rounded-[2.5rem] shadow-[0_20px_60px_-15px_rgba(0,0,0,0.6)] border border-white/40 overflow-hidden relative" style="padding: 2rem 1.75rem 2rem 1.75rem;">
            
            <!-- Top Gradient Bar -->
            <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-orange-400 via-pink-500 to-primary-500"></div>

            <!-- Cute Logo & Header -->
            <div class="text-center mb-5">
                <a href="{{ route('home') }}" class="inline-block mb-3 group">
                    <img src="{{ asset('images/full-logo.png?v=1.0.2') }}" alt="Amter English" class="h-11 w-auto object-contain mx-auto transition-transform duration-300 group-hover:scale-105">
                </a>

                <div class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-orange-100 text-orange-700 text-[11px] font-black tracking-wide mb-2">
                    <span>🎁</span> സൗജന്യ അക്കൗണ്ട് (Free)
                </div>

                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight leading-snug">
                    കൂടുതൽ ഫ്രീ ക്ലാസ്സുകൾക്കായി<br/>
                    <span class="bg-gradient-to-r from-orange-500 via-pink-500 to-primary-600 bg-clip-text text-transparent">റജിസ്റ്റർ ചെയ്യൂ</span>
                </h1>
                
                <p class="text-xs sm:text-sm font-bold text-slate-500 mt-1">
                    Register for more free classes
                </p>
            </div>

            <!-- Cute Segmented Tab Switcher (Rock-solid layout) -->
            <div style="display: flex; gap: 4px; padding: 4px; background-color: #f1f5f9; border-radius: 1rem; border: 1px solid #e2e8f0; margin-bottom: 1.25rem;">
                <a href="{{ route('login') }}" style="flex: 1; text-align: center; padding: 0.5rem 0.25rem; font-size: 12px; font-weight: 700; color: #64748b; text-decoration: none; border-radius: 0.75rem; transition: all 0.2s;" onmouseover="this.style.color='#0f172a'" onmouseout="this.style.color='#64748b'">
                    🔑 Login
                </a>
                <span style="flex: 1; text-align: center; padding: 0.5rem 0.25rem; font-size: 12px; font-weight: 800; color: #ffffff; background: linear-gradient(135deg, #f97316 0%, #ec4899 100%); border-radius: 0.75rem; box-shadow: 0 2px 6px rgba(249,115,22,0.3);">
                    ✨ Register
                </span>
            </div>

            @if($errors->any())
                <div class="mb-4 p-3.5 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-xs font-medium">
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="space-y-3.5" action="{{ route('register.submit') }}" method="POST">
                @csrf

                <!-- Name Input -->
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none pl-3.5">
                        <svg class="h-4 w-4 text-slate-400 group-focus-within:text-orange-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <input id="name" name="name" type="text" autocomplete="name" required 
                        class="block w-full py-3 pl-10 pr-4 border border-slate-200 bg-slate-50/60 rounded-xl placeholder-slate-400 focus:outline-none focus:border-orange-500 focus:bg-white focus:ring-2 focus:ring-orange-500/15 transition-all text-slate-900 font-semibold text-xs sm:text-sm"
                        value="{{ old('name') }}" placeholder="നിങ്ങളുടെ പേര് (Full Name)">
                </div>

                <!-- Email Input -->
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none pl-3.5">
                        <svg class="h-4 w-4 text-slate-400 group-focus-within:text-orange-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <input id="email" name="email" type="email" autocomplete="email" required 
                        class="block w-full py-3 pl-10 pr-4 border border-slate-200 bg-slate-50/60 rounded-xl placeholder-slate-400 focus:outline-none focus:border-orange-500 focus:bg-white focus:ring-2 focus:ring-orange-500/15 transition-all text-slate-900 font-semibold text-xs sm:text-sm"
                        value="{{ old('email') }}" placeholder="Email Address">
                </div>

                <!-- Password Input (Compact & No SHOW collision) -->
                <div class="relative group" x-data="{ show: false }">
                    <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none pl-3.5 z-10">
                        <svg class="h-4 w-4 text-slate-400 group-focus-within:text-orange-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <input id="password" name="password" :type="show ? 'text' : 'password'" autocomplete="new-password" required 
                        class="block w-full py-3 pl-10 pr-16 border border-slate-200 bg-slate-50/60 rounded-xl placeholder-slate-400 focus:outline-none focus:border-orange-500 focus:bg-white focus:ring-2 focus:ring-orange-500/15 transition-all text-slate-900 font-semibold text-xs sm:text-sm"
                        placeholder="Create Password">
                    
                    <div class="absolute inset-y-0 right-0 flex items-center pr-2 z-20">
                        <button type="button" @click="show = !show" class="text-slate-400 hover:text-orange-500 focus:outline-none transition-colors px-2 py-1 rounded text-[11px] font-bold uppercase tracking-wider">
                            <span x-show="!show">SHOW</span>
                            <span x-show="show">HIDE</span>
                        </button>
                    </div>
                </div>

                <!-- Confirm Password Input -->
                <div class="relative group" x-data="{ show: false }">
                    <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none pl-3.5 z-10">
                        <svg class="h-4 w-4 text-slate-400 group-focus-within:text-orange-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <input id="password_confirmation" name="password_confirmation" :type="show ? 'text' : 'password'" autocomplete="new-password" required 
                        class="block w-full py-3 pl-10 pr-16 border border-slate-200 bg-slate-50/60 rounded-xl placeholder-slate-400 focus:outline-none focus:border-orange-500 focus:bg-white focus:ring-2 focus:ring-orange-500/15 transition-all text-slate-900 font-semibold text-xs sm:text-sm"
                        placeholder="Confirm Password">
                    
                    <div class="absolute inset-y-0 right-0 flex items-center pr-2 z-20">
                        <button type="button" @click="show = !show" class="text-slate-400 hover:text-orange-500 focus:outline-none transition-colors px-2 py-1 rounded text-[11px] font-bold uppercase tracking-wider">
                            <span x-show="!show">SHOW</span>
                            <span x-show="show">HIDE</span>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit" 
                        class="w-full relative overflow-hidden group py-3.5 px-6 rounded-2xl bg-gradient-to-r from-orange-500 via-pink-500 to-purple-600 shadow-[0_8px_20px_-4px_rgba(249,115,22,0.45)] text-sm sm:text-base font-extrabold text-white transition-all duration-300 hover:shadow-[0_12px_25px_-4px_rgba(249,115,22,0.6)] hover:scale-[1.01] active:scale-95 flex items-center justify-center gap-2">
                        <span>സൗജന്യമായി റജിസ്റ്റർ ചെയ്യൂ ✨</span>
                        <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </div>
            </form>
            
            <!-- Bottom Helper -->
            <div class="mt-5 pt-4 border-t border-slate-100 text-center">
                <p class="text-xs text-slate-500 font-medium">
                    ഇതിനകം അക്കൗണ്ട് ഉണ്ടോ? 
                    <a href="{{ route('login') }}" class="text-orange-600 font-extrabold hover:underline ml-1">
                        Login ചെയ്യുക →
                    </a>
                </p>
            </div>
        </div>

        <!-- Friendly Footer Note -->
        <p class="mt-5 text-center text-xs text-white/50 font-medium">
            🔒 100% സുരക്ഷിതം • ക്രെഡിറ്റ് കാർഡോ പേയ്‌മെന്റോ ആവശ്യമില്ല
        </p>
        </div>
    </div>
</body>
</html>
