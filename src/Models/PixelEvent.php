<?php

namespace Tautid\Tracker\Models;

use MongoDB\Laravel\Eloquent\Model;

class PixelEvent extends Model
{
    protected $connection = 'taut-mongotrack';

    protected $collection = 'pixel_events';

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
}
