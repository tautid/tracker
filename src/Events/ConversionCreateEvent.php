<?php

namespace TautId\Tracker\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use TautId\Tracker\Data\PixelTracker\PixelTrackerData;

class ConversionCreateEvent
{
    use Dispatchable, InteractsWithSockets, Queueable, SerializesModels;

    public function __construct(
        public PixelTrackerData $data
    ) {}
}
