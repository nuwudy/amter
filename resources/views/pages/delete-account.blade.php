@extends('layouts.public')
@section('title', 'Delete Account & Data - Amter Spoken English')
@section('meta_description', 'Request or immediately perform permanent deletion of your Amter Spoken English account and associated personal learning data.')

@section('content')
<div class="relative bg-slate-950 min-h-screen pt-28 pb-24 text-slate-100">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Card -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-8 md:p-10 shadow-2xl mb-8">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-rose-500/10 border border-rose-500/30 text-rose-400 font-bold text-xs uppercase tracking-wider mb-4">
                <span>Google Play & Web Account Management</span>
            </div>
            <h1 class="text-3xl md:text-4xl font-extrabold text-white tracking-tight mb-3">
                Delete Your Amter Account
            </h1>
            <p class="text-slate-400 text-base md:text-lg leading-relaxed">
                You can permanently delete your <strong class="text-slate-200">Amter Spoken English</strong> account (<span class="text-indigo-400">amter.in</span> &amp; Android mobile app) and all associated learning data at any time using the form below.
            </p>
        </div>

        @if(session('account_deleted'))
            <div class="bg-emerald-500/15 border border-emerald-500/40 rounded-2xl p-6 mb-8 text-emerald-300 font-semibold flex items-start gap-3">
                <svg class="w-6 h-6 text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
                <div>
                    <h3 class="text-white font-bold text-lg mb-1">Account Permanently Deleted</h3>
                    <p>{{ session('account_deleted') }}</p>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-rose-500/15 border border-rose-500/40 rounded-2xl p-5 mb-8 text-rose-300 text-sm font-medium">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Data Deletion Summary (Required by Google Play Data Safety) -->
        <div class="bg-slate-900/80 border border-slate-800 rounded-3xl p-8 mb-8 space-y-6">
            <div>
                <h2 class="text-xl font-bold text-white mb-3">What Data Is Deleted Immediately</h2>
                <ul class="space-y-2.5 text-slate-300 text-sm md:text-base list-disc list-inside">
                    <li>Your account profile (<strong class="text-white">Name, Email Address, Password Hash</strong>, and authentication tokens).</li>
                    <li>All course progress, completed lessons, mastery streaks, and milestone records.</li>
                    <li>Active mobile app and web session tokens.</li>
                </ul>
            </div>

            <div class="pt-4 border-t border-slate-800">
                <h2 class="text-xl font-bold text-white mb-3">What Data May Be Retained for Legal / Tax Compliance</h2>
                <p class="text-slate-400 text-sm md:text-base leading-relaxed">
                    In accordance with Indian financial and tax regulations, non-personal transaction invoice records (order IDs, payment amounts, and timestamps for Razorpay or Google Play Billing purchases) may be retained in anonymized accounting logs for statutory audit purposes. Voice pronunciation checks are processed in real time and are not stored on our servers after scoring.
                </p>
            </div>
        </div>

        <!-- Immediate Self-Service Deletion Form -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-8 md:p-10 shadow-xl">
            <h2 class="text-2xl font-bold text-white mb-2">Confirm Permanent Account Deletion</h2>
            <p class="text-slate-400 text-sm mb-6">
                @auth
                    You are currently signed in as <strong class="text-white">{{ auth()->user()->email }}</strong>. Enter your password below to permanently delete your account immediately.
                @else
                    Enter the email address and password associated with your Amter account to verify ownership and permanently delete your account immediately.
                @endauth
            </p>

            <form action="{{ route('delete.account.submit') }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete your Amter account and all learning progress? This action cannot be undone.');" class="space-y-5">
                @csrf

                @guest
                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-300 mb-2">Registered Email Address</label>
                        <input type="email"
                               id="email"
                               name="email"
                               value="{{ old('email') }}"
                               required
                               placeholder="you@example.com"
                               class="w-full rounded-xl bg-slate-950 border border-slate-700 px-4 py-3 text-white placeholder-slate-500 focus:border-rose-500 focus:outline-none">
                    </div>
                @endguest

                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-300 mb-2">Account Password</label>
                    <input type="password"
                           id="password"
                           name="password"
                           required
                           placeholder="Enter your password to confirm"
                           class="w-full rounded-xl bg-slate-950 border border-slate-700 px-4 py-3 text-white placeholder-slate-500 focus:border-rose-500 focus:outline-none">
                </div>

                <div class="pt-2 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <button type="submit"
                            class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-sm uppercase tracking-wider transition shadow-lg shadow-rose-600/25 cursor-pointer">
                        <span>Permanently Delete My Account</span>
                    </button>

                    <span class="text-xs text-slate-400">
                        Need help or forgot your password? Email <a href="mailto:amterglobal@gmail.com?subject=Account%20Deletion%20Request" class="text-indigo-400 hover:underline font-semibold">amterglobal@gmail.com</a>
                    </span>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
