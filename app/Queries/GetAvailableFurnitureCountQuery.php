<?php

namespace App\Queries;

use App\Interfaces\CacheableQuery;

final class GetAvailableFurnitureCountQuery implements CacheableQuery
{
    public function cacheKey(): string
    {
        return 'furniture.count.available';
    }

    public function cacheTtlSeconds(): int
    {

        return 600;
    }

    public function cacheTags(): array
    {
        return ['furniture'];
    }
}
