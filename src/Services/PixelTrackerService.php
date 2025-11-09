<?php

namespace TautId\Tracker\Services;

use Carbon\Carbon;
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
    public function getUnsavedConversionByPixelEvent(string $event_id, Carbon $date): DataCollection
    {
        $ids = PixelTracker::raw(function ($collection) use ($event_id, $date) {
            return $collection->find([
                'is_saved' => false,
                'status' => ['$ne' => PixelConversionStatusEnums::Queued->value],
                'pixel.id' => $event_id,
            ], [
                'projection' => ['_id' => 1]
            ])->toArray();
        });

        $idList = collect($ids)->map(function($item) {
            return is_array($item['_id']) ? $item['_id']['$oid'] : (string) $item['_id'];
        })->toArray();

        $records = PixelTracker::whereIn('_id', $idList)
                                ->where('created_at', '<', $date)
                                ->orderBy('created_at')
                                ->get()
                                ->groupBy(function ($item) {
                                    return $item->created_at->format('Y-m-d');
                                })
                                ->map(function ($groupedRecords) {
                                    return new DataCollection(PixelTrackerData::class, $groupedRecords->map(fn($record) => PixelTrackerData::from($record)));
                                });

        return new DataCollection(DataCollection::class,$records);
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

    public function saveConversions(array $ids): void
    {
        PixelTracker::whereIn('_id',$ids)->update(['is_saved' => true]);
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
