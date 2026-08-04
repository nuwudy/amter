<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Amter - Speak English Naturally')</title>
    <meta name="description" content="@yield('meta_description', 'Amter helps you learn English naturally through bite-sized audio lessons and AI-powered voice matching. Perfect for Malayalis looking to improve their fluency.')">
    <meta name="keywords" content="@yield('meta_keywords', 'learn english, spoken english malayalam, english speaking app, language learning, voice matching english, amter english')">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('title', config('app.name', 'Amter')) - Speak English Naturally">
    <meta property="og:description" content="@yield('meta_description', 'Amter helps you learn English naturally through bite-sized audio lessons and AI-powered voice matching.')">
    <meta property="og:image" content="@yield('og_image', asset('images/hero-chips.jpg'))">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="@yield('title', config('app.name', 'Amter')) - Speak English Naturally">
    <meta property="twitter:description" content="@yield('meta_description', 'Amter helps you learn English naturally through bite-sized audio lessons and AI-powered voice matching.')">
    <meta property="twitter:image" content="@yield('og_image', asset('images/hero-chips.jpg'))">

    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Structured Data -->
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@type": "Organization",
      "name": "Amter",
      "url": "{{ url('/') }}",
      "logo": "{{ asset('images/full-logo.png?v=1.0.2') }}",
      "sameAs": [
        "https://www.instagram.com/amter_english"
      ],
      "contactPoint": {
        "@@type": "ContactPoint",
        "telephone": "+91-9895940500",
        "contactType": "customer service"
      }
    }
    </script>
    @yield('structured_data')

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="//unpkg.com/alpinejs" defer></script>

    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }
        [x-cloak] { display: none !important; }
        
        .glass-nav {
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        /* Mobile floating bar styles */
        @media (max-width: 639px) {
            body {
                padding-bottom: 76px;
            }
        }
        @supports (padding-bottom: env(safe-area-inset-bottom)) {
            .pb-safe {
                padding-bottom: env(safe-area-inset-bottom);
            }
        }
    </style>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png?v=3') }}">
    <link rel="apple-touch-icon" href="{{ asset('splash.png?v=4') }}">
    <link rel="apple-touch-startup-image" href="{{ asset('splash.png?v=4') }}">
    <link rel="manifest" href="/manifest.json?v=4">
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js');
            });
        }
    </script>
</head>
<body class="antialiased text-gray-100 bg-slate-900" 
      x-data="{ 
          scrolled: false, 
          mobileMenuOpen: false,
          deferredPrompt: null,
          async installApp() {
              if (this.deferredPrompt) {
                  this.deferredPrompt.prompt();
                  const { outcome } = await this.deferredPrompt.userChoice;
                  this.deferredPrompt = null;
              }
          }
      }" 
      x-on:scroll.window="scrolled = (window.pageYOffset > 20)"
      x-on:beforeinstallprompt.window="deferredPrompt = $event; $event.preventDefault()">

    <!-- Fast, Elegant Light Preloader -->
    <div id="amter-preloader" style="position: fixed; inset: 0; background: #0f172a; display: flex; justify-content: center; align-items: center; z-index: 9999999; transition: opacity 0.5s cubic-bezier(0.16, 1, 0.3, 1), visibility 0.5s;">
        <div style="position: relative; display: flex; align-items: center; justify-content: center; width: 80px; height: 80px;">
            <div style="position: absolute; width: 45px; height: 45px; border: 3.5px solid transparent; border-top-color: #6366f1; border-bottom-color: #a855f7; border-radius: 50%; animation: amter-preloader-spin 0.9s cubic-bezier(0.53, 0.21, 0.29, 0.87) infinite;"></div>
            <div style="position: absolute; width: 25px; height: 25px; border: 3.5px solid transparent; border-left-color: #f43f5e; border-right-color: #0ea5e9; border-radius: 50%; animation: amter-preloader-spin-reverse 0.7s cubic-bezier(0.53, 0.21, 0.29, 0.87) infinite;"></div>
        </div>
        <style>
            @keyframes amter-preloader-spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
            @keyframes amter-preloader-spin-reverse { 0% { transform: rotate(360deg); } 100% { transform: rotate(0deg); } }
            .amter-preloader-hidden { opacity: 0 !important; visibility: hidden !important; pointer-events: none !important; }
        </style>
    </div>
    <script>
        (function() {
            var loader = document.getElementById('amter-preloader');
            var hasLoaded = false;
            var hideLoader = function() {
                if (loader && !hasLoaded) {
                    hasLoaded = true;
                    loader.classList.add('amter-preloader-hidden');
                    setTimeout(function() { if (loader.parentNode) loader.parentNode.removeChild(loader); }, 600);
                }
            };
            window.addEventListener('load', hideLoader);
            setTimeout(hideLoader, 3000); // Safety fallback
        })();
    </script>

    <!-- Notifications -->
    @if(session('success') || session('error'))
        <div x-data="{ show: true }" 
             x-show="show" 
             x-init="setTimeout(() => show = false, 5000)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-4"
             class="fixed top-24 left-1/2 transform -translate-x-1/2 z-[100] w-full max-w-sm px-4">
            <div class="{{ session('success') ? 'bg-green-600' : 'bg-red-600' }} text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center justify-between gap-4">
                <p class="font-bold text-sm">{{ session('success') ?? session('error') }}</p>
                <button @click="show = false" class="text-white/80 hover:text-white">
                    <svg class="w-5 h-5 shrink-0" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
        </div>
    @endif

    <!-- Header -->
    <header class="fixed w-full top-0 z-50 transition-all duration-300" 
            :class="{ 'glass-nav py-3': scrolled, 'bg-transparent py-5': !scrolled }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center">
            <!-- Logo -->
            <div class="flex-shrink-0 flex items-center">
                <a href="{{ route('home') }}" class="flex items-center gap-2 group">
                    <img src="{{ asset('images/full-logo.png?v=1.0.2') }}" alt="Amter English" style="max-height: 48px; max-width: 200px;" class="h-12 w-auto object-contain transition-transform duration-200 group-hover:scale-105 shrink-0">
                </a>
            </div>

            <nav class="hidden md:flex space-x-8 items-center">
    <a href="{{ route('home') }}" class="font-medium text-white hover:text-primary-400 transition-colors">Home</a>
    @auth
        <a href="{{ route('filament.student.pages.dashboard') }}" class="font-medium text-white hover:text-primary-400 transition-colors">Dashboard</a>
        <a href="{{ route('logout') }}" class="font-medium text-white hover:text-red-400 transition-colors">Logout</a>
    @else
        <a href="{{ route('login') }}" class="font-medium text-white hover:text-primary-400 transition-colors">Login</a>
    @endauth
    <a href="{{ route('contact') }}" class="font-medium text-white hover:text-primary-400 transition-colors">Contact</a>
    <a href="{{ route('pricing') }}" class="font-medium text-white hover:text-primary-400 transition-colors">Pricing</a>
    <a href="{{ route('public.library') }}" class="bg-gradient-to-r from-primary-500 to-blue-600 text-white px-6 py-2.5 rounded-full font-bold shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-200">Go to Classes</a>
</nav>

            <!-- Mobile Menu Button -->
            <div class="md:hidden flex items-center">
                <button x-on:click="mobileMenuOpen = !mobileMenuOpen" class="p-2 focus:outline-none text-white">
                    <svg class="w-6 h-6 shrink-0" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!mobileMenuOpen">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                    <svg class="w-6 h-6 shrink-0" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="mobileMenuOpen" x-cloak>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div x-show="mobileMenuOpen" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="md:hidden absolute top-full left-0 w-full bg-white shadow-xl border-t border-gray-100 py-4 px-4 flex flex-col space-y-4"
             x-cloak>
            <a href="{{ route('home') }}" class="block text-lg font-medium text-gray-900 hover:text-primary-600">Home</a>
            @auth
                <a href="{{ route('filament.student.pages.dashboard') }}" class="block text-lg font-medium text-gray-900 hover:text-primary-600">Dashboard</a>
                <a href="{{ route('logout') }}" class="block text-lg font-medium text-red-600 hover:text-red-700">Logout</a>
            @else
                <a href="{{ route('login') }}" class="block text-lg font-medium text-gray-900 hover:text-primary-600">Login</a>
            @endauth
            <a href="{{ route('contact') }}" class="block text-lg font-medium text-gray-900 hover:text-primary-600">Contact</a>
            <a href="{{ route('pricing') }}" class="block text-lg font-medium text-gray-900 hover:text-primary-600">Pricing</a>

            <a href="{{ route('public.library') }}" class="block w-full text-center bg-gradient-to-r from-primary-500 to-blue-600 text-white px-6 py-3 rounded-xl font-bold shadow-md">
                Go to Classes
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Floating Install Button -->
    <div x-show="deferredPrompt" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-y-full opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="translate-y-0 opacity-100"
         x-transition:leave-end="translate-y-full opacity-0"
         class="fixed bottom-24 sm:bottom-6 left-1/2 transform -translate-x-1/2 z-[70] bg-white/10 backdrop-blur-sm p-1.5 rounded-full shadow-2xl border border-white/20"
         style="display: none;"
         x-cloak>
        <button x-on:click="installApp()" 
                class="bg-gray-900 text-white px-6 py-3 rounded-full font-bold shadow-lg hover:bg-gray-800 hover:scale-105 transition-all duration-300 flex items-center gap-3">
            <div class="bg-gray-800 p-1.5 rounded-full">
                <svg class="w-5 h-5 text-primary-400 shrink-0" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
            </div>
            <span>Install App</span>
        </button>
    </div>

    <!-- WhatsApp Floating Button -->
    @if(!request()->routeIs('public.unit.show'))
    <a href="https://wa.me/919895940500" target="_blank" rel="noopener noreferrer" 
       class="hidden sm:flex fixed bottom-6 right-6 z-50 bg-[#25D366] text-white p-4 rounded-full shadow-[0_4px_12px_rgba(0,0,0,0.15)] hover:shadow-[0_8px_24px_rgba(37,211,102,0.3)] hover:scale-110 transition-all duration-300 items-center justify-center group"
       aria-label="Chat on WhatsApp">
        <svg class="w-8 h-8 fill-current shrink-0" width="32" height="32" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
        </svg>
    </a>
    @endif

    {{-- Mobile Bottom Navigation Bar --}}
    @if(!request()->routeIs('public.unit.show'))
    <div class="sm:hidden fixed bottom-0 left-0 right-0 z-[60] bg-slate-900/95 backdrop-blur-lg border-t border-white/10 px-4 py-3 flex justify-around items-center pb-safe shadow-[0_-10px_30px_rgba(0,0,0,0.5)]">
        @auth
            <a href="{{ route('filament.student.pages.dashboard') }}" class="flex flex-col items-center gap-1 text-slate-400 hover:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                <span class="text-[10px] font-black uppercase tracking-widest mt-0.5">Classes</span>
            </a>
        @else
            <a href="{{ route('public.library') }}" class="flex flex-col items-center gap-1 text-slate-400 hover:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                <span class="text-[10px] font-black uppercase tracking-widest mt-0.5">Classes</span>
            </a>
        @endauth
        <a href="{{ route('pricing') }}" class="flex flex-col items-center gap-1 text-slate-400 hover:text-white transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            <span class="text-[10px] font-black uppercase tracking-widest mt-0.5">Plans</span>
        </a>
        <a href="{{ url('/?review=1#reviews') }}" class="flex flex-col items-center gap-1 text-slate-400 hover:text-white transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
            <span class="text-[10px] font-black uppercase tracking-widest mt-0.5">Reviews</span>
        </a>
        <a href="https://wa.me/919895940500" target="_blank" rel="noopener noreferrer" class="flex flex-col items-center gap-1 text-[#25D366] hover:text-[#25D366]/80 transition-colors">
            <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
            <span class="text-[10px] font-black uppercase tracking-widest text-[#25D366] mt-0.5">WhatsApp</span>
        </a>
    </div>
    @endif

    <!-- Footer -->
    <footer class="bg-gray-50 border-t border-gray-100 pt-16 pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center gap-2 mb-4">
                        <img src="{{ asset('images/full-logo.png?v=1.0.2') }}" alt="Amter English" style="max-height: 40px; max-width: 150px;" class="h-10 w-auto object-contain shrink-0">
                    </div>
                    <p class="text-gray-500 text-sm leading-relaxed max-w-sm">
                        Amter makes learning a new language as easy as snacking. Bite-sized lessons, voice matching technology, and a world of discovery await.
                    </p>
                </div>
                <div>
                    <h4 class="font-bold text-gray-900 mb-4">Discover</h4>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><a href="{{ route('home') }}#courses" class="hover:text-primary-600">Curriculum</a></li>
                        <li><a href="{{ route('about') }}" class="hover:text-primary-600">Methodology</a></li>
                        <li><a href="{{ route('pricing') }}" class="hover:text-primary-600">Pricing</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-gray-900 mb-4">Support</h4>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><a href="{{ route('contact') }}" class="hover:text-primary-600">Contact Us</a></li>
                        <li><a href="{{ route('privacy') }}" class="hover:text-primary-600">Privacy Policy</a></li>
                        <li><a href="{{ route('terms') }}" class="hover:text-primary-600">Terms of Service</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-200 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-xs text-gray-400">&copy; {{ date('Y') }} Amter. All rights reserved.</p>
                <div class="flex space-x-4">
                    <!-- Social Placeholders -->
                    <a href="#" class="text-gray-400 hover:text-primary-500"><span class="sr-only">Instagram</span><svg class="w-5 h-5 shrink-0" width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg></a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
