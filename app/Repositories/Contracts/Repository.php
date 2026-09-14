<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 */
interface Repository
{
    /**
     * Find a model by its primary key.
     *
     * @return TModel|null
     */
    public function find(int $id): ?Model;

    /**
     * Find a model by its primary key or throw an exception.
     *
     * @return TModel
     */
    public function findOrFail(int $id): Model;

    /**
     * Get all models.
     *
     * @return Collection<int, TModel>
     */
    public function all(): Collection;

    /**
     * Paginate the models.
     *
     * @return LengthAwarePaginator<int, TModel>
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Create a new model.
     *
     * @param  array<string, mixed>  $attributes
     * @return TModel
     */
    public function create(array $attributes): Model;

    /**
     * Update the given model.
     *
     * @param  TModel  $model
     * @param  array<string, mixed>  $attributes
     */
    public function update(Model $model, array $attributes): bool;

    /**
     * Delete the given model.
     *
     * @param  TModel  $model
     */
    public function delete(Model $model): bool;
}
