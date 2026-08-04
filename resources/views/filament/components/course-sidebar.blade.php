<nav class="space-y-1">
    @php
        $units = \App\Models\Unit::whereHas('courseSession', function ($query) use ($getRecord) {
                $query->where('module_id', $getRecord()->courseSession->module_id);
            })
            ->where('is_published', true)
            ->orderBy('sort_order', 'asc')
            ->get();
        $completedIds = auth()->user()->completedUnits()->pluck('unit_id')->toArray();
        $isStudent = request()->is('student/*') || request()->routeIs('filament.student.*');
    @endphp

    @foreach($units as $unit)
        <a href="{{ $isStudent ? \App\Filament\Student\Resources\Units\UnitResource::getUrl('view', ['record' => $unit->id]) : \App\Filament\Resources\UnitResource::getUrl('view', ['record' => $unit->id]) }}" 
           class="group flex items-center gap-3 p-2.5 rounded-xl transition duration-200 {{ $unit->id === $getRecord()->id ? 'bg-primary-50 text-primary-700 ring-1 ring-primary-500/10' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            
            <div class="flex justify-center items-center" style="width: 24px; height: 24px; min-width: 24px; display: flex; align-items: center; justify-content: center;">
                @if(in_array($unit->id, $completedIds))
                    <div style="width: 20px; height: 20px;">
                        <x-heroicon-s-check-circle class="text-success-500" style="width: 20px !important; height: 20px !important; display: block;" />
                    </div>
                @else
                    <div style="width: 20px; height: 20px;">
                        <x-heroicon-s-play-circle class="{{ $unit->id === $getRecord()->id ? 'text-primary-500' : 'text-gray-300 group-hover:text-gray-400' }}" style="width: 20px !important; height: 20px !important; display: block;" />
                    </div>
                @endif
            </div>

            <span class="text-xs sm:text-sm font-medium leading-snug line-clamp-2">
                {{ $unit->title }}
            </span>
        </a>
    @endforeach
</nav>
