<div style="border-radius: 2.5rem; overflow: hidden; background-color: white; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); border: 1px solid #f1f5f9; transition: transform 0.2s; height: 100%; display: flex; flex-direction: column;">
    <div style="position: relative; height: 16rem; overflow: hidden;">
        @if($getRecord()->thumbnail)
            <img src="{{ asset('storage/' . $getRecord()->thumbnail) }}" style="width: 100%; height: 100%; object-fit: cover;" alt="{{ $getRecord()->name }}">
        @else
            <div style="width: 100%; height: 100%; background-color: #e2e8f0; display: flex; align-items: center; justify-content: center; color: #94a3b8; font-weight: bold;">NO IMAGE</div>
        @endif
        
        <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(15, 23, 42, 0.8), transparent);"></div>
        
        <div style="position: absolute; bottom: 1.5rem; left: 1.5rem; color: white; pointer-events: none;">
            <h3 style="font-size: 1.5rem; line-height: 2rem; font-weight: 700;">{{ $getRecord()->name }}</h3>
            <p style="font-size: 0.75rem; color: #cbd5e1; text-transform: uppercase; letter-spacing: 0.1em; margin-top: 0.25rem;">{{ $getRecord()->units()->count() }} Units</p>
        </div>
    </div>
    <div style="padding: 1.5rem;">
        <p style="color: #64748b; font-size: 0.875rem; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">{{ $getRecord()->description }}</p>
    </div>
</div>
