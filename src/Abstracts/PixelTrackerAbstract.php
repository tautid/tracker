<?php

namespace TautId\Tracker\Abstracts;

use TautId\Tracker\Data\PixelEvent\PixelEventData;
use TautId\Tracker\Data\PixelTracker\PixelTrackerData;

abstract class PixelTrackerAbstract
{
    protected ?PixelEventData $pixel = null;

    protected ?PixelTrackerData $conversion = null;

    protected ?array $data = null;

    public function setPixel(PixelEventData $pixel)
    {
        $this->pixel = $pixel;

        return $this;
    }

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function setConversion(PixelTrackerData $conversion)
    {
        $this->pixel = $conversion->pixel;
        $this->conversion = $conversion;
        $this->data = $conversion->data ?? [];

        return $this;
    }

    protected function validateRequiredData(): void
    {
        if ($this->pixel === null) {
            throw new \InvalidArgumentException('Pixel data must be set before fetching. Call setPixel() first.');
        }

        if ($this->data === null) {
            throw new \InvalidArgumentException('Data must be set before fetching. Call setData() first.');
        }
    }

    abstract public function getEvents(): array;

    abstract public function validateData(): void;

    abstract public function fetch(): void;

    abstract public function createSummary(): void;
}
