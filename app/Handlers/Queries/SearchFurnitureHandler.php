<?php

namespace App\Handlers\Queries;

use App\Data\FurnitureCardData;
use App\Interfaces\QueryHandlerInterface;
use App\Interfaces\QueryInterface;
use App\Models\Furniture;
use App\Queries\SearchFurnitureQuery;
use App\Repositories\ElasticsearchRepository;
use InvalidArgumentException;

final class SearchFurnitureHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly ElasticsearchRepository $elasticsearchRepository,
    ) {
        //
    }

    public function handle(QueryInterface $queryInterface): mixed
    {
        if (! $queryInterface instanceof SearchFurnitureQuery) {
            throw new InvalidArgumentException('Unsupported query: '.$queryInterface::class);
        }

        return $this->elasticsearchRepository
            ->search($queryInterface->q)
            ->map(fn (Furniture $furniture) => FurnitureCardData::fromModel($furniture));
    }
}
