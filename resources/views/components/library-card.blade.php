@props(['record', 'admin' => false])

<div class="group relative bg-white rounded-[2rem] shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden flex flex-col w-full border border-slate-100 h-auto min-h-[350px]"
     style="border-radius: 2rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); overflow: hidden; display: flex; flex-direction: column; min-height: 350px; width: 100%; max-width: 24rem; margin-inline: auto; border: 1px solid #f1f5f9; background-color: white;">
    
    {{-- Thumbnail Section --}}
    <div class="relative h-48 overflow-hidden" style="position: relative; height: 12rem; overflow: hidden;">
        @if($record->thumbnail_path)
            <x-image :src="$record->thumbnail_path" 
                     :width="360" 
                     :height="200" 
                     alt="{{ $record->title }}"
                     class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" />
        @else
            <div class="w-full h-full bg-slate-100 flex items-center justify-center text-slate-400 font-medium" style="width: 100%; height: 100%; background-color: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #94a3b8; font-weight: 500;">
                NO IMAGE
            </div>
        @endif
        
        {{-- Gradient Overlay --}}
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-transparent to-transparent" style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(15, 23, 42, 0.6), transparent, transparent);"></div>
        
        {{-- Module Badge (Breadcrumb-ish) --}}
        <div class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full text-[10px] font-bold tracking-wider text-slate-800 uppercase shadow-sm"
             style="position: absolute; top: 1rem; right: 1rem; background-color: rgba(255, 255, 255, 0.9); backdrop-filter: blur(4px); padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 10px; font-weight: 700; letter-spacing: 0.05em; color: #1e293b; text-transform: uppercase; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);">
            {{ $record->module?->name ?? 'Course' }}
        </div>
    </div>

    {{-- Content Section --}}
    <div class="p-4 sm:p-5 flex flex-col flex-grow justify-between" style="padding: 1rem; display: flex; flex-direction: column; flex-grow: 1; justify-content: space-between;">
        <div>
            <h3 class="text-base sm:text-lg font-bold text-slate-800 leading-tight mb-1 line-clamp-2" style="font-size: 1rem; font-weight: 700; color: #1e293b; line-height: 1.25; margin-bottom: 0.25rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                {{ $record->title }}
            </h3>
            <p class="text-[10px] sm:text-xs text-slate-500 font-medium" style="font-size: 0.75rem; color: #64748b; font-weight: 500;">
                {{ $record->units->count() }} Lessons
            </p>
            @if($record->description)
                <p class="text-xs sm:text-sm text-slate-600 line-clamp-2 mt-2" style="font-size: 0.875rem; color: #475569; margin-top: 0.5rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                    {{ $record->description }}
                </p>
            @endif
        </div>

        {{-- Action Button --}}
        <div class="mt-3 sm:mt-4" style="margin-top: 0.75rem;">
            @if($admin)
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ \App\Filament\Resources\CourseSessions\CourseSessionResource::getUrl('edit', ['record' => $record]) }}" 
                       class="inline-flex items-center justify-center w-full py-2 bg-slate-50 text-slate-600 rounded-xl text-[10px] font-bold uppercase tracking-wider hover:bg-slate-100 transition-colors border border-slate-200"
                       style="display: inline-flex; align-items: center; justify-content: center; width: 100%; padding: 0.5rem; background-color: #f8fafc; color: #475569; border: 1px solid #e2e8f0; border-radius: 0.75rem; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; text-decoration: none;">
                       Edit
                    </a>
                    <a href="{{ \App\Filament\Resources\UnitResource::getUrl('index') }}?session_id={{ $record->id }}" 
                       class="inline-flex items-center justify-center w-full py-2 bg-indigo-50 text-indigo-600 rounded-xl text-[10px] font-bold uppercase tracking-wider hover:bg-indigo-100 transition-colors border border-indigo-100"
                       style="display: inline-flex; align-items: center; justify-content: center; width: 100%; padding: 0.5rem; background-color: #e0e7ff; color: #4f46e5; border: 1px solid #e0e7ff; border-radius: 0.75rem; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; text-decoration: none;">
                       Units
                    </a>
                </div>
            @else
                @if($record->module)
                    <a href="{{ route('student.modules.show', $record->module) }}" 
                        class="inline-block w-full py-2.5 bg-blue-600 text-white text-center rounded-xl text-xs font-bold uppercase tracking-widest shadow-lg shadow-blue-200 hover:bg-blue-700 hover:shadow-blue-300 transition-all transform active:scale-95"
                        style="display: inline-block; width: 100%; padding: 0.625rem 0; background-color: #2563eb; color: white; text-align: center; border-radius: 0.75rem; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; text-decoration: none; box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.5);">
                        Start Learning
                     </a>
                @else
                    <span class="inline-block w-full py-2.5 bg-slate-100 text-slate-400 text-center rounded-xl text-xs font-bold uppercase tracking-widest cursor-not-allowed"
                        style="display: inline-block; width: 100%; padding: 0.625rem 0; background-color: #f1f5f9; color: #94a3b8; text-align: center; border-radius: 0.75rem; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em;">
                        Coming Soon
                    </span>
                @endif
            @endif
        </div>
    </div>
</div>
