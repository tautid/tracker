<?php

namespace Tautid\Tracker\Data\PixelTracker;

use Illuminate\Http\Request;
use Spatie\LaravelData\Data;

class CreatePixelTrackerData extends Data
{
    public function __construct(
        public string $pixel_id,
        public Request $request,
        public ?array $data
    ) {}
}
