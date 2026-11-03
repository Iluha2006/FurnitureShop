<?php

namespace App\Queries;

use App\Data\Filter\FilterOptionsData;
use App\Interfaces\CacheableQuery;

/**
 * @return FilterOptionsData
 */
final class GetFurnitureFilterOptionsQuery implements CacheableQuery
{
    public function __construct(
        public readonly int $categoryId,
    ) {
        //
    }

    public function cacheKey(): string
    {
        return 'furniture.filter-options.category.'.$this->categoryId;
    }

    public function cacheTtlSeconds(): int
    {
        // Seven aggregate queries per category — the most expensive read in
        // the catalogue, and the result changes only when stock changes.
        return 3600;
    }

    public function cacheTags(): array
    {
        return ['furniture'];
    }
}
