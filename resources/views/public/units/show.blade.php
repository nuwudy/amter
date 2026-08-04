@extends('layouts.public')
@section('title', $unit->title . ' - Lesson Preview')
@section('meta_description', 'Preview our lesson: ' . $unit->title . '. Start learning to speak English like a native speaker with Amter.')

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
