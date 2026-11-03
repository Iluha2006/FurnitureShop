<?php

namespace App\Data;

use App\Enums\Currency;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Collection;

readonly class OrderData
{
    /**
     * @param  array<int, OrderItemData>  $items
     */
    public function __construct(
        public int $id,
        public string $status,
        public string $status_label,
        public string $total_amount,
        public string $total_currency,
        public string $total_formatted,
        public string $created_at,
        public ?string $customer_name,
        public ?string $customer_phone,
        public ?string $customer_email,
        public ?string $delivery_address,
        public ?string $comment,
        public array $items,
        public int $count,
    ) {
        //
    }

    public static function fromModel(Order $order): self
    {
        $currency = Currency::fromCode($order->total_currency);
        $items = $order->items
            ->map(fn (OrderItem $item) => OrderItemData::fromModel($item))
            ->all();

        return new self(
            id: $order->id,
            status: $order->status->value,
            status_label: $order->status->label(),
            total_amount: $order->total_amount,
            total_currency: $order->total_currency,
            total_formatted: $currency->format($order->total_amount),
            created_at: $order->created_at?->format('d.m.Y H:i') ?? '',
            customer_name: $order->customer_name,
            customer_phone: $order->customer_phone,
            customer_email: $order->customer_email,
            delivery_address: $order->delivery_address,
            comment: $order->comment,
            items: $items,
            count: (int) $order->items->sum('quantity'),
        );
    }

    /**
     * @param  Collection<int, Order>  $orders
     * @return array<int, self>
     */
    public static function fromCollection(Collection $orders): array
    {
        return $orders
            ->map(fn (Order $order) => self::fromModel($order))
            ->all();
    }
}
