<?php

namespace App\Handlers\Queries;

use App\Interfaces\QueryHandlerInterface;
use App\Interfaces\QueryInterface;
use App\Queries\GetCartQuantitiesQuery;
use App\Repositories\CartRepository;

final class GetCartQuantitiesHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly CartRepository $cartRepository,
    ) {
        //
    }

    /**
     * @return array<string, int>
     */
    public function handle(QueryInterface $queryInterface): array
    {
        if (! $queryInterface instanceof GetCartQuantitiesQuery) {
            throw new \InvalidArgumentException('Unsupported query: '.$queryInterface::class);
        }

        return $this->cartRepository->quantitiesForUser($queryInterface->userId);
    }
}
