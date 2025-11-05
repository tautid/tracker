<?php

namespace Tautid\Tracker\Data\PixelEvent;

use Spatie\LaravelData\Data;
use Illuminate\Database\Eloquent\Model;

class UpdatePixelEventData extends Data
{
    public function __construct(
        public string $id,
        public ?Model $reference,
        public string $driver,
        public string $event,
        public string $pixel_id,
        public string $token
    )
    {

    }
}
