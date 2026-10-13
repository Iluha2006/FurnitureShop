<?php

namespace App\Search;

trait Searchable
{
    /**
     * Index this model is stored in.
     */
    public function getSearchIndex(): string
    {
        return $this->getTable();
    }

    /**
     * Document type within the index.
     */
    public function getSearchType(): string
    {
        if (property_exists($this, 'useSearchType')) {
            return (string) $this->useSearchType;
        }

        return $this->getTable();
    }

    /**
     * Flatten the model for the Elasticsearch document body.
     *
     * @return array<string, mixed>
     */
    public function toSearchArray(): array
    {
        return $this->toArray();
    }
}
