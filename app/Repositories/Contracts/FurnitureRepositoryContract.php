<?php

namespace App\Repositories\Contracts;

use App\Data\FurnitureCardData;
use App\Data\FurnitureDetailData;
use App\Models\Furniture;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * @extends Repository<Furniture>
 */
interface FurnitureRepositoryContract extends Repository
{
    /**
     * Find furniture with its relations loaded.
     */
    public function findWithRelations(int $id): ?Furniture;

    /**
     * Paginate furniture that is currently in stock.
     *
     * @return LengthAwarePaginator<int, Furniture>
     */
    public function paginateAvailable(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get a furniture item as a detail DTO with its relations loaded.
     */
    public function getDetail(int $id): ?FurnitureDetailData;

    /**
     * Get in-stock furniture that belongs to the given category.
     *
     * @return LengthAwarePaginator<int, FurnitureCardData>
     */
    public function getByCategory(int $categoryId, int $perPage = 15, int $page = 1): LengthAwarePaginator;

    /**
     * Get a short list of in-stock furniture for the "Хиты продаж" section.
     *
     * @return Collection<int, FurnitureCardData>
     */
    public function hits(int $limit = 14): Collection;

    /**
     * Count furniture items that are currently in stock.
     */
    public function countAvailable(): int;
}
