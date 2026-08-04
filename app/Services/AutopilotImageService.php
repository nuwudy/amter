<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AutopilotImageService
{
    /**
     * Get optimized image URL, dynamically generating a WebP cache if needed.
     *
     * @param string|null $path Original image path (e.g. 'thumbnails/xxx.png' or relative storage paths)
     * @param int $width Target width in pixels
     * @param int $height Target height in pixels
     * @param bool $crop Whether to crop to fit, or resize proportionally
     * @return string Public asset URL of the optimized WebP or fallback to original
     */
    public static function getOptimizedUrl(?string $path, int $width, int $height, bool $crop = true): string
    {
        if (empty($path)) {
            return asset('images/placeholder.png'); // safe fallback placeholder
        }

        // Clean path (remove starting slash or full URL segments if any)
        $cleanPath = ltrim($path, '/');
        
        // Strip 'storage/' prefix if Filament or upload paths contain it
        if (str_starts_with($cleanPath, 'storage/')) {
            $cleanPath = substr($cleanPath, 8);
        }

        $disk = Storage::disk('public');

        // Check if the original file exists in public storage
        if (!$disk->exists($cleanPath)) {
            // Check if it exists directly in public/ folder (like static public/images/xxx.jpg)
            $publicFilePath = public_path($cleanPath);
            if (file_exists($publicFilePath)) {
                return self::processLocalFile($publicFilePath, $cleanPath, $width, $height, $crop);
            }

            // If not found anywhere, return original asset URL as fallback
            return asset('storage/' . $cleanPath);
        }

        $absoluteSourcePath = $disk->path($cleanPath);
        return self::processLocalFile($absoluteSourcePath, $cleanPath, $width, $height, $crop);
    }

    /**
     * Process a local image file using GD, generating cached WebP images.
     */
    private static function processLocalFile(string $sourcePath, string $relativePath, int $width, int $height, bool $crop): string
    {
        // 1. Verify GD is available
        if (!extension_loaded('gd') || !function_exists('imagewebp')) {
            Log::warning("GD or WebP support is not enabled in PHP. Autopilot Image serving original unoptimized file.");
            return asset(str_starts_with($sourcePath, public_path()) ? $relativePath : 'storage/' . $relativePath);
        }

        // 2. Obtain file details for cache busting
        $lastModified = filemtime($sourcePath);
        $fileSize = filesize($sourcePath);
        
        // Hash includes width, height, crop mode, size, and mod time to guarantee fresh cache on re-uploads
        $title = null;
        
        // 1. Try to query database for MediaItem title based on the relative path
        try {
            $title = \App\Models\MediaItem::where('path', $relativePath)
                ->orWhere('path', 'storage/' . $relativePath)
                ->value('title');
        } catch (\Exception $e) {
            // DB not loaded or table doesn't exist yet
        }

        // 2. Try to query Unit database for the Unit title if this is a direct upload
        if (empty($title)) {
            $filename = pathinfo($relativePath, PATHINFO_FILENAME);
            if (preg_match('/^[0-9A-Z]{26}$/', $filename)) {
                try {
                    $unitTitle = \App\Models\Unit::where('thumbnail', $relativePath)
                        ->orWhere('thumbnail', 'storage/' . $relativePath)
                        ->orWhere('content_blocks', 'like', '%' . $filename . '%')
                        ->value('title');
                    if ($unitTitle) {
                        $title = $unitTitle;
                    }
                } catch (\Exception $e) {
                }
            }
        }

        // 3. Fallback to slugified filename
        if (empty($title)) {
            $title = pathinfo($relativePath, PATHINFO_FILENAME);
        }

        // 4. Generate clean SEO-friendly slug
        $slug = \Illuminate\Support\Str::slug($title);
        if (empty($slug)) {
            $slug = 'image';
        }
        $slug = substr($slug, 0, 40);

        // 5. Generate unique cache buster hash (first 8 chars of full MD5)
        $fullHash = md5($relativePath . '_' . $width . 'x' . $height . '_' . ($crop ? 'crop' : 'fit') . '_' . $lastModified . '_' . $fileSize);
        $shortHash = substr($fullHash, 0, 8);

        // 6. Assemble cached filename
        $cachedFileName = $slug . '-' . $width . 'x' . $height . '-' . ($crop ? 'crop' : 'fit') . '-' . $shortHash . '.webp';
        
        // Cache directory inside public disk
        $cacheDir = 'cache/images';
        $cachedRelativePath = $cacheDir . '/' . $cachedFileName;
        $disk = Storage::disk('public');

        // 3. If cache file already exists, return its URL immediately
        if ($disk->exists($cachedRelativePath)) {
            return asset('storage/' . $cachedRelativePath);
        }

        // 4. Create cache directory if it doesn't exist
        if (!$disk->exists($cacheDir)) {
            $disk->makeDirectory($cacheDir);
        }

        $absoluteCachePath = $disk->path($cachedRelativePath);

        try {
            // 5. Open source image based on format
            $imageInfo = getimagesize($sourcePath);
            if (!$imageInfo) {
                return asset('storage/' . $relativePath);
            }

            $mime = $imageInfo['mime'];
            switch ($mime) {
                case 'image/jpeg':
                case 'image/jpg':
                    $sourceImage = imagecreatefromjpeg($sourcePath);
                    break;
                case 'image/png':
                    $sourceImage = imagecreatefrompng($sourcePath);
                    break;
                case 'image/webp':
                    $sourceImage = imagecreatefromwebp($sourcePath);
                    break;
                case 'image/gif':
                    $sourceImage = imagecreatefromgif($sourcePath);
                    break;
                default:
                    // Unsupported source format (e.g. SVG which is already perfectly crisp)
                    return asset(str_starts_with($sourcePath, public_path()) ? $relativePath : 'storage/' . $relativePath);
            }

            if (!$sourceImage) {
                return asset('storage/' . $relativePath);
            }

            // 6. Calculate dimensions and crop bounds
            $srcWidth = imagesx($sourceImage);
            $srcHeight = imagesy($sourceImage);

            if ($crop) {
                $targetW = $width;
                $targetH = $height;
            } else {
                $srcRatio = $srcWidth / $srcHeight;
                $targetRatio = $width / $height;

                if ($srcRatio > $targetRatio) {
                    $targetW = $width;
                    $targetH = (int) round($width / $srcRatio);
                } else {
                    $targetH = $height;
                    $targetW = (int) round($height * $srcRatio);
                }
            }

            // Create target canvas
            $targetImage = imagecreatetruecolor($targetW, $targetH);

            // Maintain transparency for PNG and WebP
            imagealphablending($targetImage, false);
            imagesavealpha($targetImage, true);

            // Transparent background
            $transparentColor = imagecolorallocatealpha($targetImage, 0, 0, 0, 127);
            imagefill($targetImage, 0, 0, $transparentColor);

            if ($crop) {
                // Center crop calculations (fill target box exactly)
                $srcRatio = $srcWidth / $srcHeight;
                $targetRatio = $width / $height;

                if ($srcRatio >= $targetRatio) {
                    // Source is wider than target ratio
                    $newWidth = $srcHeight * $targetRatio;
                    $srcX = ($srcWidth - $newWidth) / 2;
                    $srcY = 0;
                    $srcWidth = $newWidth;
                } else {
                    // Source is taller than target ratio
                    $newHeight = $srcWidth / $targetRatio;
                    $srcX = 0;
                    $srcY = ($srcHeight - $newHeight) / 2;
                    $srcHeight = $newHeight;
                }

                imagecopyresampled(
                    $targetImage, $sourceImage,
                    0, 0, $srcX, $srcY,
                    $width, $height, $srcWidth, $srcHeight
                );
            } else {
                // Proportionally fit inside target box without cropping
                imagecopyresampled(
                    $targetImage, $sourceImage,
                    0, 0, 0, 0,
                    $targetW, $targetH, $srcWidth, $srcHeight
                );
            }

            // 7. Save as WebP at Quality = 85 (optimal balance of sharpness & storage)
            imagewebp($targetImage, $absoluteCachePath, 85);

            // Free memory
            imagedestroy($sourceImage);
            imagedestroy($targetImage);

            return asset('storage/' . $cachedRelativePath);

        } catch (\Exception $e) {
            Log::error("Autopilot Image Service Error: " . $e->getMessage());
            return asset(str_starts_with($sourcePath, public_path()) ? $relativePath : 'storage/' . $relativePath);
        }
    }

    public static function convertAndReplaceOriginal(?string $path, ?string $titleSlug = null, bool $allowMediaLibraryOverwrite = false): ?string
    {
        if (empty($path)) {
            return null;
        }

        // Clean path (remove starting slash or full URL segments if any)
        $cleanPath = ltrim($path, '/');
        if (str_starts_with($cleanPath, 'storage/')) {
            $cleanPath = substr($cleanPath, 8);
        }

        // Safeguard: If the file is in media-library and overwrite is not explicitly allowed, do not touch it!
        if (!$allowMediaLibraryOverwrite) {
            if (str_starts_with($cleanPath, 'media-library/') || str_starts_with($cleanPath, 'storage/media-library/')) {
                return $cleanPath;
            }
        }

        $disk = Storage::disk('public');

        // Determine WebP filename (replace old extension with .webp)
        $pathInfo = pathinfo($cleanPath);
        $dirname = $pathInfo['dirname'] === '.' ? '' : $pathInfo['dirname'] . '/';
        
        if (!empty($titleSlug)) {
            $filename = $pathInfo['filename'];
            $suffix = '';
            // 1. Check if the filename is a full ULID (26 chars of uppercase alphanumeric)
            if (preg_match('/^[0-9A-Z]{26}$/', $filename)) {
                $suffix = substr($filename, 0, 8);
            } 
            // 2. Check if the filename ends with a hyphen followed by 8 uppercase alphanumeric characters
            elseif (preg_match('/-([0-9A-Z]{8})$/', $filename, $matches)) {
                $suffix = $matches[1];
            } 
            // 3. Fallback: just use first 8 characters of filename, or generate a random one if too short
            else {
                $suffix = substr(md5($filename), 0, 8);
            }
            
            $webpRelativePath = $dirname . $titleSlug . '-' . $suffix . '.webp';
        } else {
            $webpRelativePath = $dirname . $pathInfo['filename'] . '.webp';
        }
        
        $absoluteWebpPath = $disk->path($webpRelativePath);

        // If the source file doesn't exist, but the target optimized WebP file ALREADY exists,
        // we can heal any out-of-sync database records by returning the target path immediately!
        if (!$disk->exists($cleanPath)) {
            if ($disk->exists($webpRelativePath)) {
                return $webpRelativePath;
            }
            return null;
        }

        $sourcePath = $disk->path($cleanPath);

        // If the file is already WebP and has the exact target name, return it immediately!
        if (strtolower($pathInfo['extension'] ?? '') === 'webp' && $sourcePath === $absoluteWebpPath) {
            return $webpRelativePath;
        }

        // 1. Verify GD is available
        if (!extension_loaded('gd') || !function_exists('imagewebp')) {
            return null;
        }

        try {
            $imageInfo = @getimagesize($sourcePath);
            if (!$imageInfo) {
                return null;
            }

            $mime = $imageInfo['mime'];
            switch ($mime) {
                case 'image/jpeg':
                case 'image/jpg':
                    $sourceImage = @imagecreatefromjpeg($sourcePath);
                    break;
                case 'image/png':
                    $sourceImage = @imagecreatefrompng($sourcePath);
                    break;
                case 'image/webp':
                    $sourceImage = @imagecreatefromwebp($sourcePath);
                    break;
                case 'image/gif':
                    $sourceImage = @imagecreatefromgif($sourcePath);
                    break;
                default:
                    return null; // Unsupported format for conversion
            }

            if (!$sourceImage) {
                return null;
            }

            $srcWidth = imagesx($sourceImage);
            $srcHeight = imagesy($sourceImage);

            // Cap dimensions at 1600x1000 to keep it incredibly crisp while saving massive space
            $maxWidth = 1600;
            $maxHeight = 1000;
            
            $targetWidth = $srcWidth;
            $targetHeight = $srcHeight;

            if ($srcWidth > $maxWidth || $srcHeight > $maxHeight) {
                $ratio = $srcWidth / $srcHeight;
                if ($ratio > ($maxWidth / $maxHeight)) {
                    $targetWidth = $maxWidth;
                    $targetHeight = (int) round($maxWidth / $ratio);
                } else {
                    $targetHeight = $maxHeight;
                    $targetWidth = (int) round($maxHeight * $ratio);
                }
            }

            $targetImage = imagecreatetruecolor($targetWidth, $targetHeight);

            // Maintain transparency for PNG and WebP
            imagealphablending($targetImage, false);
            imagesavealpha($targetImage, true);

            $transparentColor = imagecolorallocatealpha($targetImage, 0, 0, 0, 127);
            imagefill($targetImage, 0, 0, $transparentColor);

            imagecopyresampled(
                $targetImage, $sourceImage,
                0, 0, 0, 0,
                $targetWidth, $targetHeight, $srcWidth, $srcHeight
            );

            // Save WebP at Quality = 85 (optimal balance of sharpness & storage)
            imagewebp($targetImage, $absoluteWebpPath, 85);

            imagedestroy($sourceImage);
            imagedestroy($targetImage);

            // Delete original file if it had a different name/extension
            if ($sourcePath !== $absoluteWebpPath) {
                @unlink($sourcePath);
            }

            return $webpRelativePath;

        } catch (\Exception $e) {
            Log::error("Autopilot convertAndReplaceOriginal Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Optimize all image blocks inside a Filament builder JSON content_blocks array.
     */
    public static function optimizeContentBlocks(array $blocks, ?string $titleSlug = null): array
    {
        foreach ($blocks as $i => $block) {
            if (isset($block['type']) && $block['type'] === 'image' && isset($block['data'])) {
                // Optimize direct upload url
                if (!empty($block['data']['url'])) {
                    $newUrl = self::convertAndReplaceOriginal($block['data']['url'], $titleSlug);
                    if ($newUrl) {
                        $blocks[$i]['data']['url'] = $newUrl;
                    }
                }
                // Optimize custom url if it's a relative path in public storage
                if (!empty($block['data']['custom_url'])) {
                    $cUrl = $block['data']['custom_url'];
                    if (!str_starts_with($cUrl, 'http')) {
                        $newCUrl = self::convertAndReplaceOriginal($cUrl, $titleSlug);
                        if ($newCUrl) {
                            $blocks[$i]['data']['custom_url'] = $newCUrl;
                        }
                    }
                }
            }
        }
        return $blocks;
    }
}
