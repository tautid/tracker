<?php

namespace TautId\Tracker;

use Illuminate\Support\Facades\Event;
use Spatie\LaravelPackageTools\Package;
use TautId\Tracker\Commands\TrackerCommand;
use TautId\Tracker\Events\ConversionCreateEvent;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use TautId\Tracker\Listeners\ConversionCreateListener;

class TrackerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('tracker')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_tracker_table');
    }

    public function boot()
    {
        $existing = config('database.connections', []);
        $mongotrack = config('taut-tracker.connections', []);

        config([
            'database.connections' => array_merge($existing, $mongotrack),
        ]);

        Event::listen(ConversionCreateEvent::class,ConversionCreateListener::class);
    }
}
