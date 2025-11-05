<?php

namespace TautId\Tracker\Services;

use Spatie\LaravelData\DataCollection;
use TautId\Tracker\Models\PixelSummary;
use TautId\Tracker\Data\PixelSummary\PixelSummaryData;

class PixelSummaryService
{
    public function getAllPixelSummaries(): DataCollection
    {
        $existing_data = PixelSummary::get()->map(fn($record) => PixelSummaryData::from($record));

        return new DataCollection(
            PixelSummaryData::class,
            $existing_data
        );
    }
}
