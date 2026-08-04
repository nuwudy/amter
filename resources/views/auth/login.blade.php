<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
    <!-- Background Blobs -->
    <div class="fixed inset-0 -z-10 h-full w-full">
        <div class="absolute top-20 left-20 w-96 h-96 bg-purple-600/20 rounded-full mix-blend-multiply filter blur-3xl animate-blob"></div>
        <div class="absolute top-40 right-20 w-96 h-96 bg-blue-600/20 rounded-full mix-blend-multiply filter blur-3xl animate-blob animation-delay-2000"></div>
        <div class="absolute -bottom-20 left-1/2 w-96 h-96 bg-pink-600/20 rounded-full mix-blend-multiply filter blur-3xl animate-blob animation-delay-4000"></div>
    </div>

    <div class="min-h-full flex flex-col justify-start pt-24 pb-12 sm:px-6 lg:px-8 relative">
        
        <div class="mx-auto w-full px-4" style="max-width: 440px;">
            
            <!-- Back Button -->
            <div class="mb-10 flex justify-start">
                <a href="{{ route('home') }}" class="group flex items-center gap-2 text-sm font-bold text-white/70 hover:text-white transition-all bg-white/10 hover:bg-white/20 px-5 py-2.5 rounded-full backdrop-blur-md border border-white/10 shadow-lg">
                    <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Home
                </a>
            </div>

            <!-- Clean Card -->
            <div class="bg-white/95 backdrop-blur-xl pt-12 px-8 shadow-[0_25px_50px_-12px_rgba(0,0,0,0.5)] rounded-[3rem] border border-white/20 relative overflow-hidden" style="padding-bottom: 7.5rem;">
                <!-- Subtle Top Gradient Bar -->
                <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-orange-500 via-pink-500 to-primary-500"></div>
                
                <div class="sm:mx-auto sm:w-full sm:max-w-md mb-10 text-center">
                    <a href="{{ route('home') }}" class="inline-block mb-6 group">
                        <img src="{{ asset('images/full-logo.png?v=1.0.2') }}" alt="Amter English" class="h-14 w-auto object-contain transition-transform duration-300 group-hover:scale-105">
                    </a>
                    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">
                        Welcome Back!
                    </h1>
                    <p class="mt-2 text-slate-500 font-medium text-sm italic">
                        "Your journey to fluency continues here."
                    </p>
                </div>

                @if(session('error'))
                    <div class="mb-6 p-4 rounded-2xl bg-red-50 border-2 border-red-100 text-red-700 text-sm font-semibold flex items-center gap-3">
                        <svg class="w-6 h-6 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <form class="space-y-6" action="{{ route('authenticate') }}" method="POST">
                    @csrf

                    <div class="space-y-5">
                        <!-- Email Input -->
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none pl-4">
                                <svg class="h-5 w-5 text-slate-400 group-focus-within:text-orange-500 transition-colors duration-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                                </svg>
                            </div>
                            <input id="email" name="email" type="email" autocomplete="email" required 
                                class="block w-full py-4 pl-12 pr-4 border-2 border-slate-100 bg-slate-50/50 rounded-2xl placeholder-slate-400 focus:outline-none focus:border-orange-500 focus:bg-white focus:ring-4 focus:ring-orange-500/10 transition-all duration-300 text-slate-900 font-semibold text-sm"
                                value="{{ old('email') }}" placeholder="Email Address">
                        </div>
                        @error('email')
                            <p class="mt-1 text-xs text-red-500 px-2 font-bold">{{ $message }}</p>
                        @enderror

                        <!-- Password Input -->
                        <div class="relative group" x-data="{ show: false }">
                            <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none pl-4 z-10">
                                <svg class="h-5 w-5 text-slate-400 group-focus-within:text-orange-500 transition-colors duration-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <input id="password" name="password" :type="show ? 'text' : 'password'" autocomplete="current-password" required 
                                class="block w-full py-4 pl-12 pr-12 border-2 border-slate-100 bg-slate-50/50 rounded-2xl placeholder-slate-400 focus:outline-none focus:border-orange-500 focus:bg-white focus:ring-4 focus:ring-orange-500/10 transition-all duration-300 text-slate-900 font-semibold text-sm"
                                placeholder="Password">
                            
                            <div class="absolute inset-y-0 right-0 flex items-center pr-2 z-20">
                                <button type="button" @click="show = !show" class="text-slate-400 hover:text-orange-500 focus:outline-none transition-colors p-2 rounded-full hover:bg-slate-100 italic text-xs font-bold uppercase">
                                    <span x-show="!show">Show</span>
                                    <span x-show="show">Hide</span>
                                </button>
                            </div>
                        </div>
                        @error('password')
                            <p class="mt-1 text-xs text-red-500 px-2 font-bold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between py-1">
                        <div class="flex items-center">
                            <input id="remember" name="remember" type="checkbox" 
                                class="h-5 w-5 text-orange-600 focus:ring-orange-500 border-slate-200 rounded-lg transition duration-200 cursor-pointer">
                            <label for="remember" class="ml-2.5 block text-sm font-bold text-slate-600 cursor-pointer select-none">Remember Me</label>
                        </div>
                        <a href="{{ route('filament.student.auth.password-reset.request') }}" class="text-xs font-bold text-orange-500 hover:text-orange-600 transition-colors hover:underline">
                            Forgot?
                        </a>
                    </div>

                    <div class="pt-2">
                        <button type="submit" 
                            class="w-full relative overflow-hidden group py-4.5 px-6 rounded-2xl bg-gradient-to-r from-orange-500 to-pink-600 shadow-[0_10px_20px_-5px_rgba(249,115,22,0.4)] text-lg font-extrabold text-white transition-all duration-300 hover:shadow-[0_15px_30px_-5px_rgba(249,115,22,0.6)] hover:-translate-y-0.5 active:scale-95">
                            <span class="relative z-10 flex items-center justify-center gap-3">
                                Unlock Your World
                                <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </span>
                            <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                        </button>
                    </div>
                </form>
                
                <div class="absolute bottom-0 left-0 w-full px-8 py-5 bg-gradient-to-r from-orange-50 to-pink-50 border-t border-orange-100">
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3 text-sm">
                        <div class="flex items-center gap-2">
                            <span class="bg-gradient-to-r from-orange-500 to-pink-500 text-white text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded-full shadow-sm animate-pulse">FREE</span>
                            <span class="text-slate-700 font-bold tracking-wide text-xs">Want more free classes?</span>
                        </div>
                        <a href="{{ route('filament.student.auth.register') }}" 
                           class="text-pink-600 font-extrabold hover:text-pink-700 transition-all flex items-center gap-1 group/link bg-white px-5 py-2 rounded-full shadow-sm hover:shadow-md border border-pink-200">
                            Become a Member!
                            <svg class="w-4 h-4 transition-transform group-hover/link:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Footer Small Print -->
            <p class="mt-10 text-center text-xs text-white/40 font-bold tracking-widest uppercase">
                &copy; {{ date('Y') }} Amter Learning Systems
            </p>
        </div>
    </div>
</body>
</html>
