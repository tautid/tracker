<?php

namespace TautId\Tracker\Data\PixelTracker;

use Carbon\Carbon;
use Spatie\LaravelData\Data;
use TautId\Tracker\Data\PixelEvent\PixelEventData;
use TautId\Tracker\Models\PixelTracker;

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
        $pixelData = is_array($record->pixel) ? $record->pixel : $record->pixel->toArray();

        // Convert MongoDB ObjectIds to strings
        $pixelData = self::convertObjectIdsToStrings($pixelData);

        return new self(
            id: $record->id,
            pixel: PixelEventData::from($pixelData),
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

    private static function convertObjectIdsToStrings(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // Handle MongoDB ObjectId format
                if (isset($value['$oid'])) {
                    $data[$key] = (string) $value['$oid'];
                }
                // Handle empty arrays for date fields
                elseif (in_array($key, ['created_at', 'updated_at']) && empty($value)) {
                    $data[$key] = now(); // Use current timestamp as fallback
                }
                else {
                    // Recursively process nested arrays
                    $data[$key] = self::convertObjectIdsToStrings($value);
                }
            }
        }

        return $data;
    }
}
