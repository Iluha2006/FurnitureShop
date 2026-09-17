<?php

namespace App\Data;

use App\Enums\Currency;
use App\Models\CartItem;
use Illuminate\Support\Collection;

readonly class CartData
{
    public function __construct(
        public array $items,
        public string $total,
        public string $total_formatted,
        public int $count,
    ) {
        //
    }

    public static function fromItems(Collection $items): self
    {
        $first = $items->first();
        $currency = $first instanceof CartItem
            ? Currency::fromCode($first->price_currency)
            : Currency::Ruble;

        $total = round(
            $items->sum(fn (CartItem $item) => (float) $item->price_amount * $item->quantity),
            2,
        );

        $itemsData = [];

        foreach ($items as $item) {
            $itemsData[] = CartItemData::fromModel($item);
        }

        return new self(
            items: $itemsData,
            total: number_format($total, 2, '.', ''),
            total_formatted: $currency->format($total),
            count: (int) $items->sum('quantity'),
        );
    }
}
