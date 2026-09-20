<?php

namespace App\Queries;

use App\Data\FurnitureCardData;
use App\Interfaces\QueryInterface;
use Illuminate\Support\Collection;

/**
 * @return Collection<int, FurnitureCardData>
 */
final class SearchFurnitureQuery implements QueryInterface
{
    public function __construct(
        public readonly string $q,
    ) {
        //
    }
}
