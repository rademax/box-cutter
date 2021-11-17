<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sheet extends Model
{
    protected $fillable = [
        'width',
        'length',
    ];

    protected $casts = [
        'width' => 'integer',
        'length' => 'integer',
    ];
}
