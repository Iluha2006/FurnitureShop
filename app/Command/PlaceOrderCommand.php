<?php

namespace App\Command;

use App\Interfaces\CommandInterface;

/**
 * Turns the current user's cart into an order.
 *
 * Contact details are collected during checkout and stored with the order.
 * The order is either delivered to an address or picked up at a pickup point.
 */
final class PlaceOrderCommand implements CommandInterface
{
    public function __construct(
        public readonly int $userId,
        public readonly string $customerName,
        public readonly string $customerPhone,
        public readonly string $customerEmail,
        public readonly string $deliveryAddress,
        public readonly string $deliveryMethod = 'delivery',
        public readonly ?string $pickupPointId = null,
        public readonly ?string $comment = null,
    ) {
        //
    }
}
