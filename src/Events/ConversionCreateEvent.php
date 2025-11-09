<?php

namespace TautId\Tracker\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use TautId\Tracker\Data\PixelTracker\PixelTrackerData;

class ConversionCreateEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public PixelTrackerData $data
    ) {}
}
