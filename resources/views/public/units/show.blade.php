@extends('layouts.public')
@php
    $sessionTitle = $unit->courseSession->title ?? ($course->title ?? 'Spoken English');
    $moduleName = $unit->courseSession->module->name ?? 'Spoken English Course';
    $rawUnitTitle = trim((string) $unit->title);
    $unitDisplayTitle = ctype_digit($rawUnitTitle) ? "Lesson {$rawUnitTitle}" : $rawUnitTitle;
    $seoTitle = "{$unitDisplayTitle} - {$sessionTitle} | Amter Spoken English (Malayalam to English)";
    $seoDescription = "Practice {$unitDisplayTitle} in {$sessionTitle} ({$moduleName}) on Amter Spoken English. Designed for Malayalam speakers to become as fluent as native English speakers using AI Voice Matching.";
@endphp
@section('title', $seoTitle)
@section('meta_description', $seoDescription)
@section('meta_keywords', "amter, amter spoken english, {$sessionTitle}, {$unitDisplayTitle}, spoken english malayalam, learn english in malayalam")

@section('structured_data')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "LearningResource",
  "name": @json("{$sessionTitle} - {$unitDisplayTitle}"),
  "description": @json($seoDescription),
  "learningResourceType": "Interactive Audio/Video Lesson",
  "inLanguage": ["en", "ml"],
  "educationalLevel": "Beginner to Advanced",
  "isAccessibleForFree": true,
  "provider": {
    "@@id": "{{ url('/') }}/#organization"
  },
  "isPartOf": {
    "@@type": "Course",
    "name": @json($sessionTitle),
    "provider": {
      "@@id": "{{ url('/') }}/#organization"
    }
  }
}
</script>
@endsection

@section('content')
<div class="bg-[#0f172a] min-h-screen w-full flex flex-col items-center justify-start overflow-x-hidden">
    <div class="w-full max-w-full flex justify-center">
        @if(is_array($unit->content_blocks))
            @include('filament.components.unit-renderer-student', ['blocks' => $unit->content_blocks, 'isPublic' => true, 'unit' => $unit])
        @else
            <div class="w-full max-w-4xl mx-auto py-12 px-4 flex justify-center">
                <div class="bg-white rounded-[2.5rem] shadow-2xl p-8 prose max-w-none text-gray-600 w-full">
                    {!! $unit->description ?? '' !!}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
