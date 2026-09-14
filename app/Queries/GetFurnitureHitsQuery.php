<?php

namespace App\Queries;

use App\Data\FurnitureCardData;
use App\Interfaces\CacheableQuery;
use Illuminate\Support\Collection;

/**
 * @return Collection<int, FurnitureCardData>
 */
final class GetFurnitureHitsQuery implements CacheableQuery
{
    public function __construct(
        public readonly int $limit = 14,
    ) {
        //
    }

    public function cacheKey(): string
    {
        return 'furniture.hits.'.$this->limit;
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
