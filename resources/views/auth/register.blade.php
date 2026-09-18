<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
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
    <!-- Background Blobs -->
    <div class="fixed inset-0 -z-10 h-full w-full">
        <div class="absolute top-20 left-20 w-96 h-96 bg-purple-600/20 rounded-full mix-blend-multiply filter blur-3xl animate-blob"></div>
        <div class="absolute top-40 right-20 w-96 h-96 bg-blue-600/20 rounded-full mix-blend-multiply filter blur-3xl animate-blob animation-delay-2000"></div>
        <div class="absolute -bottom-20 left-1/2 w-96 h-96 bg-pink-600/20 rounded-full mix-blend-multiply filter blur-3xl animate-blob animation-delay-4000"></div>
    </div>

    <div class="min-h-full flex flex-col justify-start pt-16 pb-12 sm:px-6 lg:px-8 relative">
        
        <div class="mx-auto w-full px-4" style="max-width: 460px;">
            
            <!-- Back Button -->
            <div class="mb-6 flex justify-between items-center">
                <a href="{{ route('public.library') }}" class="group flex items-center gap-2 text-sm font-bold text-white/70 hover:text-white transition-all bg-white/10 hover:bg-white/20 px-5 py-2.5 rounded-full backdrop-blur-md border border-white/10 shadow-lg">
                    <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Classes
                </a>

                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-xs font-black">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    100% Free Access
                </span>
            </div>

            <!-- Clean Card -->
            <div class="bg-white/95 backdrop-blur-xl pt-10 px-8 pb-10 shadow-[0_25px_50px_-12px_rgba(0,0,0,0.5)] rounded-[3rem] border border-white/20 relative overflow-hidden">
                <!-- Subtle Top Gradient Bar -->
                <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-emerald-400 via-primary-500 to-pink-500"></div>
                
                <!-- Tab Switcher: Login / Register -->
                <div class="flex items-center p-1 bg-slate-100 rounded-2xl mb-8 border border-slate-200">
                    <a href="{{ route('login') }}" class="w-1/2 py-2.5 text-center text-xs sm:text-sm font-bold text-slate-500 hover:text-slate-900 rounded-xl transition-all">
                        Login / ലോഗിൻ
                    </a>
                    <span class="w-1/2 py-2.5 text-center text-xs sm:text-sm font-extrabold text-white bg-gradient-to-r from-primary-500 to-cyan-500 rounded-xl shadow-md">
                        Register / രജിസ്റ്റർ
                    </span>
                </div>

                <div class="sm:mx-auto sm:w-full sm:max-w-md mb-8 text-center">
                    <a href="{{ route('home') }}" class="inline-block mb-4 group">
                        <img src="{{ asset('images/full-logo.png?v=1.0.2') }}" alt="Amter English" class="h-12 w-auto object-contain transition-transform duration-300 group-hover:scale-105 mx-auto">
                    </a>
                    
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-tight">
                        കൂടുതൽ ഫ്രീ ക്ലാസ്സുകൾക്കായി റജിസ്റ്റർ ചെയ്യൂ
                    </h1>
                    <p class="mt-2 text-primary-600 font-bold text-sm sm:text-base">
                        Register for more free classes
                    </p>
                    <p class="mt-1 text-slate-500 text-xs">
                        ലളിതമായി രജിസ്റ്റർ ചെയ്ത് കൂടുതൽ ഓഡിയോ പാഠങ്ങൾ സൗജന്യമായി ആസ്വദിക്കൂ.
                    </p>
                </div>

                @if($errors->any())
                    <div class="mb-6 p-4 rounded-2xl bg-red-50 border-2 border-red-100 text-red-700 text-xs font-semibold">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form class="space-y-4" action="{{ route('register.submit') }}" method="POST">
                    @csrf

                    <!-- Name Input -->
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none pl-4">
                            <svg class="h-5 w-5 text-slate-400 group-focus-within:text-primary-500 transition-colors duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <input id="name" name="name" type="text" autocomplete="name" required 
                            class="block w-full py-3.5 pl-12 pr-4 border-2 border-slate-100 bg-slate-50/50 rounded-2xl placeholder-slate-400 focus:outline-none focus:border-primary-500 focus:bg-white focus:ring-4 focus:ring-primary-500/10 transition-all duration-300 text-slate-900 font-semibold text-sm"
                            value="{{ old('name') }}" placeholder="നിങ്ങളുടെ പേര് (Your Name)">
                    </div>

                    <!-- Email Input -->
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none pl-4">
                            <svg class="h-5 w-5 text-slate-400 group-focus-within:text-primary-500 transition-colors duration-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                            </svg>
                        </div>
                        <input id="email" name="email" type="email" autocomplete="email" required 
                            class="block w-full py-3.5 pl-12 pr-4 border-2 border-slate-100 bg-slate-50/50 rounded-2xl placeholder-slate-400 focus:outline-none focus:border-primary-500 focus:bg-white focus:ring-4 focus:ring-primary-500/10 transition-all duration-300 text-slate-900 font-semibold text-sm"
                            value="{{ old('email') }}" placeholder="Email Address">
                    </div>

                    <!-- Password Input -->
                    <div class="relative group" x-data="{ show: false }">
                        <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none pl-4 z-10">
                            <svg class="h-5 w-5 text-slate-400 group-focus-within:text-primary-500 transition-colors duration-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input id="password" name="password" :type="show ? 'text' : 'password'" autocomplete="new-password" required 
                            class="block w-full py-3.5 pl-12 pr-12 border-2 border-slate-100 bg-slate-50/50 rounded-2xl placeholder-slate-400 focus:outline-none focus:border-primary-500 focus:bg-white focus:ring-4 focus:ring-primary-500/10 transition-all duration-300 text-slate-900 font-semibold text-sm"
                            placeholder="Password (കുറഞ്ഞത് 6 അക്ഷരങ്ങൾ)">
                        
                        <div class="absolute inset-y-0 right-0 flex items-center pr-2 z-20">
                            <button type="button" @click="show = !show" class="text-slate-400 hover:text-primary-500 focus:outline-none transition-colors p-2 rounded-full hover:bg-slate-100 italic text-xs font-bold uppercase">
                                <span x-show="!show">Show</span>
                                <span x-show="show">Hide</span>
                            </button>
                        </div>
                    </div>

                    <!-- Confirm Password Input -->
                    <div class="relative group" x-data="{ show: false }">
                        <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none pl-4 z-10">
                            <svg class="h-5 w-5 text-slate-400 group-focus-within:text-primary-500 transition-colors duration-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input id="password_confirmation" name="password_confirmation" :type="show ? 'text' : 'password'" autocomplete="new-password" required 
                            class="block w-full py-3.5 pl-12 pr-12 border-2 border-slate-100 bg-slate-50/50 rounded-2xl placeholder-slate-400 focus:outline-none focus:border-primary-500 focus:bg-white focus:ring-4 focus:ring-primary-500/10 transition-all duration-300 text-slate-900 font-semibold text-sm"
                            placeholder="Confirm Password (പാസ്‌വേഡ് വീണ്ടും)">
                    </div>

                    <div class="pt-2">
                        <button type="submit" 
                            class="w-full relative overflow-hidden group py-4 px-6 rounded-2xl bg-gradient-to-r from-primary-500 via-cyan-500 to-blue-600 shadow-[0_10px_20px_-5px_rgba(6,182,212,0.4)] text-base font-extrabold text-white transition-all duration-300 hover:shadow-[0_15px_30px_-5px_rgba(6,182,212,0.6)] hover:-translate-y-0.5 active:scale-95">
                            <span class="relative z-10 flex items-center justify-center gap-2">
                                സൗജന്യമായി രജിസ്റ്റർ ചെയ്യൂ (Register Free)
                                <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </span>
                            <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                        </button>
                    </div>
                </form>
                
                <div class="mt-6 pt-6 border-t border-slate-100 text-center">
                    <p class="text-xs text-slate-500">
                        ഇതിനകം അക്കൗണ്ട് ഉണ്ടോ? 
                        <a href="{{ route('login') }}" class="text-primary-600 font-extrabold hover:underline ml-1">
                            ഇവിടെ Login ചെയ്യുക
                        </a>
                    </p>
                </div>
            </div>

            <!-- Guarantee note -->
            <p class="mt-6 text-center text-xs text-white/50">
                🔒 നിങ്ങളുടെ വിവരങ്ങൾ 100% സുരക്ഷിതമാണ്. പേയ്‌മെന്റോ ക്രെഡിറ്റ് കാർഡോ ആവശ്യമില്ല.
            </p>
        </div>
    </div>
</body>
</html>
