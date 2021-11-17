<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Box extends Model
{
    protected $fillable = [
        'width',
        'deep',
        'height',
    ];

    protected $casts = [
        'width' => 'integer',
        'deep' => 'integer',
        'height' => 'integer',
    ];

}
