<?php

namespace TautId\Tracker\Data\PixelEvent;

use Carbon\Carbon;
use Spatie\LaravelData\Data;
use TautId\Tracker\Models\PixelEvent;

class PixelEventData extends Data
{
    public function __construct(
        public string $id,
        public ?string $reference_id,
        public ?string $reference_type,
        public string $name,
        public string $driver,
        public string $event,
        public string $pixel_id,
        public string $token,
        public Carbon $created_at,
        public Carbon $updated_at
    )
    {

    }

    public static function fromModel(PixelEvent $record): self
    {
        return new self(
            id: $record->id,
            reference_id: $record->reference_id,
            reference_type: $record->reference_type,
            name: $record->name,
            driver: $record->driver,
            event: $record->driver,
            pixel_id: $record->pixel_id,
            token: $record->token,
            created_at: $record->created_at,
            updated_at: $record->updated_at
        );
    }
}
