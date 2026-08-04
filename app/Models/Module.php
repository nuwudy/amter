<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Module extends Model
{
    protected $fillable = ['course_id', 'name', 'slug', 'image_path', 'thumbnail', 'description', 'sort_order', 'is_active'];

    protected static function booted()
    {
        static::saving(function ($module) {
            $titleSlug = \Illuminate\Support\Str::slug($module->name);
            if (($module->isDirty('image_path') || $module->isDirty('name')) && $module->image_path) {
                $newImg = \App\Services\AutopilotImageService::convertAndReplaceOriginal($module->image_path, $titleSlug);
                if ($newImg) {
                    $module->image_path = $newImg;
                }
            }
            if (($module->isDirty('thumbnail') || $module->isDirty('name')) && $module->thumbnail) {
                $newThumb = \App\Services\AutopilotImageService::convertAndReplaceOriginal($module->thumbnail, $titleSlug);
                if ($newThumb) {
                    $module->thumbnail = $newThumb;
                }
            }
        });
    }

    public function course(): BelongsTo {
        return $this->belongsTo(Course::class);
    }

    public function courseSessions(): HasMany {
        return $this->hasMany(CourseSession::class)->orderBy('sort_order', 'asc');
    }

    public function units()
    {
        return $this->hasManyThrough(Unit::class, CourseSession::class);
    }

    public function getProgressAttribute(): int
    {
        $user = auth()->user();
        if (!$user) return 0;

        $totalUnits = $this->units()->where('is_published', true)->count();
        if ($totalUnits === 0) return 0;

        $completedCount = $user->completedUnits()
            ->whereIn('units.id', $this->units()->select('units.id'))
            ->count();

        return (int) round(($completedCount / $totalUnits) * 100);
    }
}