<?php

namespace App\Handlers\Queries;

use App\Interfaces\QueryHandlerInterface;
use App\Interfaces\QueryInterface;
use App\Queries\GetRelatedFurnitureQuery;
use App\Repositories\Contracts\FurnitureRepositoryContract;

final class GetRelatedFurnitureHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly FurnitureRepositoryContract $furnitureRepository,
    ) {
        //
    }

    public function handle(QueryInterface $queryInterface): mixed
    {
        if (! $queryInterface instanceof GetRelatedFurnitureQuery) {
            throw new \InvalidArgumentException('Unsupported query: '.$queryInterface::class);
        }

        return $this->furnitureRepository->related(
            $queryInterface->categoryId,
            $queryInterface->excludeId,
            $queryInterface->limit,
        );
    }
}
