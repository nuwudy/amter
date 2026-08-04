<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    protected $fillable = ['unit_id', 'type', 'content', 'sort_order'];

    public function unit(): BelongsTo {
        return $this->belongsTo(Unit::class);
    }
}