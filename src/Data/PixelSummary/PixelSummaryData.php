<?php

namespace Tautid\Tracker\Data\PixelSummary;

use Carbon\Carbon;
use Spatie\LaravelData\Data;
use Tautid\Tracker\Models\PixelSummary;

class PixelSummaryData extends Data
{
    public function __construct(
        public string $id,
        public PixelInformationData $pixel,
        public int $fetch_success,
        public int $fetch_failed,
        public int $total,
        public Carbon $date,
        public Carbon $created_at,
        public Carbon $updated_at
    )
    {

    }

    public static function fromModel(PixelSummary $record): self
    {
        return new self(
            id: $record->id,
            pixel: $record->pixel,
            fetch_success: $record->fetch_success,
            fetch_failed: $record->fetch_failed,
            total: $record->total,
            date: $record->date,
            created_at: $record->created_at,
            updated_at: $record->updated_at
        );
    }
}
