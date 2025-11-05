<?php

namespace Tautid\Tracker\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Tautid\Tracker\Data\PixelTracker\PixelTrackerData;

class ConversionCreateEvent
{
    use Dispatchable, InteractsWithSockets, Queueable, SerializesModels;

    public function __construct(
        public PixelTrackerData $data
    ) {}
}
