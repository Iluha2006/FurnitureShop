<?php

namespace App\Handlers\Queries;

use App\Interfaces\QueryHandlerInterface;
use App\Interfaces\QueryInterface;
use App\Queries\GetFurnitureFilterOptionsQuery;
use App\Repositories\FurnitureSpecificationRepository;

final class GetFurnitureFilterOptionsHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly FurnitureSpecificationRepository $specificationRepository,
    ) {
        //
    }

    public function handle(QueryInterface $queryInterface): mixed
    {
        if (! $queryInterface instanceof GetFurnitureFilterOptionsQuery) {
            throw new \InvalidArgumentException('Unsupported query: '.$queryInterface::class);
        }

        return $this->specificationRepository->getFilterOptionsForCategory($queryInterface->categoryId);
    }
}
