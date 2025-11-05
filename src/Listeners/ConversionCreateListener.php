<?php

namespace Tautid\Tracker\Listeners;

use Tautid\Tracker\Events\ConversionCreateEvent;
use Tautid\Tracker\Factories\PixelTrackerDriverFactory;

class ConversionCreateListener
{
    public function handle(ConversionCreateEvent $event): void
    {
        PixelTrackerDriverFactory::getDriver($event->data->pixel->driver)
            ->setConversion($event->data)
            ->fetch();
    }
}
