<?php

namespace App\Handlers\Queries;

use App\Interfaces\QueryHandlerInterface;
use App\Interfaces\QueryInterface;
use App\Models\Furniture;
use App\Queries\GetFurnitureQuery;
use App\Repositories\Contracts\FurnitureRepositoryContract;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class GetFurnitureHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly FurnitureRepositoryContract $furnitureRepository,
    ) {
        //
    }

    public function handle(QueryInterface $queryInterface): mixed
    {
        if (! $queryInterface instanceof GetFurnitureQuery) {
            throw new \InvalidArgumentException('Unsupported query: '.$queryInterface::class);
        }

        $furniture = $this->furnitureRepository->getDetail($queryInterface->furnitureId);

        if ($furniture === null) {
            throw (new ModelNotFoundException)->setModel(
                Furniture::class,
                $queryInterface->furnitureId,
            );
        }

        return $furniture;
    }
}
