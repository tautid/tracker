<?php

namespace Tautid\Tracker\Data\PixelSummary;

use Spatie\LaravelData\Data;

class PixelInformationData extends Data
{
    public function __construct(
        public string $id,
        public string $name
    )
    {

    }
}
