<?php

namespace TautId\Tracker\Data\PixelTracker;

use Carbon\Carbon;
use Spatie\LaravelData\Data;
use TautId\Tracker\Models\PixelTracker;
use TautId\Tracker\Data\PixelEvent\PixelEventData;

class PixelTrackerData extends Data
{
    public function __construct(
        public string $id,
        public PixelEventData $pixel,
        public string $status,
        public bool $is_saved,
        public ?array $data,
        public string $user_agent,
        public string $client_ip,
        public string $source_url,
        public ?array $meta,
        public Carbon $created_at,
        public Carbon $updated_at
    ) {}

    public static function fromModel(PixelTracker $record): self
    {
        return new self(
            id: $record->id,
            pixel: PixelEventData::from($record->pixel->toArray()),
            status: $record->status,
            is_saved: $record->is_saved,
            data: $record->data,
            user_agent: $record->user_agent,
            client_ip: $record->client_ip,
            source_url: $record->source_url,
            meta: $record->meta,
            created_at: $record->created_at,
            updated_at: $record->updated_at
        );
    }
}
