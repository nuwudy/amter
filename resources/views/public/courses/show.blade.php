@extends('layouts.public')
@section('title', $course->title . ' - Spoken English Mastery')
@section('meta_description', Str::limit($course->description, 160))

@section('structured_data')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Course",
  "name": "{{ $course->title }}",
  "description": "{{ $course->description }}",
  "provider": {
    "@type": "Organization",
    "name": "Amter",
    "sameAs": "{{ url('/') }}"
  }
}
</script>
@endsection

@section('content')
<div class="bg-primary-50 min-h-screen pb-20">
    <!-- Course Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="md:flex md:items-center md:gap-12">
                <div class="hidden md:block w-1/3">
                    <img src="{{ $course->thumbnail_url ?? asset('images/chip-guy.jpg') }}" alt="{{ $course->title }}" class="rounded-xl shadow-lg rotate-3 border-4 border-white">
                </div>
                <div class="md:w-2/3">
                    <span class="inline-block bg-primary-100 text-primary-700 px-3 py-1 rounded-full text-sm font-semibold mb-4">Course</span>
                    <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-6">{{ $course->title }}</h1>
                    <p class="text-lg text-gray-600 mb-8 leading-relaxed">{{ $course->description }}</p>
                    
                    <div class="flex flex-wrap gap-4">
                        <a href="#curriculum" class="bg-primary-600 text-white px-8 py-3 rounded-full font-bold hover:bg-primary-700 transition shadow-md">
                            Start Learning
                        </a>
                        <span class="flex items-center text-gray-500 font-medium">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Self-Paced
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Why Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="text-center mb-16">
            <h2 class="text-3xl font-bold text-gray-900 sm:text-4xl">Why take this course?</h2>
            <p class="mt-4 text-lg text-gray-600">Here is what makes this learning experience unique and effective.</p>
        </div>

        <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
            <!-- Card 1 -->
            <div class="bg-white rounded-3xl p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all duration-300 border border-gray-100 hover:-translate-y-1">
                <div class="w-14 h-14 bg-rose-100 rounded-2xl flex items-center justify-center mb-6 text-rose-600 rotate-3">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Loved by Students</h3>
                <p class="text-gray-500 leading-relaxed">Join thousands of happy students who have transformed their skills with this curriculum.</p>
            </div>

            <!-- Card 2 -->
            <div class="bg-white rounded-3xl p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all duration-300 border border-gray-100 hover:-translate-y-1">
                <div class="w-14 h-14 bg-violet-100 rounded-2xl flex items-center justify-center mb-6 text-violet-600 -rotate-2">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Fast-Track Learning</h3>
                <p class="text-gray-500 leading-relaxed">Concise, action-oriented lessons designed to get you results in record time.</p>
            </div>

            <!-- Card 3 -->
            <div class="bg-white rounded-3xl p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all duration-300 border border-gray-100 hover:-translate-y-1">
                <div class="w-14 h-14 bg-amber-100 rounded-2xl flex items-center justify-center mb-6 text-amber-600 rotate-1">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Practical Focus</h3>
                <p class="text-gray-500 leading-relaxed">Learn by doing with real-world examples and hands-on exercises in every module.</p>
            </div>
        </div>
    </div>

    <!-- Breadcrumbs -->
    <div class="bg-gray-50 border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-4">
                    <li>
                        <div class="flex items-center">
                            <a href="/" class="text-sm font-medium text-gray-500 hover:text-gray-700">Home</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 flex-shrink-0 text-gray-300" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                            </svg>
                            <a href="#" class="ml-4 text-sm font-medium text-gray-500 hover:text-gray-700">Courses</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 flex-shrink-0 text-gray-300" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                            </svg>
                            <span class="ml-4 text-sm font-bold text-gray-900" aria-current="page">{{ $course->title }}</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Curriculum -->
    <div id="curriculum" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <h2 class="text-3xl font-bold text-gray-900 mb-8 tracking-tight">Course Curriculum</h2>

        <div class="space-y-8">
            @foreach($course->modules as $module)
            <div x-data="{ expanded: {{ $loop->first ? 'true' : 'false' }} }" class="bg-white rounded-3xl border border-gray-200 shadow-sm overflow-hidden">
                <!-- Module Header (Clickable) -->
                <button @click="expanded = !expanded" class="w-full px-8 py-6 bg-white hover:bg-gray-50 transition-colors flex items-center justify-between focus:outline-none group">
                    <div class="flex items-center gap-6">
                        <div class="h-14 w-14 rounded-2xl bg-primary-100 flex items-center justify-center text-primary-600 group-hover:scale-110 transition-transform">
                             @if($module->thumbnail)
                                <img src="{{ asset('storage/' . $module->thumbnail) }}" class="w-full h-full object-cover rounded-2xl">
                             @else
                                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                             @endif
                        </div>
                        <div class="text-left">
                            <h3 class="font-bold text-xl text-gray-900">{{ $module->name }}</h3>
                            <p class="text-sm text-gray-500 font-medium">{{ $module->courseSessions->count() }} Sessions • {{ $module->units->count() }} Lessons</p>
                        </div>
                    </div>
                    <span class="transform transition-transform duration-300 text-gray-400 bg-gray-100 rounded-full p-2 group-hover:bg-gray-200" :class="{'rotate-180': expanded}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </span>
                </button>
                
                <!-- Expanded Content (Sessions & Units) -->
                <div x-show="expanded" x-collapse style="display: none;" class="bg-gray-50/30 border-t border-gray-100">
                    <div class="p-6 md:p-8 space-y-8">
                        @foreach($module->courseSessions as $session)
                            <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden">
                                {{-- Session Header --}}
                                <div class="bg-gray-50/80 px-8 py-4 border-b border-gray-100 flex justify-between items-center">
                                    <h4 class="font-bold text-lg text-gray-800">{{ $session->title }}</h4>
                                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wide">{{ $session->units->count() }} UNITS</span>
                                </div>

                                {{-- Units List --}}
                                <div class="p-4 sm:p-6 space-y-3">
                                    @foreach($session->units as $unit)
                                        @php
                                            $isFree = $unit->is_free_sample;
                                        @endphp

                                        <div class="group relative flex items-center gap-4 p-4 rounded-2xl transition-all duration-200 border {{ $isFree ? 'bg-white hover:border-primary-200 border-gray-100 hover:shadow-md cursor-pointer' : 'bg-gray-50/50 border-transparent opacity-80 hover:opacity-100' }}">
                                            
                                            {{-- Status Icon / Number --}}
                                            <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center rounded-xl {{ $isFree ? 'bg-primary-50 text-primary-600' : 'bg-gray-200 text-gray-400' }}">
                                                @if($isFree)
                                                    <svg class="w-6 h-6 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                @else
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                                @endif
                                            </div>

                                            {{-- Content Info --}}
                                            <div class="flex-grow min-w-0">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <h5 class="font-bold text-gray-900 truncate text-base">
                                                        {{ $unit->title }}
                                                    </h5>
                                                    @if($isFree)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide bg-green-100 text-green-700">
                                                            Free Preview
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="flex items-center gap-3 text-xs font-medium text-gray-500">
                                                    <span class="flex items-center gap-1">
                                                        @if($unit->video_id)
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                                            Video Lesson
                                                        @else
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                            Interactive Lesson
                                                        @endif
                                                    </span>
                                                    <span>•</span>
                                                    <span>{{ $unit->video_id ? '10 mins' : '5 mins' }}</span>
                                                </div>
                                            </div>

                                            {{-- Action --}}
                                            <div class="flex-shrink-0">
                                                {{-- Link logic --}}
                                                <a href="{{ route('public.unit.show', [$course, $unit]) }}" class="absolute inset-0 z-10 focus:outline-none">
                                                    <span class="sr-only">{{ $isFree ? 'Watch Preview' : 'View Lesson' }}</span>
                                                </a>

                                                @if($isFree)
                                                    <div class="h-8 w-8 rounded-full bg-primary-100 flex items-center justify-center text-primary-600 group-hover:bg-primary-600 group-hover:text-white transition-colors">
                                                        <svg class="w-4 h-4 transform group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                                    </div>
                                                @else
                                                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider px-3 py-1 bg-gray-100 rounded-lg flex items-center gap-1">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                                        Locked
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
