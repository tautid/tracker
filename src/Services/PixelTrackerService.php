<?php

namespace Tautid\Tracker\Services;

use Tautid\Tracker\Data\PixelTracker\CreatePixelTrackerData;
use Tautid\Tracker\Data\PixelTracker\PixelTrackerData;
use Tautid\Tracker\Enums\PixelConversionStatusEnums;
use Tautid\Tracker\Events\ConversionCreateEvent;
use Tautid\Tracker\Factories\PixelTrackerDriverFactory;
use Tautid\Tracker\Models\PixelTracker;

class PixelTrackerService
{
    public function createConversion(CreatePixelTrackerData $data): PixelTrackerData
    {
        $pixel = app(PixelEventService::class)->getPixelEventById($data->pixel_id);

        PixelTrackerDriverFactory::getDriver($pixel->driver)
            ->setPixel($pixel)
            ->setData($data->data)
            ->validateData();

        $meta = match ($pixel->driver) {
            'meta' => $this->handleMetaDriverMeta($data),
            default => []
        };

        $record = PixelTracker::create([
            'id' => uniqid(),
            'event_id' => $pixel->id,
            'status' => PixelConversionStatusEnums::Queued->value,
            'is_saved' => false,
            'data' => $data->data,
            'meta' => $meta,
            'user_agent' => $data->request->userAgent(),
            'client_ip' => $data->request->ip(),
            'source_url' => $data->request->url(),
        ]);

        $result = PixelTrackerData::from($record);

        event((new ConversionCreateEvent($result)));

        return $result;
    }

    private function handleMetaDriverMeta(CreatePixelTrackerData $data): array
    {
        return [
            'fbp' => $data->request->cookie('_fbp'),
            'fbc' => $data->request->cookie('_fbc'),
        ];
    }
}
