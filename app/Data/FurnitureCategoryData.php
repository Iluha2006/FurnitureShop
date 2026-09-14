<?php

namespace App\Data;

use App\Models\FurnitureCategory;

readonly class FurnitureCategoryData
{
    public function __construct(
        public int $category_id,
        public string $name,
    ) {
        //
    }

    public static function fromModel(FurnitureCategory $category): self
    {
        return new self(
            category_id: $category->category_id,
            name: $category->name,
        );
    }
}
