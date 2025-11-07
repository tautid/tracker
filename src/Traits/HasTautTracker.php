<?php

namespace TautId\Tracker\Traits;

use Illuminate\Support\Collection;
use TautId\Tracker\Models\PixelEvent;

trait HasTautTracker
{
    /**
     * Get pixel events for this model
     */
    public function getPixelEventsAttribute(): Collection
    {
        $className = static::class;
        $escapedClassName = addslashes($className);
        $referenceId = $this->getKey();

        return PixelEvent::where(function ($query) use ($className, $escapedClassName) {
            $query->where('reference_type', $className)
                ->orWhere('reference_type', $escapedClassName);
        })
            ->where(function ($query) use ($referenceId) {
                $query->where('reference_id', $referenceId)
                    ->orWhere('reference_id', (string) $referenceId);
            })
            ->get();
    }
}
