<?php

namespace TautId\Tracker\Services;

use Spatie\LaravelData\DataCollection;
<<<<<<< HEAD
use TautId\Tracker\Models\PixelSummary;
use TautId\Tracker\Data\PixelSummary\PixelSummaryData;
=======
use Tautid\Tracker\Data\PixelSummary\PixelSummaryData;
use Tautid\Tracker\Models\PixelSummary;
>>>>>>> 9a8c86233e257ccf199186b3aa6d7bfe1c431e11

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
