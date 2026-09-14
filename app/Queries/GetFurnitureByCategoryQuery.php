<?php

namespace App\Queries;

use App\Data\FurnitureCardData;
use App\Interfaces\CacheableQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * @return LengthAwarePaginator<int, FurnitureCardData>
 */
final class GetFurnitureByCategoryQuery implements CacheableQuery
{
    public function __construct(
        public readonly int $categoryId,
        public readonly int $perPage = 15,
        public readonly int $page = 1,
    ) {
        //
    }

    public function cacheKey(): string
    {
        return 'furniture.category.'.$this->categoryId.'.page'.$this->page.'.size'.$this->perPage;
    }

    public function cacheTtlSeconds(): int
    {
        return 900;
    }

    public function cacheTags(): array
    {
        return ['furniture'];
    }
}
