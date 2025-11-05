<?php

namespace TautId\Tracker\Data\PixelEvent;

use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelData\Data;

class CreatePixelEventData extends Data
{
    public function __construct(
        public ?Model $reference,
        public string $name,
        public string $driver,
        public string $event,
        public string $pixel_id,
        public string $token
    ) {}
}
