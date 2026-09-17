<?php

namespace App\Repositories;

use App\Data\Filter\FurnitureFilterData;
use App\Data\FurnitureCardData;
use App\Data\FurnitureDetailData;
use App\Models\Furniture;
use App\Repositories\Contracts\FurnitureRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;

/**
 * @extends BaseRepository<Furniture>
 */
class FurnitureRepository extends BaseRepository implements FurnitureRepositoryContract
{
    public function __construct(Furniture $furniture)
    {
        parent::__construct($furniture);
    }

    public function findWithRelations(int $id): ?Furniture
    {
        return $this->query()
            ->with(['category', 'manufacturer', 'images'])
            ->find($id);
    }

    public function paginateAvailable(int $perPage = 15): LengthAwarePaginator
    {
        return $this->availableQuery()
            ->with(['manufacturer', 'images'])
            ->paginate($perPage);
    }

    public function getDetail(int $id): ?FurnitureDetailData
    {
        $furniture = $this->findWithRelations($id);

        return $furniture === null ? null : FurnitureDetailData::fromModel($furniture);
    }

    /**
     * Get in-stock furniture that belongs to the given category, filtered by
     * the characteristics. Used when navigating into a category.
     *
     * @return LengthAwarePaginator<int, FurnitureCardData>
     */
    public function getByCategory(
        int $categoryId,
        int $perPage = 15,
        int $page = 1,
        ?FurnitureFilterData $filter = null,
    ): LengthAwarePaginator {
        $query = $this->availableQuery()
            ->where('furniture.category_id', $categoryId)
            ->with(['manufacturer', 'images'])
            ->orderByDesc('furniture.created_at')
            ->orderBy('furniture.furniture_id');

        $this->applyFilters($query, $filter);

        return $query->paginate($perPage, ['*'], 'page', $page)
            ->through(fn (Furniture $furniture) => FurnitureCardData::fromModel($furniture));
    }

    /**
     * Get a short list of in-stock furniture from the same category,
     * excluding the current item — used for the "Похожие товары" section.
     *
     * @return Collection<int, FurnitureCardData>
     */
    public function related(int $categoryId, int $excludeId, int $limit = 4): Collection
    {
        return $this->availableQuery()
            ->where('category_id', $categoryId)
            ->whereNot('furniture_id', $excludeId)
            ->inRandomOrder()
            ->limit($limit)
            ->get()
            ->map(fn (Furniture $furniture) => FurnitureCardData::fromModel($furniture))
            ->values();
    }

    /**
     * Get a short list of in-stock furniture for the "Хиты продаж" section.
     *
     * @return Collection<int, FurnitureCardData>
     */
    public function hits(int $limit = 14): Collection
    {
        return $this->availableQuery()
            ->with(['manufacturer', 'images'])
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (Furniture $furniture) => FurnitureCardData::fromModel($furniture))
            ->values();
    }

    public function countAvailable(): int
    {
        return $this->availableQuery()->count();
    }

    /**
     * @return Builder<Furniture>
     */
    protected function availableQuery(): Builder
    {
        return $this->query()->where('furniture.quantity', '>', 0);
    }

    protected function applyFilters(Builder $query, ?FurnitureFilterData $filter): void
    {
        if ($filter === null || ! $filter->hasActiveFilters()) {
            return;
        }

        if ($filter->price_min !== null) {
            $query->where('furniture.price', '>=', $filter->price_min);
        }

        if ($filter->price_max !== null) {
            $query->where('furniture.price', '<=', $filter->price_max);
        }

        if ($filter->manufacture !== null && $filter->manufacture !== []) {
            $query->whereIn('furniture.manufacturer_id', $filter->manufacture);
        }

        $this->applySpecificationFilters($query, $filter);
    }

    protected function applySpecificationFilters(Builder $query, FurnitureFilterData $filter): void
    {
        $hasSpecFilters = $filter->width_min !== null
            || $filter->width_max !== null
            || $filter->height_min !== null
            || $filter->height_max !== null
            || $filter->length_min !== null
            || $filter->length_max !== null
            || $filter->foldingType !== null
            || $filter->surface !== null
            || $filter->materials !== null;

        if (! $hasSpecFilters) {
            return;
        }

        $this->joinSpecifications($query);

        if ($filter->width_min !== null) {
            $query->where('furniture_specifications.width_cm', '>=', $filter->width_min);
        }

        if ($filter->width_max !== null) {
            $query->where('furniture_specifications.width_cm', '<=', $filter->width_max);
        }

        if ($filter->height_min !== null) {
            $query->where('furniture_specifications.height_cm', '>=', $filter->height_min);
        }

        if ($filter->height_max !== null) {
            $query->where('furniture_specifications.height_cm', '<=', $filter->height_max);
        }

        if ($filter->length_min !== null) {
            $query->where('furniture_specifications.length_cm', '>=', $filter->length_min);
        }

        if ($filter->length_max !== null) {
            $query->where('furniture_specifications.length_cm', '<=', $filter->length_max);
        }

        if ($filter->foldingType !== null) {
            $query->whereIn('furniture_specifications.folding_type', $filter->foldingType);
        }

        if ($filter->surface !== null) {
            $query->whereIn('furniture_specifications.surface', $filter->surface);
        }

        if ($filter->materials !== null) {
            $query->whereIn('furniture_specifications.materials', $filter->materials);
        }
    }

    /**
     * Join the specifications table once per query and only select furniture
     * columns, so characteristics filters and sorts do not leak spec columns
     * into hydrated models or duplicate rows.
     *
     * @return Builder<Furniture>
     */
    protected function joinSpecifications(Builder $query): Builder
    {
        $alreadyJoined = collect($query->getQuery()->joins ?? [])->contains(
            fn (JoinClause $join) => (string) $join->table === 'furniture_specifications',
        );

        if (! $alreadyJoined) {
            $query->join(
                'furniture_specifications',
                'furniture.specifications_id',
                '=',
                'furniture_specifications.id',
            );
            $query->select('furniture.*');
        }

        return $query;
    }
}
