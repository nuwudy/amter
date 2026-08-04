@extends('layouts.public')
@section('title', 'Privacy Policy - Amter')
@section('meta_description', 'Learn how Amter protects your privacy and handles your data for a safe learning experience.')

@section('content')
<div class="relative bg-gray-50 min-h-screen pt-24 pb-20">
    <!-- Header/Hero Section -->
    <div class="bg-white border-b border-gray-100 mb-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-50 text-primary-600 font-bold text-xs mb-4">
                <span class="w-1.5 h-1.5 rounded-full bg-primary-500"></span>
                Last Updated: {{ date('F d, Y') }}
            </div>
            <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 tracking-tight mb-4">Privacy Policy</h1>
            <p class="text-xl text-gray-500 leading-relaxed">
                At Amter, we are committed to protecting your privacy while providing the best English learning experience.
            </p>
        </div>
    </div>

    <!-- Content Section -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 p-8 md:p-12">
            <div class="prose prose-lg prose-primary max-w-none">
                <div class="space-y-12">
                    <section>
                        <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-3">
                            <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-primary-100 text-primary-600 text-sm">01</span>
                            Information We Collect
                        </h2>
                        <div class="pl-11">
                            <p class="text-gray-600 mb-4">
                                To provide our AI-powered learning services, we collect:
                            </p>
                            <ul class="space-y-3 list-disc list-inside text-gray-600">
                                <li>Account information (Name, Email)</li>
                                <li>Voice recordings for pronunciation feedback</li>
                                <li>Learning progress and quiz scores</li>
                                <li>Device and usage data for performance optimization</li>
                            </ul>
                        </div>
                    </section>

                    <section>
                        <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-3">
                            <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-pink-100 text-pink-600 text-sm">02</span>
                            How We Use Your Voice Data
                        </h2>
                        <div class="pl-11">
                            <p class="text-gray-600 mb-4 font-medium text-gray-900">
                                Your voice is your identity, and we treat it with the highest care.
                            </p>
                            <p class="text-gray-600 mb-4">
                                Our AI voice matching technology processes your recordings in real-time to provide immediate feedback. These recordings are:
                            </p>
                            <ul class="space-y-3 list-disc list-inside text-gray-600">
                                <li>Not sold to third parties</li>
                                <li>Processed securely to improve our pronunciation models</li>
                                <li>Accessible only to you through your dashboard</li>
                            </ul>
                        </div>
                    </section>

                    <section>
                        <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-3">
                            <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-100 text-blue-600 text-sm">03</span>
                            Data Security
                        </h2>
                        <div class="pl-11">
                            <p class="text-gray-600">
                                We implement industry-standard security measures to protect your data. All communication between your device and our servers is encrypted using SSL technology.
                            </p>
                        </div>
                    </section>

                    <section>
                        <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-3">
                            <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-green-100 text-green-600 text-sm">04</span>
                            Your Rights
                        </h2>
                        <div class="pl-11">
                            <p class="text-gray-600 mb-4">
                                You have the right to:
                            </p>
                            <ul class="space-y-3 list-disc list-inside text-gray-600">
                                <li>Access and export your personal data</li>
                                <li>Request the deletion of your account and all associated data</li>
                                <li>Opt-out of marketing communications</li>
                            </ul>
                        </div>
                    </section>

                    <div class="pt-8 border-t border-gray-100">
                        <p class="text-sm text-gray-400">
                            If you have any questions about this Privacy Policy, please contact us at <a href="mailto:support@amter.test" class="text-primary-600 hover:underline">support@amter.test</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
