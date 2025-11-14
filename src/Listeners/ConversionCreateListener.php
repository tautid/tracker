<?php

namespace TautId\Tracker\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use TautId\Tracker\Events\ConversionCreateEvent;
use TautId\Tracker\Factories\PixelTrackerDriverFactory;

class ConversionCreateListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(ConversionCreateEvent $event): void
    {
        PixelTrackerDriverFactory::getDriver($event->data->pixel->driver)
            ->setConversion($event->data)
            ->fetch();
    }
}
