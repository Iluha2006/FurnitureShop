<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    /**
     * Get the display label for the status.
     */
    public function label(): string
    {
        return ucfirst($this->value);
    }
}
