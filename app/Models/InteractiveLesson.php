<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InteractiveLesson extends Model
{
    protected $fillable = ['name', 'description', 'items'];

    protected $casts = [
        'items' => 'array',
    ];
}
