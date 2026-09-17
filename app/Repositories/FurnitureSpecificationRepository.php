<?php

namespace App\Repositories;

use App\Data\Filter\FilterOptionsData;
use App\Data\Filter\HeightRangeData;
use App\Data\Filter\LengthRangeData;
use App\Data\Filter\PriceRangeData;
use App\Data\Filter\SortOptionData;
use App\Data\Filter\WidthRangeData;
use App\Data\FurnitureSpecificationData;
use App\Models\Furniture;
use App\Models\FurnitureManufacturer;
use App\Models\FurnitureSpecification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

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

    public function getFilterOptionsForCategory(int $categoryId): FilterOptionsData
    {
        return new FilterOptionsData(
            price_range: $this->getPriceRangeForCategory($categoryId),
            widthRangeData: $this->getWidthRangeForCategory($categoryId),
            heightRangeData: $this->getHeightRangeForCategory($categoryId),
            lengthRangeData: $this->getLengthRangeForCategory($categoryId),
            folding_type: $this->getDistinctSpecValuesForCategory($categoryId, 'folding_type')->all(),
            surface: $this->getDistinctSpecValuesForCategory($categoryId, 'surface')->all(),
            materials: $this->getDistinctSpecValuesForCategory($categoryId, 'materials')->all(),
            manufacture: $this->getManufacturersForCategory($categoryId)->all(),
        );
    }

    protected function getPriceRangeForCategory(int $categoryId): PriceRangeData
    {
        $range = $this->availableForCategory($categoryId)
            ->selectRaw('MIN(furniture.price) AS min_cm, MAX(furniture.price) AS max_cm')
            ->first();

        return new PriceRangeData(
            min_cm: (float) ($range?->min_cm ?? 0),
            max_cm: (float) ($range?->max_cm ?? 0),
        );
    }

    protected function getWidthRangeForCategory(int $categoryId): WidthRangeData
    {
        $range = $this->availableForCategory($categoryId)
            ->join('furniture_specifications', 'furniture.specifications_id', '=', 'furniture_specifications.id')
            ->selectRaw('MIN(furniture_specifications.width_cm) AS min_cm, MAX(furniture_specifications.width_cm) AS max_cm')
            ->first();

        return new WidthRangeData(
            min_cm: (float) ($range?->min_cm ?? 0),
            max_cm: (float) ($range?->max_cm ?? 0),
        );
    }

    protected function getHeightRangeForCategory(int $categoryId): HeightRangeData
    {
        $range = $this->availableForCategory($categoryId)
            ->join('furniture_specifications', 'furniture.specifications_id', '=', 'furniture_specifications.id')
            ->selectRaw('MIN(furniture_specifications.height_cm) AS min_cm, MAX(furniture_specifications.height_cm) AS max_cm')
            ->first();

        return new HeightRangeData(
            min_cm: (float) ($range?->min_cm ?? 0),
            max_cm: (float) ($range?->max_cm ?? 0),
        );
    }

    protected function getLengthRangeForCategory(int $categoryId): LengthRangeData
    {
        $range = $this->availableForCategory($categoryId)
            ->join('furniture_specifications', 'furniture.specifications_id', '=', 'furniture_specifications.id')
            ->selectRaw('MIN(furniture_specifications.length_cm) AS min_cm, MAX(furniture_specifications.length_cm) AS max_cm')
            ->first();

        return new LengthRangeData(
            min_cm: (float) ($range?->min_cm ?? 0),
            max_cm: (float) ($range?->max_cm ?? 0),
        );
    }

    protected function getDistinctSpecValuesForCategory(int $categoryId, string $column): Collection
    {
        $values = $this->availableForCategory($categoryId)
            ->join('furniture_specifications', 'furniture.specifications_id', '=', 'furniture_specifications.id')
            ->whereNotNull('furniture_specifications.'.$column)
            ->distinct()
            ->pluck('furniture_specifications.'.$column)
            ->sort()
            ->values();

        return $values->map(fn (string $value) => new SortOptionData(value: $value, label: $value));
    }

    protected function getManufacturersForCategory(int $categoryId): Collection
    {
        return FurnitureManufacturer::query()
            ->whereHas('furniture', fn (Builder $query) => $query
                ->where('furniture.category_id', $categoryId)
                ->where('furniture.quantity', '>', 0))
            ->orderBy('name')
            ->get(['manufacturer_id', 'name'])
            ->map(
                fn (FurnitureManufacturer $manufacturer) => new SortOptionData(
                    value: (string) $manufacturer->manufacturer_id,
                    label: $manufacturer->name,
                ),
            )
            ->values();
    }

    protected function availableForCategory(int $categoryId): Builder
    {
        return Furniture::query()
            ->where('furniture.category_id', $categoryId)
            ->where('furniture.quantity', '>', 0);
    }
}
