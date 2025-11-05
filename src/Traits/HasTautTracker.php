<?php

namespace TautId\Tracker\Traits;

use TautId\Tracker\Models\PixelEvent;
use Illuminate\Support\Collection;

trait HasTautTracker
{
    public function pixelEvents(): Collection
    {
        return PixelEvent::where('reference_type', static::class)
            ->where('reference_id', $this->getKey())
            ->get();
    }
}
