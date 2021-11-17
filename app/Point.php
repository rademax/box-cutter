<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Point extends Model
{
    protected $fillable = [
        'x',
        'y',
    ];

    protected $casts = [
        'x' => 'integer',
        'y' => 'integer',
    ];
}
