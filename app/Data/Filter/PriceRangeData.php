<?php

namespace App\Data\Filter;

use Spatie\LaravelData\Data;

class PriceRangeData extends Data
{
    public function __construct(
        public readonly float $min_cm,
        public readonly float $max_cm,
    ) {}

}
