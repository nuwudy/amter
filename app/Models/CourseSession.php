<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseSession extends Model
{
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($session) {
            if (empty($session->slug)) {
                $session->slug = \Illuminate\Support\Str::slug($session->title);
            }
        });

        static::saving(function ($session) {
            $titleSlug = \Illuminate\Support\Str::slug($session->title);
            if (($session->isDirty('thumbnail_path') || $session->isDirty('title')) && $session->thumbnail_path) {
                $newThumbnail = \App\Services\AutopilotImageService::convertAndReplaceOriginal($session->thumbnail_path, $titleSlug);
                if ($newThumbnail) {
                    $session->thumbnail_path = $newThumbnail;
                }
            }
        });
    }

    protected $fillable = ['module_id', 'title', 'slug', 'description', 'duration_minutes', 'thumbnail_path', 'sort_order'];

    public function module(): BelongsTo {
        return $this->belongsTo(Module::class);
    }
    public function units(): HasMany {
        return $this->hasMany(Unit::class)->orderBy('sort_order', 'asc');
    }
}