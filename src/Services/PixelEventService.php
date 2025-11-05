<?php

namespace TautId\Tracker\Services;

use TautId\Tracker\Models\PixelEvent;
use Spatie\LaravelData\DataCollection;
use TautId\Tracker\Traits\FilterServiceTrait;
use Spatie\LaravelData\PaginatedDataCollection;
use Illuminate\Database\RecordNotFoundException;
use TautId\Tracker\Data\PixelEvent\PixelEventData;
use TautId\Tracker\Data\Utility\FilterPaginationData;
use TautId\Tracker\Data\PixelEvent\CreatePixelEventData;

class PixelEventService
{
    use FilterServiceTrait;

    public function getAllPixelEvents(): DataCollection
    {
        return new DataCollection(
            PixelEventData::class,
            PixelEvent::get()->map(fn ($record) => PixelEventData::from($record))
        );
    }

    public function getPaginatedPixelEvents(FilterPaginationData $data): PaginatedDataCollection
    {
        $query = $this->filteredQuery(PixelEvent::class, $data);

        $pagination = $query->paginate($data->per_page, ['*'], 'page', $data->page);

        $transformedItems = $pagination->getCollection()->map(fn ($record) => PixelEventData::from($record));

        $pagination->setCollection($transformedItems);

        return new PaginatedDataCollection(PixelEventData::class, $pagination);
    }

    public function getPixelEventById(string $id): PixelEventData
    {
        $record = PixelEvent::find($id);

        if (empty($record)) {
            throw new RecordNotFoundException('Pixel event not found');
        }

        return PixelEventData::from($record);
    }

    public function createPixelEvent(CreatePixelEventData $data): PixelEventData
    {
        $record = PixelEvent::create([
            'id' => uniqid(),
            'reference_id' => $data->reference?->id ?? null,
            'reference_type' => ($data->reference) ? get_class($data->reference) : null,
            'name' => $data->name,
            'driver' => $data->driver,
            'event' => $data->event,
            'token' => $data->token,
        ]);

        return PixelEventData::from($record);
    }

    public function deletePixelEvent(string $id): void
    {
        $record = PixelEvent::find($id);

        if (empty($record)) {
            throw new RecordNotFoundException('Pixel event not found');
        }

        $record->delete();
    }
}
