<?php

namespace App\Queries;

use App\Interfaces\CacheableQuery;

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

        return ['categories'];
    }
}
