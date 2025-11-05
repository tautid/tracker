<?php

namespace Tautid\Tracker\Factories;

use Tautid\Tracker\Abstracts\PixelTrackerAbstract;
use Tautid\Tracker\Factories\PixelTrackerDrivers\MetaDriver;

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
