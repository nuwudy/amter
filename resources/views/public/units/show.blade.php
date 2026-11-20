@php
    $sessionTitle = $unit->courseSession->title ?? ($course->title ?? 'Spoken English');
    $moduleName = $unit->courseSession->module->name ?? 'Spoken English Course';
    $rawUnitTitle = trim((string) $unit->title);
    $unitDisplayTitle = ctype_digit($rawUnitTitle) ? "Lesson {$rawUnitTitle}" : $rawUnitTitle;
    $seoTitle = "{$unitDisplayTitle} - {$sessionTitle} | Amter Spoken English (Malayalam to English)";
    $seoDescription = "Practice {$unitDisplayTitle} in {$sessionTitle} ({$moduleName}) on Amter Spoken English. Designed for Malayalam speakers to become as fluent as native English speakers using AI Voice Matching.";
    $reqPath = request()->getPathInfo();
    $canonicalUrl = app()->environment('local')
        ? url()->current()
        : 'https://amter.in' . ($reqPath === '/' ? '' : rtrim($reqPath, '/'));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDescription }}">
    <meta name="keywords" content="amter, amter spoken english, {{ $sessionTitle }}, {{ $unitDisplayTitle }}, spoken english malayalam, learn english in malayalam">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <link rel="canonical" href="{{ $canonicalUrl }}">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Amter Spoken English">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:image" content="{{ asset('images/hero-chips.jpg') }}">

    <!-- Structured Data -->
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

    <!-- Fonts & Assets -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png?v=3') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="antialiased bg-slate-50 text-slate-900 overflow-hidden m-0 p-0">
    @include('filament.components.unit-chat-player', [
        'unit' => $unit,
        'course' => $course ?? null,
        'isFullscreen' => true,
    ])
</body>
</html>
