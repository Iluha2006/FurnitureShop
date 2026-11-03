<?php

namespace App\Queries;

use App\Data\Filter\FurnitureFilterData;
use App\Interfaces\CacheableQuery;

final class GetFurnitureByCategoryQuery implements CacheableQuery
{
    public function __construct(
        public readonly int $categoryId,
        public readonly int $perPage = 15,
        public readonly int $page = 1,

        public readonly ?FurnitureFilterData $filter = null,
    ) {
        //
    }

    public function cacheKey(): string
    {

        $filter = $this->filter?->cacheSuffix() ?? 'empty';

        return 'furniture.category.'.$this->categoryId.'.filter'.$filter.'.page'.$this->page.'.size'.$this->perPage;
    }

    public function cacheTtlSeconds(): int
    {
        // Filters add joins and sorting, so each page is expensive; the key
        // already encodes every filter value and the page number.
        return 900;
    }

    public function cacheTags(): array
    {
        return ['furniture'];
    }
}
