@extends('layouts.public')
@section('title', 'Cancellation & Refund Policy - Amter')
@section('meta_description', 'Read our Cancellation and Refund Policy to understand our terms regarding course subscriptions and refunds.')

@section('content')
<div class="relative bg-gray-50 min-h-screen pt-24 pb-20">
    <!-- Header Section -->
    <div class="bg-white border-b border-gray-100 mb-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-50 text-primary-600 font-bold text-xs mb-4">
                <span class="w-1.5 h-1.5 rounded-full bg-primary-500"></span>
                Last Updated: {{ date('F d, Y') }}
            </div>
            <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 tracking-tight mb-4">Cancellation & Refund Policy</h1>
            <p class="text-xl text-gray-500 leading-relaxed">
                We believe in complete transparency and delivering the best English speaking practice experience to our learners.
            </p>
        </div>
    </div>

    <!-- Content Section -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 p-8 md:p-12">
            <div class="prose prose-lg prose-primary max-w-none space-y-10">
                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-3">
                        <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-primary-100 text-primary-600 text-sm">01</span>
                        Digital Course Access & Delivery
                    </h2>
                    <div class="pl-11 text-gray-600 space-y-3">
                        <p>
                            All courses and AI practice units provided by <strong>Amter</strong> are online digital services. Upon successful payment verification, your subscription access is granted instantly and automatically to your account.
                        </p>
                        <p>
                            Since access is digital and instant, no physical goods are shipped.
                        </p>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-3">
                        <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-pink-100 text-pink-600 text-sm">02</span>
                        Cancellation Policy
                    </h2>
                    <div class="pl-11 text-gray-600 space-y-3">
                        <p>
                            Amter offers fixed-duration learning plans (e.g., 1 Month, 2 Months, 3 Months, 6 Months) paid as a one-time purchase. We do not charge recurring automatic renewals without your explicit consent.
                        </p>
                        <p>
                            You may choose to discontinue using the platform at any time without any recurring billing penalty.
                        </p>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-3">
                        <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-100 text-blue-600 text-sm">03</span>
                        Refund Policy
                    </h2>
                    <div class="pl-11 text-gray-600 space-y-3">
                        <p>
                            If you encounter technical issues that prevent you from accessing the lessons or if you believe an accidental duplicate charge occurred, you are eligible to request a refund within <strong>7 days</strong> of your purchase.
                        </p>
                        <p>
                            To request a refund, please send an email to <a href="mailto:amterglobal@gmail.com" class="text-primary-600 font-semibold hover:underline">amterglobal@gmail.com</a> with:
                        </p>
                        <ul class="list-disc list-inside space-y-2">
                            <li>Your registered email address and phone number</li>
                            <li>Order / Payment ID received via email / SMS</li>
                            <li>Brief reason for the refund request</li>
                        </ul>
                        <p>
                            Once verified, approved refunds are processed within <strong>5 to 7 business days</strong> back to the original payment method (bank account, card, or UPI VPA) used during purchase.
                        </p>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-3">
                        <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-green-100 text-green-600 text-sm">04</span>
                        Customer Support
                    </h2>
                    <div class="pl-11 text-gray-600 space-y-2">
                        <p>If you have any questions or queries regarding payments, cancellations, or refunds, please reach out to us:</p>
                        <ul class="list-none space-y-2 pt-2">
                            <li><strong>Entity:</strong> Amter English</li>
                            <li><strong>Email:</strong> <a href="mailto:amterglobal@gmail.com" class="text-primary-600 hover:underline">amterglobal@gmail.com</a></li>
                            <li><strong>Phone:</strong> +91 98959 40500</li>
                            <li><strong>Location:</strong> Kerala, India</li>
                        </ul>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
@endsection
