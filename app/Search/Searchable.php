<?php

namespace App\Search;

trait Searchable
{
    public function getSearchIndex()
    {
        return $this->getTable();
    }

    public function getSearchType()
    {
        if (property_exists($this, 'useSearchType')) {
            return $this->useSearchType;
        }

        return $this->getTable();
    }

    public function toSearchArray(): array
    {
        return $this->toArray();
    }
}
