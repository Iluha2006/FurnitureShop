<?php

namespace App\Data;

use App\Models\FurnitureManufacturer;

readonly class FurnitureManufacturerData
{
    public function __construct(
        public int $manufacturer_id,
        public string $name,
        public ?string $slug,
    ) {
        //
    }

    public static function fromModel(FurnitureManufacturer $manufacturer): self
    {
        return new self(
            manufacturer_id: $manufacturer->manufacturer_id,
            name: $manufacturer->name,
            slug: $manufacturer->slug,
        );
    }
}
