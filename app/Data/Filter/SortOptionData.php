<?php

namespace App\Data\Filter;

use Spatie\LaravelData\Data;

class SortOptionData extends Data
{
    public function __construct(
        public readonly string $value,
        public readonly string $label,
    ) {}
}
