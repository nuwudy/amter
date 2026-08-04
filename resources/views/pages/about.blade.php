@extends('layouts.public')
@section('title', 'About Amter - Our Mission and Methodology')
@section('meta_description', 'Learn about Amter\'s mission to make language learning natural and accessible. Discover our bite-sized lesson methodology.')

@section('content')
<div class="bg-white">
    <!-- Hero -->
    <section class="relative py-20 bg-primary-50 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
            <h1 class="text-4xl md:text-6xl font-extrabold text-gray-900 mb-6">The <span class="text-primary-600">nuWudy</span> Story</h1>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                Redefining how you connect with the world, one sound at a time.
            </p>
        </div>
        <!-- Decorative blobs -->
        <div class="absolute top-0 left-0 w-64 h-64 bg-pink-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
        <div class="absolute top-0 right-0 w-64 h-64 bg-yellow-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
    </section>

    <!-- Story Content -->
    <section class="py-20 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="prose prose-lg prose-indigo mx-auto text-gray-600">
            <p class="lead text-2xl font-light text-gray-800 mb-8">
                Language learning shouldn't feel like a chore. It should feel like discovering a new favorite song.
            </p>
            <p>
                At <strong>Amter</strong>, we believe in the power of "nuWudy"—the new way to study. We stripped away the boring grammar drills and heavy textbooks. Instead, we focused on what naturally helps us learn: <strong>Listening and Mimicking.</strong>
            </p>
            
            <div class="my-12 grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                <div class="bg-gray-100 rounded-2xl p-8 h-full flex flex-col justify-center">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">🎤 Voice Match Technology</h3>
                    <p>
                        Our specialized AI compares your voice to native speakers instantly. It's not just about being understood; it's about sounding authentic.
                    </p>
                </div>
                <div class="bg-gray-100 rounded-2xl p-8 h-full flex flex-col justify-center">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">⏱️ Bite-Sized Units</h3>
                    <p>
                        We know you're busy. That's why our lessons are designed to be consumed in 5-10 minute bursts. Learn while you commute, cook, or relax.
                    </p>
                </div>
            </div>

            <p>
                Whether you're looking to reconnect with your heritage, prepare for a trip, or just stretch your brain, Amter gives you the tools to speak confidently from Day 1.
            </p>
        </div>
    </section>
</div>
@endsection
