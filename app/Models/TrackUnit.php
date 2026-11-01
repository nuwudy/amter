<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class TrackUnit extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::creating(function ($trackUnit) {
            if (empty($trackUnit->step_number) || (int) $trackUnit->step_number <= 0) {
                $max = static::where('learning_track_id', $trackUnit->learning_track_id)->max('step_number');
                $trackUnit->step_number = ((int) ($max ?? 0)) + 1;
            }
        });

        static::created(function ($trackUnit) {
            if ($trackUnit->learning_track_id) {
                static::resequenceTrack((int) $trackUnit->learning_track_id, (int) $trackUnit->id, (int) $trackUnit->step_number);
            }
        });

        static::updated(function ($trackUnit) {
            if ($trackUnit->wasChanged('step_number') && $trackUnit->learning_track_id) {
                static::resequenceTrack((int) $trackUnit->learning_track_id, (int) $trackUnit->id, (int) $trackUnit->step_number);
            }
        });

        static::deleted(function ($trackUnit) {
            if ($trackUnit->learning_track_id) {
                static::resequenceTrack((int) $trackUnit->learning_track_id);
            }
        });
    }

    /**
     * Re-sequence all steps in a LearningTrack so that manual Admin step_number edits
     * take priority and remaining steps shift cleanly (1, 2, 3...) without duplicates or gaps.
     */
    public static function resequenceTrack(int $trackId, ?int $anchorTrackUnitId = null, ?int $anchorTargetStep = null): void
    {
        if (!$trackId) {
            return;
        }

        $steps = static::where('learning_track_id', $trackId)
            ->when($anchorTrackUnitId, fn ($q) => $q->where('id', '!=', $anchorTrackUnitId))
            ->orderBy('step_number', 'asc')
            ->orderBy('id', 'asc')
            ->get(['id', 'step_number']);

        if ($anchorTrackUnitId && ($anchor = static::find($anchorTrackUnitId, ['id', 'step_number']))) {
            $targetStep = max(1, (int) ($anchorTargetStep ?? $anchor->step_number ?? ($steps->count() + 1)));
            $insertIndex = min($steps->count(), $targetStep - 1);
            $steps->splice($insertIndex, 0, [$anchor]);
        }

        $expectedStep = 1;
        $now = now();

        foreach ($steps as $step) {
            if ((int) $step->step_number !== $expectedStep) {
                DB::table('track_units')
                    ->where('id', $step->id)
                    ->update([
                        'step_number' => $expectedStep,
                        'updated_at' => $now,
                    ]);
            }
            $expectedStep++;
        }
    }

    protected $fillable = [
        'learning_track_id',
        'unit_id',
        'step_number',
        'title_override',
        'notes',
    ];

    protected $casts = [
        'step_number' => 'integer',
    ];

    public function learningTrack(): BelongsTo
    {
        return $this->belongsTo(LearningTrack::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the next step in this track.
     */
    public function nextStep(): ?TrackUnit
    {
        return TrackUnit::where('learning_track_id', $this->learning_track_id)
            ->where('step_number', '>', $this->step_number)
            ->orderBy('step_number', 'asc')
            ->first();
    }

    /**
     * Get the previous step in this track.
     */
    public function previousStep(): ?TrackUnit
    {
        return TrackUnit::where('learning_track_id', $this->learning_track_id)
            ->where('step_number', '<', $this->step_number)
            ->orderBy('step_number', 'desc')
            ->first();
    }
}
