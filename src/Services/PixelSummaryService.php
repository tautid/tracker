<?php

namespace TautId\Tracker\Services;

use Spatie\LaravelData\DataCollection;
use TautId\Tracker\Data\PixelSummary\PixelSummaryData;
use TautId\Tracker\Models\PixelSummary;

class PixelSummaryService
{
    public function getAllPixelSummaries(): DataCollection
    {
        $existing_data = PixelSummary::get()->map(fn ($record) => PixelSummaryData::from($record));

        return new DataCollection(
            PixelSummaryData::class,
            $existing_data
        );
    }
}
