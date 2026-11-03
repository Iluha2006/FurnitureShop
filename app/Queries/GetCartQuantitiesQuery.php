<?php

namespace App\Queries;

use App\Interfaces\QueryInterface;

final class GetCartQuantitiesQuery implements QueryInterface
{
    public function __construct(
        public readonly int $userId,
    ) {
        //
    }
}
