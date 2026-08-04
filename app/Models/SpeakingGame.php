<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpeakingGame extends Model
{
    protected $fillable = [
        'name',
        'description',
        'questions',
    ];

    protected $casts = [
        'questions' => 'array',
    ];
}
