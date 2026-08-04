@extends('layouts.public')
@section('title', 'Contact Us - Get in Touch with Amter')
@section('meta_description', 'Have questions? We are here to help. Contact the Amter team for support, partnerships, or general inquiries.')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center py-20 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl w-full bg-white rounded-3xl shadow-xl overflow-hidden flex flex-col md:flex-row">
        <!-- Decoration / Info Side -->
        <div class="bg-primary-600 w-full md:w-1/2 p-10 text-white flex flex-col justify-between relative overflow-hidden">
            <div class="absolute top-0 right-0 w-40 h-40 bg-white opacity-10 rounded-full translate-x-10 -translate-y-10"></div>
            <div class="absolute bottom-0 left-0 w-40 h-40 bg-white opacity-10 rounded-full -translate-x-10 translate-y-10"></div>
            
            <div>
                <h2 class="text-3xl font-bold mb-4">Let's Chat!</h2>
                <p class="text-primary-100 mb-8">
                    Have a question about a course? Found a bug? Or just want to say hi? We'd love to hear from you.
                </p>
            </div>

            <div class="space-y-4">
                <div class="flex items-center gap-4">
                    <span class="bg-white/20 p-2 rounded-lg">📧</span>
                    <span>contact.amterenglis@gmail.com</span>
                </div>
                <div class="flex items-center gap-4">
                    <span class="bg-white/20 p-2 rounded-lg">📞</span>
                    <span>+91 98959 40500</span>
                </div>
                <div class="flex items-center gap-4">
                    <span class="bg-white/20 p-2 rounded-lg">📍</span>
                    <span>Kerala, India</span>
                </div>
            </div>
        </div>

        <!-- Form Side -->
        <div class="w-full md:w-1/2 p-10">
            <form action="#" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Your Name</label>
                    <input type="text" name="name" id="name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 outline-none transition" placeholder="John Doe">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" name="email" id="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 outline-none transition" placeholder="john@example.com">
                </div>
                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                    <textarea name="message" id="message" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lr focus:ring-primary-500 focus:border-primary-500 outline-none transition" placeholder="How can we help?"></textarea>
                </div>
                
                <button type="button" onclick="alert('Message simulation sent!')" class="w-full bg-gray-900 text-white font-bold py-3 rounded-xl hover:bg-gray-800 transition transform hover:scale-105">
                    Send Message
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
