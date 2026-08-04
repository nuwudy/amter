<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeachingModule extends Model
{
    protected $fillable = ['name', 'description', 'slides'];

    protected $casts = [
        'slides' => 'array',
    ];
}
