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
}
