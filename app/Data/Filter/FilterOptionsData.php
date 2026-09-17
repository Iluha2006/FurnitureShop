<?php

namespace App\Data\Filter;

use Spatie\LaravelData\Data;

class FilterOptionsData extends Data
{
    public function __construct(
        public readonly PriceRangeData $price_range,
        public readonly WidthRangeData $widthRangeData,
        public readonly HeightRangeData $heightRangeData,
        public readonly LengthRangeData $lengthRangeData,
        public readonly array $folding_type,
        public readonly array $surface,
        public readonly array $materials,
        public readonly array $manufacture,
    ) {}

}
