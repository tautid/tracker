<?php

namespace TautId\Tracker\Services;

use TautId\Tracker\Models\PixelTracker;
use TautId\Tracker\Events\ConversionCreateEvent;
use TautId\Tracker\Enums\PixelConversionStatusEnums;
use TautId\Tracker\Data\PixelTracker\PixelTrackerData;
use TautId\Tracker\Factories\PixelTrackerDriverFactory;
use TautId\Tracker\Data\PixelTracker\CreatePixelTrackerData;

class PixelTrackerService
{
    public function createConversion(CreatePixelTrackerData $data): PixelTrackerData
    {
        $pixel = app(PixelEventService::class)->getPixelEventById($data->pixel_id);

        PixelTrackerDriverFactory::getDriver($pixel->driver)
                                    ->setPixel($pixel)
                                    ->setData($data->data)
                                    ->validateData();

        $meta = match($pixel->driver)
        {
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
            'source_url' => $data->request->url()
        ]);

        $result = PixelTrackerData::from($record);

        event((new ConversionCreateEvent($result)));

        return $result;
    }

    private function handleMetaDriverMeta(CreatePixelTrackerData $data): array
    {
        return [
            'fbp' => $data->request->cookie('_fbp'),
            'fbc' => $data->request->cookie('_fbc')
        ];
    }
}
