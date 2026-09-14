<?php

namespace App\Queries;

use App\Data\FurnitureDetailData;
use App\Interfaces\CacheableQuery;

/**
 * @return FurnitureDetailData
 */
final class GetFurnitureQuery implements CacheableQuery
{
    public function __construct(
        public readonly int $furnitureId,
    ) {
        //
    }

    public function cacheKey(): string
    {
        return 'furniture.detail.'.$this->furnitureId;
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
