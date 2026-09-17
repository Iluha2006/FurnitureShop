<?php

namespace App\Data;

use App\Models\Furniture;
use Spatie\LaravelData\Data;

class FurnitureCardData extends Data
{
    public function __construct(
        public FurnitureData $furniture,
        public ?FurnitureManufacturerData $manufacturer = null,
        public ?string $mainImage = null,
    ) {
        //
    }

    public static function fromModel(Furniture $furniture): self
    {
        $mainImage = $furniture->relationLoaded('images')
            ? $furniture->images->firstWhere('is_main', true)
            : null;

        return new self(
            furniture: FurnitureData::fromModel($furniture),
            manufacturer: $furniture->relationLoaded('manufacturer') && $furniture->manufacturer !== null
                ? FurnitureManufacturerData::fromModel($furniture->manufacturer)
                : null,
            mainImage: $mainImage !== null
                ? FurnitureImageData::resolveUrl($mainImage->path_image)
                : null,
        );
    }
}
