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
        return 1800;
    }

    public function cacheTags(): array
    {
        return ['furniture'];
    }
}
