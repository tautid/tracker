<?php

namespace TautId\Tracker\Factories;

use TautId\Tracker\Abstracts\PixelTrackerAbstract;
use TautId\Tracker\Interfaces\PixelTrackerInterface;
use TautId\Tracker\Factories\PixelTrackerDrivers\MetaDriver;

class PixelTrackerDriverFactory
{
    public static function getDriver(string $driverName): PixelTrackerAbstract
    {
        $driver = match (strtolower($driverName)) {
            'meta' => new MetaDriver,
            default => null
        };

        if (empty($driver)) {
            throw new \Exception('Driver not found');
        }

        if (! in_array(strtolower($driverName), config('taut-tracker.drivers'))) {
            throw new \Exception("{$driverName} is disabled from config");
        }

        return $driver;
    }

    public static function getOptions(): array
    {
        $options = collect(config('taut-tracker.drivers'))
            ->mapWithKeys(fn ($item) => [$item => ucfirst($item)])
            ->toArray();

        return $options;
    }
}
