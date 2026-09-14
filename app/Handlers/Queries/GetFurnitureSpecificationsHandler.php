<?php

namespace App\Handlers\Queries;

use App\Interfaces\QueryHandlerInterface;
use App\Interfaces\QueryInterface;
use App\Queries\GetFurnitureSpecificationsQuery;
use App\Repositories\FurnitureSpecificationRepository;

final class GetFurnitureSpecificationsHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly FurnitureSpecificationRepository $specificationRepository,
    ) {
        //
    }

    public function handle(QueryInterface $queryInterface): mixed
    {
        if (! $queryInterface instanceof GetFurnitureSpecificationsQuery) {
            throw new \InvalidArgumentException('Unsupported query: '.$queryInterface::class);
        }

        return $this->specificationRepository->getForFurniture($queryInterface->furnitureId);
    }
}
