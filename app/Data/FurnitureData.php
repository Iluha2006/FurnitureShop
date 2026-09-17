<?php

namespace App\Data;

use App\Models\Furniture;
use Spatie\LaravelData\Data;

class FurnitureData extends Data
{
    public function __construct(
        public int $furniture_id,
        public string $name,
        public ?string $description,
        public ?string $color,
        public string $price,
        public int $quantity,
        public ?int $manufacturer_id,
        public int $category_id,
        public ?int $specifications_id,
    ) {
        //
    }

    public static function fromModel(Furniture $furniture): self
    {
        return new self(
            furniture_id: $furniture->furniture_id,
            name: $furniture->name,
            description: $furniture->description,
            color: $furniture->color,
            price: $furniture->price,
            quantity: $furniture->quantity,
            manufacturer_id: $furniture->manufacturer_id,
            category_id: $furniture->category_id,
            specifications_id: $furniture->specifications_id,
        );
    }
}
