<?php

namespace TautId\Tracker\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \TautId\Tracker\Tracker
 */
class TautTracker extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \TautId\Tracker\TautTracker::class;
    }
}
