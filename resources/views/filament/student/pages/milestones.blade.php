<x-filament-panels::page>
    {{-- WRAPPER DIV for Livewire --}}
    <div>
        <style>
            /* Hide Default Header Elements */
            .fi-header { display: none !important; }
            .fi-header-icon { display: none !important; }
            
            /* Global SVG Constraints */
            svg { max-width: 100%; height: auto; }

            /* NUCLEAR OPTION: Hide any SVG that appears in the top header area */
            .fi-topbar svg, .fi-header svg { 
                display: none !important; 
                width: 0 !important; 
                height: 0 !important; 
            }
            
            /* Aggressive Constraints for any remaining Widget Icons (e.g. from Filament itself) */
            .fi-wi-stats-overview-stat-icon svg,
            .fi-wi-stats-overview-stat-description-icon svg,
            .fi-ta-icon svg, 
            .fi-icon svg {
                width: 1.5rem !important;
                height: 1.5rem !important;
                max-width: 1.5rem !important;
                max-height: 1.5rem !important;
            }
        </style>

        {{-- Custom Header Section (Inline Styled) --}}
        <div style="display: flex; flex-direction: column; gap: 0.5rem; justify-content: flex-end; margin-bottom: 2.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid #f1f5f9;">
            <h1 style="font-size: 1.875rem; line-height: 2.25rem; font-weight: 700; color: #0f172a; margin: 0;">
                Achievements
            </h1>
            <p style="font-size: 1.125rem; line-height: 1.75rem; color: #64748b; font-weight: 500; margin: 0;">
                Your learning milestones.
            </p>
        </div>

        {{-- Milestones Grid --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem;">
            @forelse($milestones as $milestone)
                <div style="background-color: white; padding: 1.5rem; border-radius: 1rem; border: 1px solid #f1f5f9; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); display: flex; flex-direction: column; align-items: center; text-align: center; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 1px 2px 0 rgba(0, 0, 0, 0.05)'">
                    
                    {{-- Trophy Icon --}}
                    <div style="width: 3rem; height: 3rem; border-radius: 9999px; background-color: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <svg style="width: 1.5rem !important; height: 1.5rem !important;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>

                    <h3 style="font-size: 1.125rem; font-weight: 700; color: #1e293b; margin-bottom: 0.5rem;">{{ $milestone->title }}</h3>
                    <p style="font-size: 0.875rem; color: #64748b; margin-bottom: 1.5rem; line-height: 1.5;">{{ $milestone->message }}</p>
                    
                    <span style="margin-top: auto; padding: 0.25rem 0.75rem; background-color: #f8fafc; color: #94a3b8; font-size: 0.75rem; font-weight: 600; border-radius: 9999px; border: 1px solid #e2e8f0; text-transform: uppercase; letter-spacing: 0.05em;">
                        {{ $milestone->awarded_at instanceof \Carbon\Carbon ? $milestone->awarded_at->format('M d, Y') : \Carbon\Carbon::parse($milestone->awarded_at)->format('M d, Y') }}
                    </span>
                </div>
            @empty
                <div style="grid-column: 1 / -1; display: flex; flex-direction: column; items-center: center; justify-content: center; padding: 3rem 1rem; text-align: center; background-color: #f8fafc; border-radius: 1rem; border: 2px dashed #e2e8f0;">
                    <h3 style="font-size: 1.125rem; font-weight: 700; color: #334155; margin-bottom: 0.25rem;">No Milestones Yet</h3>
                    <p style="font-size: 0.875rem; color: #94a3b8; max-width: 20rem; margin: 0 auto;">Complete lessons to unlock your achievements gallery.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>
