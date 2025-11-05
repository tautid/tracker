<?php

namespace TautId\Tracker\Listeners;

use TautId\Tracker\Events\ConversionCreateEvent;
use TautId\Tracker\Factories\PixelTrackerDriverFactory;

class ConversionCreateListener
{
    public function handle(ConversionCreateEvent $event): void
    {
        PixelTrackerDriverFactory::getDriver($event->data->pixel->driver)
            ->setConversion($event->data)
            ->fetch();
    }
}
