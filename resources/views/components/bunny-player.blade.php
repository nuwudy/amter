@props(['videoId'])

<div class="video-container rounded-xl overflow-hidden shadow-lg bg-black">
    <div style="position:relative;padding-top:56.25%;">
        <iframe src="https://iframe.mediadelivery.net/embed/569307/{{ $videoId }}?autoplay=false" 
                loading="lazy" 
                style="border:0;position:absolute;top:0;height:100%;width:100%;" 
                allow="accelerometer;gyroscope;autoplay;encrypted-media;picture-in-picture;" 
                allowfullscreen="true"></iframe>
    </div>
</div>
