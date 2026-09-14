<?php

namespace App\Repositories;

use App\Data\FurnitureSpecificationData;
use App\Models\Furniture;
use App\Models\FurnitureSpecification;

/**
 * Read/write access to furniture specifications (characteristics).
 *
 * Kept as a separate repository on purpose: characteristics are a distinct
 * aggregate and a hot read path, so it can be cached and evolved independently
 * of the furniture repository.
 *
 * @extends BaseRepository<FurnitureSpecification>
 */
class FurnitureSpecificationRepository extends BaseRepository
{
    public function __construct(FurnitureSpecification $specification)
    {
        parent::__construct($specification);
    }

    public function getById(int $id): ?FurnitureSpecificationData
    {
        $specification = $this->query()->find($id);

        return $specification === null ? null : FurnitureSpecificationData::fromModel($specification);
    }

    /**
     * Resolve a furniture item's characteristics through its specification.
     */
    public function getForFurniture(int $furnitureId): ?FurnitureSpecificationData
    {
        $specificationsId = Furniture::query()
            ->whereKey($furnitureId)
            ->value('specifications_id');

        if ($specificationsId === null) {
            return null;
        }

        return $this->getById($specificationsId);
    }
}
