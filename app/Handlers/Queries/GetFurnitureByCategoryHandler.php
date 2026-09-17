<?php

namespace App\Handlers\Queries;

use App\Interfaces\QueryHandlerInterface;
use App\Interfaces\QueryInterface;
use App\Queries\GetFurnitureByCategoryQuery;
use App\Repositories\Contracts\FurnitureRepositoryContract;

final class GetFurnitureByCategoryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly FurnitureRepositoryContract $furnitureRepository,
    ) {
        //
    }

    public function handle(QueryInterface $queryInterface): mixed
    {
        if (! $queryInterface instanceof GetFurnitureByCategoryQuery) {
            throw new \InvalidArgumentException('Unsupported query: '.$queryInterface::class);
        }

        return $this->furnitureRepository->getByCategory(
            $queryInterface->categoryId,
            $queryInterface->perPage,
            $queryInterface->page,
            $queryInterface->filter,
        );
    }
}
