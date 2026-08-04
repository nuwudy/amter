<div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
    <div class="mb-2 text-xs font-bold text-gray-500 uppercase">Pronunciation Audio</div>
    <audio controls class="w-full">
        <source src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($getRecord()->audio_url) }}" type="audio/mpeg">
    </audio>
