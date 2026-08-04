<picture>
    <source srcset="{{ $webp2x }} 2x, {{ $webp1x }} 1x" type="image/webp">
    <img src="{{ $fallback }}" 
         width="{{ $width }}" 
         height="{{ $height }}" 
         alt="{{ $alt }}" 
         class="{{ $class }}" 
         @if($lazy) loading="lazy" @endif
         style="@if($crop) aspect-ratio: {{ $width }} / {{ $height }}; object-fit: cover; @endif width: 100%; height: auto;">
</picture>
