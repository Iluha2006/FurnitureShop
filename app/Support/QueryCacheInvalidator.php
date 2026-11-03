<?php

namespace App\Support;

use Illuminate\Contracts\Cache\Repository as CacheRepository;

/**
 * Invalidates read-model cache entries from the write side of CQRS.
 *
 * Command handlers call this after a successful state change so cached
 * query results never outlive the data they were built from.
 */
final class QueryCacheInvalidator
{
    public function __construct(
        private readonly CacheRepository $cache,
    ) {
        //
    }

    /**
     * Drop every cache entry tagged with any of the given tags.
     *
     * @param  list<string>  $tags
     */
    public function invalidate(array $tags): void
    {
        if ($tags === []) {
            return;
        }

        if (method_exists($this->cache, 'supportsTags') && $this->cache->supportsTags()) {
            $this->cache->tags($tags)->flush();

            return;
        }

        // Without tag support there is no safe way to target only the
        // affected entries, so fall back to clearing the whole store.
        $this->cache->flush();
    }
}
