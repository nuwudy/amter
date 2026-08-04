@php
    $record = $getState();
    $type = $getRecord()->type;
    $content = $getRecord()->content;
@endphp

<div class="space-y-4">
    @if($type === 'video_clip')
        <div style="position:relative;padding-top:56.25%;">
            <iframe src="{{ $content }}" 
                    loading="lazy" 
                    style="border:0;position:absolute;top:0;height:100%;width:100%;" 
                    allow="accelerometer;gyroscope;autoplay;encrypted-media;picture-in-picture;" 
                    allowfullscreen="true"></iframe>
        </div>
    @elseif($type === 'vocab_card')
        <div class="bg-amber-50 dark:bg-amber-900/20 p-6 rounded-xl border border-amber-200 dark:border-amber-700">
            <h3 class="text-xl font-bold text-amber-900 dark:text-amber-100 mb-2">Vocabulary</h3>
            <div class="prose dark:prose-invert">
                {!! nl2br(e($content)) !!}
            </div>
        </div>
    @else
        <div class="p-4 bg-gray-100 dark:bg-gray-800 rounded-lg">
            <p class="text-gray-600 dark:text-gray-400 italic">Content preview for {{ $type }} is coming soon.</p>
            <div class="mt-2 text-sm">
                {{ $content }}
            </div>
        </div>
    @endif
</div>
