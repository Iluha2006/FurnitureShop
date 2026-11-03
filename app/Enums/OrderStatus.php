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
        return match ($this) {
            self::Pending => 'Ожидает подтверждения',
            self::Processing => 'В обработке',
            self::Shipped => 'Отправлен',
            self::Delivered => 'Доставлен',
            self::Cancelled => 'Отменён',
        };
    }
}
