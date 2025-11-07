<?php

namespace TautId\Tracker\Data\PixelSummary;

use Carbon\Carbon;
use Spatie\LaravelData\Data;

class CreatePixelSummaryData extends Data
{

    public function __construct(
        public PixelInformationData $pixel,
        public int $fetch_success,
        public int $fetch_failed,
        public int $fetch_duplicated,
        public int $total,
        public ?array $meta,
        public Carbon $date,
    )
    {

    }
}
