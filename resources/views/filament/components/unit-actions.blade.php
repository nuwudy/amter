@php
    // Debug: Ensure record is present
    if (!isset($record)) {
        echo '<div class="p-4 bg-red-500 text-white font-bold">ERROR: $record is missing in unit-actions.blade.php</div>';
        return;
    }
    $next = $record->nextUnit();
    $previous = $record->previousUnit();
@endphp

<!-- DEBUG MARKER: Actions View is Loading -->
    {{-- Navigation Buttons --}}
    <div class="flex flex-col sm:flex-row gap-4 w-full">
        @if($previous)
            <a href="{{ \App\Filament\Resources\UnitResource::getUrl('view', ['record' => $previous->id]) }}" 
               class="flex-1 flex flex-col items-center justify-center p-5 bg-slate-50 hover:bg-slate-100 rounded-2xl border border-slate-100 transition-all active:scale-95 group">
                <div class="flex items-center gap-2 text-slate-400 mb-1">
                    <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    <span class="text-[10px] font-black uppercase tracking-widest">Previous</span>
                </div>
                <span class="text-sm font-bold text-slate-700 text-center line-clamp-1">{{ $previous->title ?? 'Previous Lesson' }}</span>
            </a>
        @else
            <div class="flex-1 hidden sm:block"></div> {{-- Spacer --}}
        @endif

        @if($next)
            <a href="{{ \App\Filament\Resources\UnitResource::getUrl('view', ['record' => $next->id]) }}" 
               class="flex-1 flex flex-col items-center justify-center p-5 bg-blue-50 hover:bg-blue-100 rounded-2xl border border-blue-100 transition-all active:scale-95 group shadow-[0_8px_16px_-6px_rgba(59,130,246,0.2)]">
                <div class="flex items-center gap-2 text-blue-500 mb-1">
                    <span class="text-[10px] font-black uppercase tracking-widest">Next Lesson</span>
                    <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </div>
                <span class="text-sm font-bold text-blue-700 text-center line-clamp-1">{{ $next->title ?? 'Next Lesson' }}</span>
            </a>
        @endif
    </div>

</div>
