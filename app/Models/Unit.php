<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class Unit extends Model
{
    public ?int $previousCourseSessionId = null;

    protected static function booted()
    {
        static::saving(function ($unit) {
            // Keep numeric / "Lesson N" Title and Order (sort_order) synchronized when inserting or editing
            $titleOrder = static::extractOrderFromTitle($unit->title);

            if ($unit->exists) {
                $originalTitle = trim((string) ($unit->getOriginal('title') ?? ''));
                $origTitleOrder = static::extractOrderFromTitle($originalTitle);

                if ($unit->isDirty('title') && !$unit->isDirty('sort_order') && $titleOrder !== null) {
                    $unit->sort_order = $titleOrder;
                } elseif ($unit->isDirty('sort_order') && !$unit->isDirty('title') && ($originalTitle === '' || $origTitleOrder !== null)) {
                    $unit->title = static::formatTitleForOrder($originalTitle, (int) $unit->sort_order);
                }
            } elseif ($unit->course_session_id && $titleOrder !== null && $titleOrder !== (int) $unit->sort_order) {
                $maxOrder = static::where('course_session_id', $unit->course_session_id)->max('sort_order');
                $defaultNext = ($maxOrder ?? 0) + 1;

                if ((int) $unit->sort_order === $defaultNext) {
                    $unit->sort_order = $titleOrder;
                } elseif ($titleOrder === $defaultNext) {
                    $unit->title = static::formatTitleForOrder($unit->title, (int) $unit->sort_order);
                }
            }

            $titleSlug = \Illuminate\Support\Str::slug($unit->title);
            if (($unit->isDirty('content_blocks') || $unit->isDirty('title')) && is_array($unit->content_blocks)) {
                $unit->content_blocks = \App\Services\AutopilotImageService::optimizeContentBlocks($unit->content_blocks, $titleSlug);
            }
            if (($unit->isDirty('thumbnail') || $unit->isDirty('title')) && $unit->thumbnail) {
                $cleanThumb = ltrim($unit->thumbnail, '/');
                if (str_starts_with($cleanThumb, 'storage/')) {
                    $cleanThumb = substr($cleanThumb, 8);
                }
                // Do not optimize if it's a shared Media Library item
                if (!str_starts_with($cleanThumb, 'media-library/')) {
                    $newThumbnail = \App\Services\AutopilotImageService::convertAndReplaceOriginal($unit->thumbnail, $titleSlug);
                    if ($newThumbnail) {
                        $unit->thumbnail = $newThumbnail;
                    }
                }
            }
        });

        static::updating(function ($unit) {
            $origSession = $unit->getOriginal('course_session_id');
            $unit->previousCourseSessionId = $origSession ? (int) $origSession : null;
        });

        static::created(function ($unit) {
            if ($unit->course_session_id) {
                static::resequenceSession((int) $unit->course_session_id, (int) $unit->id, (int) $unit->sort_order);
            }
        });

        static::updated(function ($unit) {
            if ($unit->wasChanged(['sort_order', 'course_session_id'])) {
                if ($unit->previousCourseSessionId && $unit->previousCourseSessionId !== (int) $unit->course_session_id) {
                    static::resequenceSession($unit->previousCourseSessionId);
                }
                if ($unit->course_session_id) {
                    static::resequenceSession((int) $unit->course_session_id, (int) $unit->id, (int) $unit->sort_order);
                }
            }

            if ($unit->wasChanged('is_published') && $unit->is_published) {
                // Find all users enrolled in any course
                $users = \App\Models\User::whereHas('courses')->get();

                if ($users->isNotEmpty()) {
                    Notification::make()
                        ->title('New Lesson Alert! 🎬')
                        ->body("Lesson {$unit->sort_order}: {$unit->title} is now available in your dashboard.")
                        ->success()
                        ->sendToDatabase($users);
                }
            }
        });

        static::deleted(function ($unit) {
            TrackUnit::where('unit_id', $unit->id)->delete();
            if ($unit->course_session_id) {
                static::resequenceSession((int) $unit->course_session_id);
            }
        });
    }

    /**
     * Extract numeric order if title is purely a number (e.g. "56") or "Lesson 56" / "Unit 56".
     */
    public static function extractOrderFromTitle(?string $title): ?int
    {
        $trimmed = trim((string) $title);
        if ($trimmed === '') {
            return null;
        }
        if (ctype_digit($trimmed)) {
            return (int) $trimmed;
        }
        if (preg_match('/^(?:lesson|unit)\s*(\d+)$/i', $trimmed, $m)) {
            return (int) $m[1];
        }
        return null;
    }

    /**
     * Format a unit title for a new order while preserving "Lesson X" or pure numeric style.
     */
    public static function formatTitleForOrder(?string $currentTitle, int $newOrder): string
    {
        $trimmed = trim((string) $currentTitle);
        if ($trimmed === '' || ctype_digit($trimmed)) {
            return (string) $newOrder;
        }
        if (preg_match('/^(lesson|unit)(\s*)\d+$/i', $trimmed, $m)) {
            $space = $m[2] !== '' ? $m[2] : ' ';
            return $m[1] . $space . $newOrder;
        }
        return (string) $currentTitle;
    }

    /**
     * Re-sequence all units in a CourseSession so sort_order (and numeric / "Lesson N" titles)
     * shift automatically without gaps or collisions when inserting, moving, or deleting,
     * and keep any LearningTrack containing this session's units in sync.
     */
    public static function resequenceSession(int $sessionId, ?int $anchorUnitId = null, ?int $anchorTargetOrder = null): void
    {
        if (!$sessionId) {
            return;
        }

        $units = static::where('course_session_id', $sessionId)
            ->when($anchorUnitId, fn ($q) => $q->where('id', '!=', $anchorUnitId))
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get(['id', 'sort_order', 'title']);

        if ($anchorUnitId && ($anchorUnit = static::find($anchorUnitId, ['id', 'sort_order', 'title']))) {
            $targetOrder = max(1, (int) ($anchorTargetOrder ?? $anchorUnit->sort_order ?? ($units->count() + 1)));
            $insertIndex = min($units->count(), $targetOrder - 1);
            $units->splice($insertIndex, 0, [$anchorUnit]);
        }

        $expectedOrder = 1;
        $now = now();

        foreach ($units as $u) {
            $newTitle = static::formatTitleForOrder($u->title, $expectedOrder);

            if ((int) $u->sort_order !== $expectedOrder || $u->title !== $newTitle) {
                DB::table('units')
                    ->where('id', $u->id)
                    ->update([
                        'sort_order' => $expectedOrder,
                        'title' => $newTitle,
                        'updated_at' => $now,
                    ]);
            }

            $expectedOrder++;
        }

        // Sync LearningTrack steps for any track that includes units from this session
        $publishedUnitIdsInOrder = static::where('course_session_id', $sessionId)
            ->where('is_published', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->pluck('id')
            ->values();

        if ($publishedUnitIdsInOrder->isNotEmpty()) {
            $tracksWithSession = TrackUnit::whereHas('unit', fn ($q) => $q->where('course_session_id', $sessionId))
                ->pluck('learning_track_id')
                ->unique();

            foreach ($tracksWithSession as $trackId) {
                $trackSteps = TrackUnit::with('unit')
                    ->where('learning_track_id', $trackId)
                    ->orderBy('step_number', 'asc')
                    ->orderBy('id', 'asc')
                    ->get();

                $firstIndex = $trackSteps->search(fn ($tu) => $tu->unit && (int) $tu->unit->course_session_id === $sessionId);
                if ($firstIndex === false) {
                    continue;
                }

                $existingByUnit = $trackSteps
                    ->filter(fn ($tu) => $tu->unit && (int) $tu->unit->course_session_id === $sessionId)
                    ->keyBy('unit_id');

                $beforeSteps = $trackSteps->slice(0, $firstIndex)->filter(fn ($tu) => !$tu->unit || (int) $tu->unit->course_session_id !== $sessionId);
                $afterSteps = $trackSteps->slice($firstIndex)->filter(fn ($tu) => !$tu->unit || (int) $tu->unit->course_session_id !== $sessionId);

                $sessionSteps = collect();
                foreach ($publishedUnitIdsInOrder as $uid) {
                    if ($existingByUnit->has($uid)) {
                        $sessionSteps->push($existingByUnit->get($uid));
                    } else {
                        $newTu = TrackUnit::create([
                            'learning_track_id' => $trackId,
                            'unit_id' => $uid,
                            'step_number' => 999999,
                        ]);
                        $sessionSteps->push($newTu);
                    }
                }

                $merged = $beforeSteps->concat($sessionSteps)->concat($afterSteps)->values();
                $stepCounter = 1;
                foreach ($merged as $step) {
                    if ((int) $step->step_number !== $stepCounter) {
                        DB::table('track_units')
                            ->where('id', $step->id)
                            ->update(['step_number' => $stepCounter, 'updated_at' => $now]);
                    }
                    $stepCounter++;
                }
            }
        }
    }

    // This allows us to mass-assign these fields
    protected $fillable = [
        'course_session_id', 
        'title', 
        'is_free_sample', 
        'sort_order', 
        'video_id', 
        'transcript', 
        'is_published',
        'is_registered_only',
        'type',
        'content_data',
        'content_blocks',
        'audio_url',
        'thumbnail'
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_free_sample' => 'boolean',
        'is_registered_only' => 'boolean',
        'content_data' => 'array',
        'content_blocks' => 'array',
    ];

    // Relationship: A Unit belongs to a Session
    public function courseSession(): BelongsTo
    {
        return $this->belongsTo(CourseSession::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function trackUnits(): HasMany
    {
        return $this->hasMany(TrackUnit::class);
    }

    public function learningTracks(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(LearningTrack::class, 'track_units')
            ->withPivot(['id', 'step_number', 'title_override', 'notes'])
            ->withTimestamps();
    }

    public function isAccessibleBy(?User $user): bool
    {
        // 0. If unit is not published, nobody can see it
        if (!$this->is_published) return false;

        // 1. Free samples are always open, even for guests
        if ($this->is_free_sample) return true;

        // If no user is logged in, they can ONLY see free samples (handled above)
        if (!$user) return false;

        // 2. Registered only content: accessible if logged in
        if ($this->is_registered_only) return true;

        // 3. Premium content: Check for active subscription
        if ($user->hasActiveSubscription()) return true;

        // If admin, always allow (fallback)
        if ($user->isAdmin()) return true;

        return false;
    }

    /**
     * Get video statistics from Bunny.net.
     */
    public function getBunnyStats(): array
    {
        if (!$this->video_id) {
            return [];
        }

        return \Illuminate\Support\Facades\Cache::remember("unit_{$this->id}_bunny_stats", 600, function () {
            // Instantiate service directly or via container
            return app(\App\Services\BunnyService::class)->getVideoStats($this->video_id) ?? [];
        });
    }

    /**
     * Get the next unit in a specific learning track.
     */
    public function nextTrackUnit(int $trackId): ?Unit
    {
        $currentTrackUnit = TrackUnit::where('learning_track_id', $trackId)
            ->where('unit_id', $this->id)
            ->first();

        if (!$currentTrackUnit) return null;

        $nextStep = $currentTrackUnit->nextStep();
        return $nextStep ? $nextStep->unit : null;
    }

    /**
     * Get the previous unit in a specific learning track.
     */
    public function previousTrackUnit(int $trackId): ?Unit
    {
        $currentTrackUnit = TrackUnit::where('learning_track_id', $trackId)
            ->where('unit_id', $this->id)
            ->first();

        if (!$currentTrackUnit) return null;

        $prevStep = $currentTrackUnit->previousStep();
        return $prevStep ? $prevStep->unit : null;
    }

    /**
     * Get the next unit in the sequence (even across sessions).
     */
    public function nextUnit(): ?Unit
    {
        // 1. Next unit in SAME session
        $next = Unit::where('course_session_id', $this->course_session_id)
            ->where('is_published', true)
            ->where('sort_order', '>', $this->sort_order)
            ->orderBy('sort_order', 'asc')
            ->first();

        if ($next) return $next;

        // 2. First unit of NEXT session
        $currentSession = $this->courseSession;
        if (!$currentSession) return null;

        $nextSession = CourseSession::where('module_id', $currentSession->module_id)
            ->where('sort_order', '>', $currentSession->sort_order)
            ->orderBy('sort_order', 'asc')
            ->first();

        if ($nextSession) {
            return $nextSession->units()
                ->where('is_published', true)
                ->orderBy('sort_order', 'asc')
                ->first();
        }

        return null;
    }

    public function previousUnit(): ?Unit
    {
        // 1. Previous unit in SAME session
        $prev = Unit::where('course_session_id', $this->course_session_id)
            ->where('is_published', true)
            ->where('sort_order', '<', $this->sort_order)
            ->orderBy('sort_order', 'desc')
            ->first();

        if ($prev) return $prev;

        // 2. Last unit of PREVIOUS session
        $currentSession = $this->courseSession;
        if (!$currentSession) return null;

        $prevSession = CourseSession::where('module_id', $currentSession->module_id)
            ->where('sort_order', '<', $currentSession->sort_order)
            ->orderBy('sort_order', 'desc')
            ->first();
        
        if ($prevSession) {
            return $prevSession->units()
                ->where('is_published', true)
                ->orderBy('sort_order', 'desc')
                ->first();
        }

        return null;
    }
}
