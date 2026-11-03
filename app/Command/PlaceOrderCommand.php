<?php

namespace App\Command;

use App\Interfaces\CommandInterface;

/**
 * Turns the current user's cart into an order.
 *
 * Contact details are collected during checkout and stored with the order.
 */
final class PlaceOrderCommand implements CommandInterface
{
    public function __construct(
        public readonly int $userId,
        public readonly string $customerName,
        public readonly string $customerPhone,
        public readonly string $customerEmail,
        public readonly string $deliveryAddress,
        public readonly ?string $comment = null,
    ) {
        //
    }
}
