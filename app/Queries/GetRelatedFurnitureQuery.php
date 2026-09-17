<?php

namespace App\Queries;

use App\Data\FurnitureCardData;
use App\Interfaces\CacheableQuery;
use Illuminate\Support\Collection;

/**
 * @return Collection<int, FurnitureCardData>
 */
final class GetRelatedFurnitureQuery implements CacheableQuery
{
    public function __construct(
        public readonly int $categoryId,
        public readonly int $excludeId,
        public readonly int $limit = 4,
    ) {
        //
    }

    public function cacheKey(): string
    {
        return 'furniture.related.category'.$this->categoryId.'.exclude'.$this->excludeId.'.limit'.$this->limit;
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
