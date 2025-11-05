<?php

namespace TautId\Tracker\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \TautId\Tracker\Tracker
 */
class Tracker extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \TautId\Tracker\Tracker::class;
    }
}
