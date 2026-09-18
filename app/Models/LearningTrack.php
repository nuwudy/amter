<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class LearningTrack extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'thumbnail_path',
        'is_active',
        'is_default',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($track) {
            if (empty($track->slug)) {
                $track->slug = Str::slug($track->title);
            }
        });

        static::saving(function ($track) {
            if ($track->is_default) {
                // Ensure only one track is default at any time
                static::where('id', '!=', $track->id)->update(['is_default' => false]);
            }
        });
    }

    public static function getPrimaryTrack(): ?self
    {
        return static::where('is_active', true)
            ->where('is_default', true)
            ->first();
    }

    public function getFirstStepUrl(): ?string
    {
        $firstStep = $this->trackUnits()->orderBy('step_number', 'asc')->first();
        if (!$firstStep) {
            return null;
        }

        return route('student.units.show', [
            'unit' => $firstStep->unit_id,
            'track_id' => $this->id,
        ]);
    }

    public function trackUnits(): HasMany
    {
        return $this->hasMany(TrackUnit::class)->orderBy('step_number', 'asc');
    }

    public function units(): BelongsToMany
    {
        return $this->belongsToMany(Unit::class, 'track_units')
            ->withPivot(['id', 'step_number', 'title_override', 'notes'])
            ->withTimestamps()
            ->orderByPivot('step_number', 'asc');
    }

    /**
     * Get the next step number to append to this track.
     */
    public function getNextStepNumber(): int
    {
        $max = $this->trackUnits()->max('step_number');
        return ($max !== null) ? $max + 1 : 1;
    }

    /**
     * Cleanly re-index all steps sequentially (1, 2, 3...) without gaps.
     */
    public function renumberSteps(): void
    {
        $steps = $this->trackUnits()->orderBy('step_number', 'asc')->orderBy('id', 'asc')->get();
        $counter = 1;
        foreach ($steps as $step) {
            if ($step->step_number !== $counter) {
                $step->update(['step_number' => $counter]);
            }
            $counter++;
        }
    }

    /**
     * Bulk append units by unit IDs.
     */
    public function appendUnits(array $unitIds, ?string $notes = null): int
    {
        $currentStep = $this->getNextStepNumber();
        $added = 0;

        foreach ($unitIds as $unitId) {
            TrackUnit::create([
                'learning_track_id' => $this->id,
                'unit_id' => $unitId,
                'step_number' => $currentStep++,
                'notes' => $notes,
            ]);
            $added++;
        }

        return $added;
    }

    /**
     * Bulk append a range of units from a CourseSession (Title).
     */
    public function appendRangeFromSession(int $sessionId, int $fromSortOrder, int $toSortOrder, ?string $notes = null): int
    {
        $units = Unit::where('course_session_id', $sessionId)
            ->where('is_published', true)
            ->whereBetween('sort_order', [min($fromSortOrder, $toSortOrder), max($fromSortOrder, $toSortOrder)])
            ->orderBy('sort_order', 'asc')
            ->get();

        $currentStep = $this->getNextStepNumber();
        $added = 0;

        foreach ($units as $unit) {
            TrackUnit::create([
                'learning_track_id' => $this->id,
                'unit_id' => $unit->id,
                'step_number' => $currentStep++,
                'notes' => $notes,
            ]);
            $added++;
        }

        return $added;
    }

    /**
     * Search steps in this track by step number, title override, unit title, session title, or module name.
     */
    public function searchSteps(string $keyword)
    {
        $keyword = trim($keyword);
        if (empty($keyword)) {
            return collect();
        }

        $stepNumber = null;
        if (preg_match('/^(?:step\s*)?(\d+)$/i', $keyword, $m)) {
            $stepNumber = (int) $m[1];
        }

        return $this->trackUnits()
            ->with(['unit', 'unit.courseSession', 'unit.courseSession.module'])
            ->where(function ($query) use ($keyword, $stepNumber) {
                if ($stepNumber !== null) {
                    $query->orWhere('step_number', $stepNumber);
                }
                $query->orWhere('title_override', 'like', "%{$keyword}%")
                    ->orWhereHas('unit', function ($q) use ($keyword) {
                        $q->where('title', 'like', "%{$keyword}%")
                          ->orWhereHas('courseSession', function ($sq) use ($keyword) {
                              $sq->where('title', 'like', "%{$keyword}%")
                                ->orWhereHas('module', function ($mq) use ($keyword) {
                                    $mq->where('name', 'like', "%{$keyword}%");
                                });
                          });
                    });
            })
            ->orderBy('step_number', 'asc')
            ->limit(40)
            ->get();
    }

    /**
     * Return a lightweight array of all steps in this track for client-side search & navigation.
     */
    public function getTrackIndexData(?User $user = null): array
    {
        $completedUnitIds = $user ? $user->completedUnits()->pluck('units.id')->flip()->toArray() : [];

        $steps = $this->trackUnits()
            ->with(['unit', 'unit.courseSession'])
            ->orderBy('step_number', 'asc')
            ->get();

        return $steps->map(function ($step) use ($completedUnitIds, $user) {
            $unit = $step->unit;
            $session = $unit?->courseSession;
            $unitTitle = $step->title_override ?: ($unit?->title ?? 'Untitled Lesson');
            $sessionTitle = $session?->title ?? $session?->name ?? 'Course Track';
            $isCompleted = isset($completedUnitIds[$step->unit_id]);
            $isAccessible = $unit ? $unit->isAccessibleBy($user) : true;

            return [
                'step' => $step->step_number,
                'unit_id' => $step->unit_id,
                'title' => $unitTitle,
                'session' => $sessionTitle,
                'url' => route('student.units.show', [
                    'unit' => $step->unit_id,
                    'track_id' => $this->id,
                ]),
                'completed' => $isCompleted,
                'accessible' => $isAccessible,
            ];
        })->toArray();
    }
}

