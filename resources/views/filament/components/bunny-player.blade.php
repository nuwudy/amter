<div class="relative w-full overflow-hidden rounded-xl bg-black aspect-video shadow-2xl">
    <iframe 
        src="https://iframe.mediadelivery.net/embed/{{ config('services.bunny.library_id') }}/{{ $getRecord()->video_id }}?autoplay=false&loop=false&muted=false&preload=true" 
        loading="lazy" 
        style="border:0;position:absolute;top:0;height:100%;width:100%;" 
        allow="accelerometer;gyroscope;autoplay;encrypted-media;picture-in-picture;" 
        allowfullscreen="true">
    </iframe>
</div>
