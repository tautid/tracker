<?php

namespace Tautid\Tracker\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Tautid\Tracker\Tracker
 */
class Tracker extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Tautid\Tracker\Tracker::class;
    }
}
