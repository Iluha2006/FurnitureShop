<?php

namespace App\Queries;

use App\Data\CartData;
use App\Interfaces\QueryInterface;

/**
 * Read the current user's cart projection.
 *
 * Intentionally NOT cacheable: the cart is user-specific and volatile.
 *
 * @return CartData
 */
final class GetCartQuery implements QueryInterface
{
    public function __construct(
        public readonly int $userId,
    ) {
        //
    }
}
