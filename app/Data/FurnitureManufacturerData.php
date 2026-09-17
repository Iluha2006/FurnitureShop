<?php

namespace App\Data;

use App\Models\FurnitureManufacturer;
use Spatie\LaravelData\Data;

class FurnitureManufacturerData extends Data
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
