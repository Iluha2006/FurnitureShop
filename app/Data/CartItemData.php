<?php

namespace App\Data;

use App\Enums\Currency;
use App\Models\CartItem;
use App\Models\FurnitureImage;

readonly class CartItemData
{
    public function __construct(
        public int $id,
        public int $furniture_id,
        public string $name,
        public int $quantity,
        public string $price_amount,
        public string $price_currency,
        public string $price_formatted,
        public string $line_total,
        public string $line_total_formatted,
        public ?string $mainImage = null,
    ) {
        //
    }

    public static function fromModel(CartItem $item): self
    {
        $furniture = $item->furniture;
        $currency = Currency::fromCode($item->price_currency);

        $lineTotal = round((float) $item->price_amount * $item->quantity, 2);

        $mainImage = $furniture->images->firstWhere('is_main', true);

        return new self(
            id: $item->id,
            furniture_id: $item->furniture_id,
            name: $furniture->name,
            quantity: $item->quantity,
            price_amount: $item->price_amount,
            price_currency: $item->price_currency,
            price_formatted: $currency->format($item->price_amount),
            line_total: number_format($lineTotal, 2, '.', ''),
            line_total_formatted: $currency->format($lineTotal),
            mainImage: $mainImage instanceof FurnitureImage
                ? FurnitureImageData::resolveUrl($mainImage->path_image)
                : null,
        );
    }
}
