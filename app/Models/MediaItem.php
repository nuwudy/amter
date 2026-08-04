<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'size' => 'integer',
        'type' => 'string', // 'image', 'video', 'audio'
    ];

    protected static function booted()
    {
        static::saving(function ($mediaItem) {
            \Illuminate\Support\Facades\Log::info("MediaItem saving event triggered", [
                'id' => $mediaItem->id,
                'path' => $mediaItem->path,
                'isDirtyPath' => $mediaItem->isDirty('path'),
                'isDirtyTitle' => $mediaItem->isDirty('title'),
                'title' => $mediaItem->title
            ]);
            if (($mediaItem->isDirty('path') || $mediaItem->isDirty('title')) && $mediaItem->path) {
                try {
                    $ext = strtolower(pathinfo($mediaItem->path, PATHINFO_EXTENSION));
                    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                    
                    \Illuminate\Support\Facades\Log::info("MediaItem saving check", [
                        'ext' => $ext,
                        'isImage' => $isImage
                    ]);

                    if ($isImage) {
                        $titleSlug = \Illuminate\Support\Str::slug($mediaItem->title);
                        \Illuminate\Support\Facades\Log::info("Running convertAndReplaceOriginal for " . $mediaItem->path . " with slug: " . $titleSlug);
                        $newPath = \App\Services\AutopilotImageService::convertAndReplaceOriginal($mediaItem->path, $titleSlug, true);
                        \Illuminate\Support\Facades\Log::info("convertAndReplaceOriginal returned: " . var_export($newPath, true));
                        if ($newPath) {
                            $mediaItem->path = $newPath;
                        }
                    }

                    $disk = \Illuminate\Support\Facades\Storage::disk('public');
                    if ($disk->exists($mediaItem->path)) {
                        $mime = $disk->mimeType($mediaItem->path);
                        $mediaItem->mime_type = $mime;
                        $mediaItem->size = $disk->size($mediaItem->path);

                        if ($mime) {
                            if (str_starts_with($mime, 'image/')) {
                                $mediaItem->type = 'image';
                            } elseif (str_starts_with($mime, 'video/')) {
                                $mediaItem->type = 'video';
                            } elseif (str_starts_with($mime, 'audio/')) {
                                $mediaItem->type = 'audio';
                            }
                        }
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning("Could not get media metadata: " . $e->getMessage());
                }
            }
        });

        static::deleting(function ($mediaItem) {
            if ($mediaItem->path) {
                try {
                    $cleanPath = ltrim($mediaItem->path, '/');
                    if (str_starts_with($cleanPath, 'storage/')) {
                        $cleanPath = substr($cleanPath, 8);
                    }
                    $disk = \Illuminate\Support\Facades\Storage::disk('public');
                    if ($disk->exists($cleanPath)) {
                        $disk->delete($cleanPath);
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning("Could not delete physical media file: " . $e->getMessage());
                }
            }
        });
    }
}
