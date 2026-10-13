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

/**
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
        $range = $this->rangeAttributes(
            $this->availableForCategory($categoryId)
                ->selectRaw('MIN(furniture.price) AS min_cm, MAX(furniture.price) AS max_cm')
        );

        return new PriceRangeData(
            min_cm: (float) $range['min_cm'],
            max_cm: (float) $range['max_cm'],
        );
    }

    protected function getWidthRangeForCategory(int $categoryId): WidthRangeData
    {
        $range = $this->rangeAttributes(
            $this->availableForCategory($categoryId)
                ->join('furniture_specifications', 'furniture.specifications_id', '=', 'furniture_specifications.id')
                ->selectRaw('MIN(furniture_specifications.width_cm) AS min_cm, MAX(furniture_specifications.width_cm) AS max_cm')
        );

        return new WidthRangeData(
            min_cm: (float) $range['min_cm'],
            max_cm: (float) $range['max_cm'],
        );
    }

    protected function getHeightRangeForCategory(int $categoryId): HeightRangeData
    {
        $range = $this->rangeAttributes(
            $this->availableForCategory($categoryId)
                ->join('furniture_specifications', 'furniture.specifications_id', '=', 'furniture_specifications.id')
                ->selectRaw('MIN(furniture_specifications.height_cm) AS min_cm, MAX(furniture_specifications.height_cm) AS max_cm')
        );

        return new HeightRangeData(
            min_cm: (float) $range['min_cm'],
            max_cm: (float) $range['max_cm'],
        );
    }

    protected function getLengthRangeForCategory(int $categoryId): LengthRangeData
    {
        $range = $this->rangeAttributes(
            $this->availableForCategory($categoryId)
                ->join('furniture_specifications', 'furniture.specifications_id', '=', 'furniture_specifications.id')
                ->selectRaw('MIN(furniture_specifications.length_cm) AS min_cm, MAX(furniture_specifications.length_cm) AS max_cm')
        );

        return new LengthRangeData(
            min_cm: (float) $range['min_cm'],
            max_cm: (float) $range['max_cm'],
        );
    }

    /**
     * Distinct values of a specification column within a category.
     *
     * @return Collection<int, SortOptionData>
     */
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

    /**
     * Manufacturers that have at least one in-stock product in the category.
     *
     * @return Collection<int, SortOptionData>
     */
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

    /**
     * Read the min/max aliases produced by a range aggregate query.
     *
     * @param  Builder<Furniture>  $query
     * @return array{min_cm: mixed, max_cm: mixed}
     */
    protected function rangeAttributes(Builder $query): array
    {
        $row = $query->first()?->getAttributes() ?? [];

        return ['min_cm' => $row['min_cm'] ?? 0, 'max_cm' => $row['max_cm'] ?? 0];
    }

    /**
     * In-stock furniture of the given category.
     *
     * @return Builder<Furniture>
     */
    protected function availableForCategory(int $categoryId): Builder
    {
        return Furniture::query()
            ->where('furniture.category_id', $categoryId)
            ->where('furniture.quantity', '>', 0);
    }
}
