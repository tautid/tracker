<?php

namespace Tautid\Tracker\Data\PixelEvent;

use Spatie\LaravelData\Data;
use Illuminate\Database\Eloquent\Model;

class CreatePixelEventData extends Data
{
    public function __construct(
        public ?Model $reference,
        public string $name,
        public string $driver,
        public string $event,
        public string $pixel_id,
        public string $token
    )
    {

    }
}
