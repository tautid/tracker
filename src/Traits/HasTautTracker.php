<?php

namespace TautId\Tracker\Traits;

use Illuminate\Support\Collection;
use TautId\Tracker\Models\PixelEvent;

trait HasTautTracker
{
    public function pixelEvents(): Collection
    {
        return PixelEvent::where('reference_type', static::class)
            ->where('reference_id', $this->getKey())
            ->get();
    }
}
