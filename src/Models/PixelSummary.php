<?php

namespace TautId\Tracker\Models;

use MongoDB\Laravel\Eloquent\Model;

class PixelSummary extends Model
{
    protected $connection = 'taut-mongotrack';

    protected $collection = 'pixel_summaries';

    protected $guarded = [];

    protected $casts = [
        'pixel' => 'array',
        'meta' => 'array',
        'date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
