<?php

namespace App\Handlers\Queries;

use App\Interfaces\QueryHandlerInterface;
use App\Interfaces\QueryInterface;
use App\Queries\GetCategoriesQuery;
use App\Repositories\FurnitureCategoryRepository;

final class GetCategoriesHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly FurnitureCategoryRepository $categoryRepository,
    ) {}

    public function handle(QueryInterface $queryInterface): mixed
    {
        if (! $queryInterface instanceof GetCategoriesQuery) {
            throw new \InvalidArgumentException('Unsupported query: '.$queryInterface::class);
        }

        return $this->categoryRepository->getCategory();
    }
}
