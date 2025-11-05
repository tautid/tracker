<?php

namespace Tautid\Tracker\Services;

use Spatie\LaravelData\DataCollection;
use Tautid\Tracker\Models\PixelSummary;
use Tautid\Tracker\Data\PixelSummary\PixelSummaryData;

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
