<?php

namespace App\Repositories;

use App\Repositories\Contracts\Repository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 *
 * @implements Repository<TModel>
 */
abstract class BaseRepository implements Repository
{
    /**
     * @param  TModel  $model
     */
    public function __construct(
        protected Model $model,
    ) {
        //
    }

    public function find(int $id): ?Model
    {
        return $this->query()->find($id);
    }

    public function findOrFail(int $id): Model
    {
        return $this->query()->findOrFail($id);
    }

    public function all(): Collection
    {
        return $this->query()->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()->paginate($perPage);
    }

    public function create(array $attributes): Model
    {
        return $this->query()->create($attributes);
    }

    /**
     * @return Builder<TModel>
     */
    protected function newQuery(): Builder
    {
        return $this->model->newQuery();
    }

    public function update(Model $model, array $attributes): bool
    {
        return $model->update($attributes);
    }

    public function delete(Model $model): bool
    {
        return (bool) $model->delete();
    }

    /**
     * Get a fresh query builder for the model.
     *
     * @return Builder<TModel>
     */
    protected function query(): Builder
    {
        return $this->model->newQuery();
    }
}
