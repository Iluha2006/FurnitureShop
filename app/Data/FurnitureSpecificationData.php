<?php

namespace App\Data;

use App\Models\FurnitureSpecification;

readonly class FurnitureSpecificationData
{
    public function __construct(
        public int $id,
        public ?float $width_cm,
        public ?float $length_cm,
        public ?float $height_cm,
        public ?string $folding_type,
        public ?string $insert_type,
        public ?string $materials,
        public ?string $surface,
        public ?float $weight_kg,
        public ?float $package_volume_m3,
        public ?string $warranty,
    ) {
        //
    }

    public static function fromModel(FurnitureSpecification $specification): self
    {
        return new self(
            id: $specification->id,
            width_cm: $specification->width_cm,
            length_cm: $specification->length_cm,
            height_cm: $specification->height_cm,
            folding_type: $specification->folding_type,
            insert_type: $specification->insert_type,
            materials: $specification->materials,
            surface: $specification->surface,
            weight_kg: $specification->weight_kg,
            package_volume_m3: $specification->package_volume_m3,
            warranty: $specification->warranty,
        );
    }
}
