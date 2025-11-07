<?php

namespace TautId\Tracker\Services;

use Illuminate\Database\RecordNotFoundException;
use Spatie\LaravelData\DataCollection;
use TautId\Tracker\Data\PixelTracker\CreatePixelTrackerData;
use TautId\Tracker\Data\PixelTracker\PixelTrackerData;
use TautId\Tracker\Enums\PixelConversionStatusEnums;
use TautId\Tracker\Events\ConversionCreateEvent;
use TautId\Tracker\Factories\PixelTrackerDriverFactory;
use TautId\Tracker\Models\PixelTracker;

class PixelTrackerService
{
    public function getUnsavedConversionByPixelEvent(string $event_id): DataCollection
    {
        $records = PixelTracker::where('is_saved', false)
            ->whereNot('status', PixelConversionStatusEnums::Queued->value)
            ->where('pixel.id', $event_id)
            ->orderBy('created_at')
            ->get()
            ->groupBy(function ($item) {
                return $item->created_at->format('Y-m-d');
            })
            ->flatten()
            ->map(fn ($conversions) => new DataCollection(
                PixelTrackerData::class,
                collect([$conversions])->map(fn ($record) => PixelTrackerData::from($record))
            ));

        return new DataCollection(PixelTrackerData::class, $records);
    }

    public function createConversion(CreatePixelTrackerData $data): PixelTrackerData
    {
        $pixel = app(PixelEventService::class)->getPixelEventById($data->pixel_id);

        PixelTrackerDriverFactory::getDriver($pixel->driver)
            ->setPixel($pixel)
            ->setData($data?->data ?? [])
            ->validateData();

        $meta = match ($pixel->driver) {
            'meta' => $this->handleMetaDriverMeta($data),
            default => []
        };

        $record = PixelTracker::create([
            'pixel' => $pixel,
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

    public function changeStatusToSuccess(string $id): void
    {
        $record = PixelTracker::find($id);

        if (empty($record)) {
            throw new RecordNotFoundException('Tracker / Conversion is not found');
        }

        if ($record->status != PixelConversionStatusEnums::Queued->value) {
            throw new \InvalidArgumentException('Current status of tracker is not queued');
        }

        $record->update([
            'status' => PixelConversionStatusEnums::Success->value,
        ]);
    }

    public function changeStatusToFailed(string $id): void
    {
        $record = PixelTracker::find($id);

        if (empty($record)) {
            throw new RecordNotFoundException('Tracker / Conversion is not found');
        }

        if ($record->status != PixelConversionStatusEnums::Queued->value) {
            throw new \InvalidArgumentException('Current status of tracker is not queued');
        }

        $record->update([
            'status' => PixelConversionStatusEnums::Failed->value,
        ]);
    }

    public function changeStatusToDuplicate(string $id): void
    {
        $record = PixelTracker::find($id);

        if (empty($record)) {
            throw new RecordNotFoundException('Tracker / Conversion is not found');
        }

        if ($record->status != PixelConversionStatusEnums::Queued->value) {
            throw new \InvalidArgumentException('Current status of tracker is not queued');
        }

        $record->update([
            'status' => PixelConversionStatusEnums::Duplicate->value,
        ]);
    }
}
