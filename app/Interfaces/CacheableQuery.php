<?php

namespace App\Interfaces;

/**
 * Marks a query whose result should be cached transparently by the bus.
 *
 * Queries that are not cacheable (user-specific, volatile, side-effect driven)
 * simply do not implement this interface and bypass the cache entirely.
 */
interface CacheableQuery extends QueryInterface
{
    /**
     * A stable, unique key identifying the exact result set.
     * Must include every parameter that changes the result.
     */
    public function cacheKey(): string;

    /**
     * Number of seconds the result stays fresh in the cache.
     */
    public function cacheTtlSeconds(): int;

    /**
     * Optional tags used to invalidate groups of cached results at once.
     * Only used when the configured cache store supports tags.
     *
     * @return list<string>
     */
    public function cacheTags(): array;
}
