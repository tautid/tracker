<?php

namespace TautId\Tracker\Models;

use MongoDB\Laravel\Eloquent\Model;

class PixelTracker extends Model
{
    protected $connection = 'taut-mongotrack';

    protected $collection = 'pixel_trackers';

    protected $guarded = [];

    protected $casts = [
        'data' => 'array',
        'meta' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
