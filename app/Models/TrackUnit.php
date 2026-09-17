<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackUnit extends Model
{
    use HasFactory;

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
