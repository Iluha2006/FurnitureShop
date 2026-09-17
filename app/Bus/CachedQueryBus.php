<?php

namespace App\Bus;

use App\Interfaces\CacheableQuery;
use App\Interfaces\QueryBusInterface;
use App\Interfaces\QueryInterface;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

final class CachedQueryBus implements QueryBusInterface
{
    public function __construct(
        private readonly QueryBusInterface $bus,
        private readonly CacheRepository $cache,
    ) {}

    public function ask(QueryInterface $query): mixed
    {
        if (! $query instanceof CacheableQuery) {
            return $this->bus->ask($query);
        }

        $tags = $query->cacheTags();

        if ($tags !== [] && method_exists($this->cache, 'supportsTags') && $this->cache->supportsTags()) {

            return $this->cache->tags($tags)->remember(

                $query->cacheKey(),

                $query->cacheTtlSeconds(),

                fn () => $this->bus->ask($query),
            );
        }

        return $this->cache->remember(
            $query->cacheKey(),
            $query->cacheTtlSeconds(),
            fn () => $this->bus->ask($query),
        );
    }
}
