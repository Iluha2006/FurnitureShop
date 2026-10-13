<?php

namespace App\Queries;

use App\Interfaces\QueryInterface;

/**
 * Read the quantity of every product already in the current user's cart.
 *
 * Lets the storefront switch a product from "add to cart" to a quantity
 * stepper without loading the whole cart.
 *
 * Intentionally NOT cacheable: the cart is user-specific and volatile.
 */
final class GetCartQuantitiesQuery implements QueryInterface
{
    public function __construct(
        public readonly int $userId,
    ) {
        //
    }
}
