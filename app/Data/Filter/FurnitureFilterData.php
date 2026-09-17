<?php

namespace App\Data\Filter;

use Spatie\LaravelData\Data;

/**
 * The user's active filter selection for the category catalog.
 *
 * Distinct from FilterOptionsData (the available choices): this DTO holds the
 * values actually applied to the query.
 */
class FurnitureFilterData extends Data
{
    /**
     * @param  list<string>  $foldingType
     * @param  list<string>  $surface
     * @param  list<string>  $materials
     * @param  list<int>  $manufacture
     */
    public function __construct(
        public readonly ?float $price_min = null,
        public readonly ?float $price_max = null,
        public readonly ?float $width_min = null,
        public readonly ?float $width_max = null,
        public readonly ?float $height_min = null,
        public readonly ?float $height_max = null,
        public readonly ?float $length_min = null,
        public readonly ?float $length_max = null,
        public readonly ?array $foldingType = null,
        public readonly ?array $surface = null,
        public readonly ?array $materials = null,
        public readonly ?array $manufacture = null,
    ) {}

    public function hasActiveFilters(): bool
    {
        return $this->price_min !== null
            || $this->price_max !== null
            || $this->width_min !== null
            || $this->width_max !== null
            || $this->height_min !== null
            || $this->height_max !== null
            || $this->length_min !== null
            || $this->length_max !== null
            || $this->foldingType !== null
            || $this->surface !== null
            || $this->materials !== null
            || $this->manufacture !== null;
    }

    /**
     * A stable string identifying this exact selection, used in cache keys.
     */
    public function cacheSuffix(): string
    {
        return hash('xxh128', serialize(array_filter($this->toArray(), fn (mixed $value) => $value !== null)));
    }
}
