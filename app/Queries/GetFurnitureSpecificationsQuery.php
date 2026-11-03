<?php

namespace App\Queries;

use App\Data\FurnitureSpecificationData;
use App\Interfaces\CacheableQuery;

/**
 * @return FurnitureSpecificationData|null
 */
final class GetFurnitureSpecificationsQuery implements CacheableQuery
{
    public function __construct(
        public readonly int $furnitureId,
    ) {
        //
    }

    public function cacheKey(): string
    {
        return 'furniture.specifications.'.$this->furnitureId;
    }

    public function cacheTtlSeconds(): int
    {
        // Specifications are almost immutable; still tag-invalidated when the
        // catalogue changes.
        return 3600;
    }

    public function cacheTags(): array
    {
        return ['furniture'];
    }
}
