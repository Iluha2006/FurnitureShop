<?php

namespace App\Repositories;

use App\Data\FurnitureCardData;
use App\Data\FurnitureDetailData;
use App\Models\Furniture;
use App\Repositories\Contracts\FurnitureRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
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
     * Get in-stock furniture that belongs to the given category.
     * Used when navigating into a category.
     *
     * @return LengthAwarePaginator<int, FurnitureCardData>
     */
    public function getByCategory(int $categoryId, int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        return $this->availableQuery()
            ->where('category_id', $categoryId)
            ->with(['manufacturer', 'images'])
            ->paginate($perPage, ['*'], 'page', $page)
            ->through(fn (Furniture $furniture) => FurnitureCardData::fromModel($furniture));
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
        return $this->query()->where('quantity', '>', 0);
    }
}
