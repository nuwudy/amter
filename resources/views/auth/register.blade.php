<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>കൂടുതൽ ഫ്രീ ക്ലാസ്സുകൾക്കായി റജിസ്റ്റർ ചെയ്യൂ - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Outfit', sans-serif;
            background-color: #090d16;
            background-image:
                radial-gradient(at 15% 15%, rgba(0, 194, 232, 0.18) 0px, transparent 50%),
                radial-gradient(at 85% 10%, rgba(236, 72, 153, 0.18) 0px, transparent 50%),
                radial-gradient(at 50% 90%, rgba(124, 58, 237, 0.2) 0px, transparent 55%);
            background-attachment: fixed;
            color: #0f172a;
            min-height: 100vh;
        }
        .reg-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 1.5rem 1rem 2.5rem;
        }
        .reg-container {
            width: 100%;
            max-width: 440px;
        }
        .reg-topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        .reg-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.78rem;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.88);
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.16);
            padding: 0.45rem 0.95rem;
            border-radius: 9999px;
            text-decoration: none;
            backdrop-filter: blur(8px);
            transition: all 0.2s;
        }
        .reg-back-btn:hover {
            background: rgba(255, 255, 255, 0.18);
            color: #ffffff;
        }
        .reg-free-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.35rem 0.8rem;
            border-radius: 9999px;
            background: rgba(16, 185, 129, 0.16);
            border: 1px solid rgba(52, 211, 153, 0.35);
            color: #6ee7b7;
            font-size: 0.72rem;
            font-weight: 800;
        }
        .reg-card {
            background: #ffffff;
            border-radius: 2rem;
            padding: 2rem 1.75rem 1.75rem;
            box-shadow: 0 25px 65px -15px rgba(0, 0, 0, 0.7), 0 0 0 1px rgba(255, 255, 255, 0.5);
            position: relative;
            overflow: hidden;
        }
        .reg-card-accent {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #f97316 0%, #ec4899 50%, #00c2e8 100%);
        }
        .reg-title-highlight {
            background: linear-gradient(90deg, #ea580c 0%, #db2777 55%, #7c3aed 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            display: inline-block;
            font-weight: 900;
        }
        .reg-perks {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.4rem;
            margin: 0.85rem 0 1.15rem;
        }
        .reg-perk-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.7rem;
            font-weight: 700;
            color: #334155;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 0.28rem 0.65rem;
            border-radius: 9999px;
        }
        .reg-tabs {
            display: flex;
            gap: 4px;
            padding: 4px;
            background-color: #f1f5f9;
            border-radius: 0.95rem;
            border: 1px solid #e2e8f0;
            margin-bottom: 1.25rem;
        }
        .reg-tab-inactive {
            flex: 1;
            text-align: center;
            padding: 0.55rem 0.5rem;
            font-size: 0.8rem;
            font-weight: 700;
            color: #64748b;
            text-decoration: none;
            border-radius: 0.7rem;
            transition: all 0.2s;
        }
        .reg-tab-inactive:hover {
            color: #0f172a;
            background: rgba(255,255,255,0.6);
        }
        .reg-tab-active {
            flex: 1;
            text-align: center;
            padding: 0.55rem 0.5rem;
            font-size: 0.8rem;
            font-weight: 800;
            color: #ffffff;
            background: linear-gradient(135deg, #f97316 0%, #ec4899 100%);
            border-radius: 0.7rem;
            box-shadow: 0 3px 10px rgba(236, 72, 153, 0.3);
        }
        .reg-field-group {
            margin-bottom: 0.85rem;
        }
        .reg-label {
            display: block;
            font-size: 0.74rem;
            font-weight: 800;
            color: #334155;
            margin-bottom: 0.35rem;
            padding-left: 0.2rem;
        }
        .reg-input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }
        .reg-input-icon {
            position: absolute;
            left: 0.9rem;
            width: 1.1rem;
            height: 1.1rem;
            color: #94a3b8;
            pointer-events: none;
            transition: color 0.2s;
        }
        .reg-input {
            width: 100%;
            padding: 0.8rem 1rem 0.8rem 2.65rem;
            font-family: inherit;
            font-size: 0.88rem;
            font-weight: 600;
            color: #0f172a;
            background-color: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 0.9rem;
            outline: none;
            transition: all 0.2s;
        }
        .reg-input::placeholder {
            color: #94a3b8;
            font-weight: 500;
        }
        .reg-input:focus {
            background-color: #ffffff;
            border-color: #f97316;
            box-shadow: 0 0 0 3.5px rgba(249, 115, 22, 0.15);
        }
        .reg-input-wrap:focus-within .reg-input-icon {
            color: #f97316;
        }
        .reg-show-btn {
            position: absolute;
            right: 0.55rem;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            color: #475569;
            font-size: 0.68rem;
            font-weight: 800;
            padding: 0.3rem 0.55rem;
            border-radius: 0.5rem;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            transition: all 0.15s;
        }
        .reg-show-btn:hover {
            background: #ffedd5;
            color: #c2410c;
            border-color: #fdba74;
        }
        .reg-submit-btn {
            width: 100%;
            margin-top: 0.5rem;
            padding: 0.95rem 1.25rem;
            border: none;
            border-radius: 1rem;
            background: linear-gradient(135deg, #f97316 0%, #ec4899 55%, #7c3aed 100%);
            color: #ffffff;
            font-family: inherit;
            font-size: 0.98rem;
            font-weight: 800;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
            box-shadow: 0 10px 25px -5px rgba(236, 72, 153, 0.5);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .reg-submit-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 30px -5px rgba(236, 72, 153, 0.65);
        }
        .reg-submit-btn:active {
            transform: scale(0.98);
        }
    </style>
</head>
<body>
    <div class="reg-wrapper">
        <div class="reg-container">

            <!-- Top Navigation -->
            <div class="reg-topbar">
                <a href="{{ route('public.library') }}" class="reg-back-btn">
                    <svg style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Back to Classes</span>
                </a>

                <span class="reg-free-pill">
                    <span style="width: 6px; height: 6px; border-radius: 9999px; background: #34d399;"></span>
                    100% Free Access
                </span>
            </div>

            <!-- Main Card -->
            <div class="reg-card">
                <div class="reg-card-accent"></div>

                <!-- Header -->
                <div style="text-align: center;">
                    <a href="{{ route('home') }}" style="display: inline-block; margin-bottom: 0.6rem;">
                        <img src="{{ asset('images/full-logo.png?v=1.0.2') }}" alt="Amter English" style="height: 42px; width: auto; object-fit: contain;">
                    </a>

                    <div>
                        <span style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.25rem 0.75rem; border-radius: 9999px; background: #fff7ed; border: 1px solid #fed7aa; color: #c2410c; font-size: 0.72rem; font-weight: 800; margin-bottom: 0.5rem;">
                            🎁 സൗജന്യ അക്കൗണ്ട് (Free Account)
                        </span>
                    </div>

                    <h1 style="font-size: 1.4rem; font-weight: 900; color: #0f172a; line-height: 1.35; margin: 0.2rem 0 0;">
                        കൂടുതൽ ഫ്രീ ക്ലാസ്സുകൾക്കായി<br>
                        <span class="reg-title-highlight">റജിസ്റ്റർ ചെയ്യൂ</span>
                    </h1>

                    <p style="font-size: 0.82rem; font-weight: 700; color: #64748b; margin: 0.25rem 0 0;">
                        Create your free account in 10 seconds
                    </p>

                    <!-- Value Pills -->
                    <div class="reg-perks">
                        <span class="reg-perk-chip"><span style="color: #10b981;">✓</span> Free Classes</span>
                        <span class="reg-perk-chip"><span style="color: #0ea5e9;">✓</span> AI Voice Check</span>
                        <span class="reg-perk-chip"><span style="color: #f97316;">✓</span> Save Progress</span>
                    </div>
                </div>

                <!-- Segmented Switcher -->
                <div class="reg-tabs">
                    <a href="{{ route('login') }}" class="reg-tab-inactive">
                        🔑 Login
                    </a>
                    <span class="reg-tab-active">
                        ✨ Register
                    </span>
                </div>

                @if($errors->any())
                    <div style="margin-bottom: 1rem; padding: 0.85rem 1rem; border-radius: 0.9rem; background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; font-size: 0.78rem; font-weight: 600;">
                        <ul style="margin: 0; padding-left: 1.1rem;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('register.submit') }}" method="POST" x-data="{ showPass: false, pw: '' }">
                    @csrf

                    <!-- Name Input -->
                    <div class="reg-field-group">
                        <label for="name" class="reg-label">നിങ്ങളുടെ പേര് (Full Name)</label>
                        <div class="reg-input-wrap">
                            <svg class="reg-input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <input id="name" name="name" type="text" autocomplete="name" required autofocus
                                class="reg-input"
                                value="{{ old('name') }}" placeholder="e.g. Rahul Krishnan">
                        </div>
                    </div>

                    <!-- Email Input -->
                    <div class="reg-field-group">
                        <label for="email" class="reg-label">ഇമെയിൽ (Email Address)</label>
                        <div class="reg-input-wrap">
                            <svg class="reg-input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <input id="email" name="email" type="email" autocomplete="email" required
                                class="reg-input"
                                value="{{ old('email') }}" placeholder="you@gmail.com">
                        </div>
                    </div>

                    <!-- Password Input (Single streamlined field with Show/Hide toggle) -->
                    <div class="reg-field-group">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <label for="password" class="reg-label">പാസ്‌വേഡ് (Create Password)</label>
                            <span style="font-size: 0.68rem; font-weight: 700;"
                                  :style="pw.length >= 6 ? 'color: #10b981;' : 'color: #94a3b8;'">
                                <span x-show="pw.length >= 6">✓ Ready</span>
                                <span x-show="pw.length < 6">Min 6 characters</span>
                            </span>
                        </div>
                        <div class="reg-input-wrap">
                            <svg class="reg-input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <input id="password" name="password" x-model="pw" :type="showPass ? 'text' : 'password'" autocomplete="new-password" required minlength="6"
                                class="reg-input" style="padding-right: 4.5rem;"
                                placeholder="At least 6 characters">
                            <button type="button" @click="showPass = !showPass" class="reg-show-btn">
                                <span x-show="!showPass">Show</span>
                                <span x-show="showPass" x-cloak>Hide</span>
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="reg-submit-btn">
                        <span>സൗജന്യമായി റജിസ്റ്റർ ചെയ്യൂ ✨</span>
                        <svg style="width: 18px; height: 18px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </form>

                <!-- Bottom Helper -->
                <div style="margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid #f1f5f9; text-align: center;">
                    <p style="margin: 0; font-size: 0.8rem; color: #64748b; font-weight: 600;">
                        ഇതിനകം അക്കൗണ്ട് ഉണ്ടോ?
                        <a href="{{ route('login') }}" style="color: #ea580c; font-weight: 800; text-decoration: none; margin-left: 0.25rem;">
                            Login ചെയ്യുക →
                        </a>
                    </p>
                </div>
            </div>

            <!-- Friendly Footer Note -->
            <p style="margin-top: 1rem; text-align: center; font-size: 0.75rem; color: rgba(255,255,255,0.65); font-weight: 600;">
                🔒 100% സുരക്ഷിതം • ക്രെഡിറ്റ് കാർഡോ പേയ്‌മെന്റോ ആവശ്യമില്ല
            </p>
        </div>
    </div>
</body>
</html>
