<?php

namespace App\Repositories;

use App\Data\FurnitureCategoryData;
use App\Models\FurnitureCategory;
use Illuminate\Support\Collection;

/**
 * @extends BaseRepository<FurnitureCategory>
 */
class FurnitureCategoryRepository extends BaseRepository
{
    public function __construct(FurnitureCategory $furniture)
    {
        parent::__construct($furniture);
    }

    /**
     * @return Collection<int, FurnitureCategoryData>
     */
    public function getCategory(): Collection
    {
        return $this->all()
            ->map(fn (FurnitureCategory $category) => FurnitureCategoryData::fromModel($category));
    }
}
