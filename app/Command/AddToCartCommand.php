<?php

namespace App\Command;

use App\Interfaces\CommandInterface;

/**
 * Adds a furniture item to the user's cart, accumulating the quantity
 * when the line already exists.
 */
final class AddToCartCommand implements CommandInterface
{
    public function __construct(
        public readonly int $userId,
        public readonly int $furnitureId,
        public readonly int $quantity = 1,
    ) {
        //
    }
}
