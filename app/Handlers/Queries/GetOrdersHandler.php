<?php

namespace App\Handlers\Queries;

use App\Interfaces\QueryHandlerInterface;
use App\Interfaces\QueryInterface;
use App\Queries\GetOrdersQuery;
use App\Repositories\OrderRepository;

final class GetOrdersHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
    ) {
        //
    }

    public function handle(QueryInterface $queryInterface): mixed
    {
        if (! $queryInterface instanceof GetOrdersQuery) {
            throw new \InvalidArgumentException('Unsupported query: '.$queryInterface::class);
        }

        return $this->orderRepository->buildForUser($queryInterface->userId);
    }
}
