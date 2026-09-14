<?php

namespace App\Handlers\Queries;

use App\Interfaces\QueryHandlerInterface;
use App\Interfaces\QueryInterface;
use App\Queries\GetFurnitureHitsQuery;
use App\Repositories\Contracts\FurnitureRepositoryContract;

final class GetFurnitureHitsHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly FurnitureRepositoryContract $furnitureRepository,
    ) {
        //
    }

    public function handle(QueryInterface $queryInterface): mixed
    {
        if (! $queryInterface instanceof GetFurnitureHitsQuery) {
            throw new \InvalidArgumentException('Unsupported query: '.$queryInterface::class);
        }

        return $this->furnitureRepository->hits($queryInterface->limit);
    }
}
