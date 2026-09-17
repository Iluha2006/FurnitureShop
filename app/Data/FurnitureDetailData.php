<?php

namespace App\Data;

use App\Models\Furniture;
use App\Models\FurnitureImage;

readonly class FurnitureDetailData
{
    public function __construct(
        public FurnitureData $furniture,
        public ?FurnitureCategoryData $category = null,
        public ?FurnitureManufacturerData $manufacturer = null,
        public array $images = [],
    ) {
        //
    }

    public static function fromModel(Furniture $furniture): self
    {
        return new self(
            furniture: FurnitureData::fromModel($furniture),
            category: $furniture->relationLoaded('category') && $furniture->category !== null
                ? FurnitureCategoryData::fromModel($furniture->category)
                : null,
            manufacturer: $furniture->relationLoaded('manufacturer') && $furniture->manufacturer !== null
                ? FurnitureManufacturerData::fromModel($furniture->manufacturer)
                : null,
            images: $furniture->relationLoaded('images')
                ? array_values(
                    $furniture->images
                        ->map(fn (FurnitureImage $image) => FurnitureImageData::fromModel($image))
                        ->all(),
                )
                : [],
        );
    }
}
