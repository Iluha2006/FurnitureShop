<?php

namespace App\Queries;

use App\Data\OrderData;
use App\Interfaces\QueryInterface;

/**
 * Read the authenticated user's orders projection.
 *
 * Intentionally NOT cacheable: orders are user-specific and change over time.
 *
 * @return array<int, OrderData>
 */
final class GetOrdersQuery implements QueryInterface
{
    public function __construct(
        public readonly int $userId,
    ) {
        //
    }
}
