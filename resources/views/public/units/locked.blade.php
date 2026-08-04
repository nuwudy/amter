@extends('layouts.public')

@section('content')
<div class="min-h-screen bg-gray-50 flex flex-col items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-xl max-w-lg w-full text-center overflow-hidden">
        <div class="bg-gradient-to-r from-primary-500 to-pink-500 p-8 text-white relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-full bg-white opacity-10 blur-xl scale-150"></div>
            <h2 class="text-3xl font-extrabold relative z-10">Hold on a sec!</h2>
        </div>
        
        <div class="p-10">
            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6 text-4xl">
                🔒
            </div>
            
            <h3 class="text-xl font-bold text-gray-900 mb-4">You've Discovered Premium Content</h3>
            <p class="text-gray-600 mb-8">
                This unit is part of the full <strong>{{ $course->title }}</strong> experience. 
                Login now to unlock all lessons, track your progress, and earn XP!
            </p>

            <div class="space-y-4">
                <a href="{{ route('login') }}" class="block w-full bg-primary-600 text-white font-bold py-3 rounded-xl shadow-lg hover:bg-primary-700 transform hover:scale-105 transition">
                    Login to Unlock
                </a>
                <a href="{{ route('home') }}" class="block w-full text-gray-500 hover:text-gray-800 font-medium">
                    Maybe later, take me home
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
