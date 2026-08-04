@extends('layouts.public')
@section('title', 'Terms of Service - Amter')
@section('meta_description', 'Read our Terms of Service to understand the rules and guidelines for using Amter.')

@section('content')
<div class="relative bg-gray-50 min-h-screen pt-24 pb-20">
    <!-- Header/Hero Section -->
    <div class="bg-white border-b border-gray-100 mb-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-50 text-primary-600 font-bold text-xs mb-4">
                <span class="w-1.5 h-1.5 rounded-full bg-primary-500"></span>
                Last Updated: {{ date('F d, Y') }}
            </div>
            <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 tracking-tight mb-4">Terms of Service</h1>
            <p class="text-xl text-gray-500 leading-relaxed">
                Welcome to Amter. By using our platform, you agree to the following terms and guidelines.
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
                            Agreement to Terms
                        </h2>
                        <div class="pl-11">
                            <p class="text-gray-600">
                                By accessing or using Amter, you agree to be bound by these Terms of Service. If you do not agree, please do not use our services. We believe in clear, simple agreements that focus on learning rather than legal jargon.
                            </p>
                        </div>
                    </section>

                    <section>
                        <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-3">
                            <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-pink-100 text-pink-600 text-sm">02</span>
                            Account & Usage
                        </h2>
                        <div class="pl-11">
                            <p class="text-gray-600 mb-4">
                                You are responsible for maintaining the security of your account. One account is intended for one learner.
                            </p>
                            <ul class="space-y-3 list-disc list-inside text-gray-600">
                                <li>Account sharing is not permitted</li>
                                <li>You must provide accurate information during registration</li>
                                <li>We reserve the right to suspend accounts that violate our guidelines</li>
                            </ul>
                        </div>
                    </section>

                    <section>
                        <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-3">
                            <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-100 text-blue-600 text-sm">03</span>
                            Payments & Refunds
                        </h2>
                        <div class="pl-11">
                            <p class="text-gray-600 mb-4">
                                Amter offers a straightforward "One-Time Payment" model. 
                            </p>
                            <ul class="space-y-3 list-disc list-inside text-gray-600">
                                <li>No hidden subscription or recurring fees</li>
                                <li>Refunds are reviewed on a case-by-case basis within 7 days of purchase</li>
                                <li>Contact <a href="mailto:billing@amter.test" class="text-primary-600">billing@amter.test</a> for any payment queries</li>
                            </ul>
                        </div>
                    </section>

                    <section>
                        <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-3">
                            <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-green-100 text-green-600 text-sm">04</span>
                            Community Standards
                        </h2>
                        <div class="pl-11">
                            <p class="text-gray-600 mb-4">
                                We are a community of learners. To maintain a positive environment, you agree not to:
                            </p>
                            <ul class="space-y-3 list-disc list-inside text-gray-600">
                                <li>Harass other students or staff</li>
                                <li>Attempt to reverse-engineer our voice matching technology</li>
                                <li>Distribute course materials without permission</li>
                            </ul>
                        </div>
                    </section>

                    <div class="pt-8 border-t border-gray-100">
                        <p class="text-sm text-gray-400">
                            By continuing to use Amter, you acknowledge that you have read and understood these terms.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
