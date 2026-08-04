<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Laravel') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    </head>
    <style>
        [x-cloak] { display: none !important; }
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #0f172a !important;
            background-image: 
                radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), 
                radial-gradient(at 50% 0%, hsla(225,39%,30%,1) 0, transparent 50%), 
                radial-gradient(at 100% 0%, hsla(339,49%,30%,1) 0, transparent 50%) !important;
            background-attachment: fixed;
            background-size: cover;
            min-height: 100vh;
        }
        .animate-blob { animation: blob 7s infinite; }
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
    </style>
    <body class="font-sans antialiased text-gray-900 overflow-x-hidden">
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

        {{-- Background Blobs --}}
        <div class="fixed inset-0 -z-10 h-full w-full pointer-events-none">
            <div class="absolute top-20 left-20 w-96 h-96 bg-purple-600/10 rounded-full mix-blend-multiply filter blur-3xl animate-blob"></div>
            <div class="absolute top-40 right-20 w-96 h-96 bg-blue-600/10 rounded-full mix-blend-multiply filter blur-3xl animate-blob [animation-delay:2000ms]"></div>
            <div class="absolute -bottom-20 left-1/2 w-96 h-96 bg-pink-600/10 rounded-full mix-blend-multiply filter blur-3xl animate-blob [animation-delay:4000ms]"></div>
        </div>
        
        {{ $slot }}
    </body>
</html>
