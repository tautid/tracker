<?php

namespace TautId\Tracker\Services;

use Carbon\Carbon;
use Spatie\LaravelData\DataCollection;
use TautId\Tracker\Models\PixelSummary;
use TautId\Tracker\Models\PixelTracker;
use TautId\Tracker\Enums\PixelConversionStatusEnums;
use TautId\Tracker\Data\PixelSummary\PixelSummaryData;
use TautId\Tracker\Factories\PixelTrackerDriverFactory;
use TautId\Tracker\Data\PixelSummary\CreatePixelSummaryData;

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

    public function createPixelSummary(CreatePixelSummaryData $data): void
    {
        PixelSummary::create([
                    'pixel' => $data->pixel,
                    'fetch_success' => $data->fetch_success,
                    'fetch_failed' => $data->fetch_failed,
                    'fetch_duplicated' => $data->fetch_duplicated,
                    'total' => $data->fetch_success + $data->fetch_failed + $data->fetch_duplicated,
                    'date' => $data->date,
                    'meta' => $data->meta
                ]);
    }

    public function createPixelSummaryFromUnsavedConversion(?Carbon $date = null): void
    {
        $pixel_events = app(PixelEventService::class)->getAllPixelEvents();

        foreach($pixel_events as $pixel) {
            PixelTrackerDriverFactory::getDriver($pixel->driver)
                                        ->setPixel($pixel)
                                        ->createSummary($date);
        }
    }
}
