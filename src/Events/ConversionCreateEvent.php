<?php

namespace Tautid\Tracker\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Tautid\Tracker\Data\PixelTracker\PixelTrackerData;

class ConversionCreateEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels, Queueable;

    public function __construct(
        public PixelTrackerData $data
    )
    {

    }
}
