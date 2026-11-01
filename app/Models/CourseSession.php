<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class CourseSession extends Model
{
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($session) {
            if (empty($session->slug)) {
                $session->slug = \Illuminate\Support\Str::slug($session->title);
            }
            if (empty($session->sort_order) || (int) $session->sort_order <= 0) {
                $session->sort_order = ((int) (static::max('sort_order') ?? 0)) + 1;
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

        static::created(function ($session) {
            static::resequenceSessions((int) $session->id, (int) $session->sort_order);
        });

        static::updated(function ($session) {
            if ($session->wasChanged('sort_order')) {
                static::resequenceSessions((int) $session->id, (int) $session->sort_order);
            }
        });

        static::deleted(function () {
            static::resequenceSessions();
        });
    }

    /**
     * Re-sequence all CourseSessions so that manual Admin Order (sort_order) is strictly respected
     * and remaining sessions shift cleanly (1, 2, 3...) without duplicate order numbers.
     */
    public static function resequenceSessions(?int $anchorSessionId = null, ?int $anchorTargetOrder = null): void
    {
        $sessions = static::query()
            ->when($anchorSessionId, fn ($q) => $q->where('id', '!=', $anchorSessionId))
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get(['id', 'sort_order']);

        if ($anchorSessionId && ($anchor = static::find($anchorSessionId, ['id', 'sort_order']))) {
            $targetOrder = max(1, (int) ($anchorTargetOrder ?? $anchor->sort_order ?? ($sessions->count() + 1)));
            $insertIndex = min($sessions->count(), $targetOrder - 1);
            $sessions->splice($insertIndex, 0, [$anchor]);
        }

        $expectedOrder = 1;
        $now = now();

        foreach ($sessions as $s) {
            if ((int) $s->sort_order !== $expectedOrder) {
                DB::table('course_sessions')
                    ->where('id', $s->id)
                    ->update([
                        'sort_order' => $expectedOrder,
                        'updated_at' => $now,
                    ]);
            }
            $expectedOrder++;
        }
    }

    protected $fillable = ['module_id', 'title', 'is_hidden', 'slug', 'description', 'duration_minutes', 'thumbnail_path', 'sort_order'];

    protected $casts = [
        'is_hidden' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class)->orderBy('sort_order', 'asc');
    }
}
