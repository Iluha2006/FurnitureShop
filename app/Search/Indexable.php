<?php

namespace App\Search;

/**
 * Contract for models that are mirrored into Elasticsearch.
 *
 * Implemented by the {@see Searchable} trait, which supplies the bodies.
 */
interface Indexable
{
    /**
     * Index the model is stored in.
     */
    public function getSearchIndex(): string;

    /**
     * Document type within the index.
     */
    public function getSearchType(): string;

    /**
     * Flatten the model into the Elasticsearch document body.
     *
     * @return array<string, mixed>
     */
    public function toSearchArray(): array;
}
