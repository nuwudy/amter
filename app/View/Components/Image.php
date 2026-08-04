<?php

namespace App\View\Components;

use App\Services\AutopilotImageService;
use Illuminate\View\Component;

class Image extends Component
{
    public ?string $src;
    public string $alt;
    public int $width;
    public int $height;
    public bool $crop;
    public string $class;
    public bool $lazy;

    public string $webp1x;
    public string $webp2x;
    public string $fallback;

    /**
     * Create a new component instance.
     */
    public function __construct(
        ?string $src,
        string $alt = '',
        int $width = 360,
        int $height = 200,
        bool $crop = true,
        string $class = '',
        bool $lazy = true
    ) {
        $this->src = $src;
        $this->alt = $alt;
        $this->width = $width;
        $this->height = $height;
        $this->crop = $crop;
        $this->class = $class;
        $this->lazy = $lazy;

        // If not cropping, dynamically scale width/height to match original image's aspect ratio
        if (!$this->crop && $this->src) {
            $cleanPath = ltrim($this->src, '/');
            if (str_starts_with($cleanPath, 'storage/')) {
                $cleanPath = substr($cleanPath, 8);
            }
            $disk = \Illuminate\Support\Facades\Storage::disk('public');
            $absolutePath = null;
            if ($disk->exists($cleanPath)) {
                $absolutePath = $disk->path($cleanPath);
            } else {
                $publicFilePath = public_path($cleanPath);
                if (file_exists($publicFilePath)) {
                    $absolutePath = $publicFilePath;
                }
            }

            if ($absolutePath && file_exists($absolutePath)) {
                $imageInfo = @getimagesize($absolutePath);
                if ($imageInfo) {
                    $srcWidth = $imageInfo[0];
                    $srcHeight = $imageInfo[1];
                    if ($srcWidth > 0 && $srcHeight > 0) {
                        $srcRatio = $srcWidth / $srcHeight;
                        $targetRatio = $width / $height;

                        if ($srcRatio > $targetRatio) {
                            $this->width = $width;
                            $this->height = (int) round($width / $srcRatio);
                        } else {
                            $this->height = $height;
                            $this->width = (int) round($height * $srcRatio);
                        }
                    }
                }
            }
        }

        // Generate optimized standard (1x) and retina (2x) images
        if ($this->src) {
            $this->webp1x = AutopilotImageService::getOptimizedUrl($this->src, $this->width, $this->height, $this->crop);
            $this->webp2x = AutopilotImageService::getOptimizedUrl($this->src, $this->width * 2, $this->height * 2, $this->crop);
            
            // Build absolute public fallback URL
            $cleanPath = ltrim($this->src, '/');
            if (str_starts_with($cleanPath, 'storage/')) {
                $cleanPath = substr($cleanPath, 8);
            }
            $this->fallback = asset('storage/' . $cleanPath);
        } else {
            $placeholder = asset('images/placeholder.png');
            $this->webp1x = $placeholder;
            $this->webp2x = $placeholder;
            $this->fallback = $placeholder;
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.image');
    }
}
