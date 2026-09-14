<?php

namespace App\Queries;

use App\Data\FurnitureCategoryData;
use App\Interfaces\CacheableQuery;
use Illuminate\Support\Collection;

/**
 * @return Collection<int, FurnitureCategoryData>
 */
final class GetCategoriesQuery implements CacheableQuery
{
    public function cacheKey(): string
    {
        return 'furniture.categories';
    }

    public function cacheTtlSeconds(): int
    {
        return 3600;
    }

    public function cacheTags(): array
    {
        return ['furniture'];
    }
}
