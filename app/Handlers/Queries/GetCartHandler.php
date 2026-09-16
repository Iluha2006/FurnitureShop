<?php

namespace App\Handlers\Queries;

use App\Interfaces\QueryHandlerInterface;
use App\Interfaces\QueryInterface;
use App\Queries\GetCartQuery;
use App\Repositories\CartRepository;

final class GetCartHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly CartRepository $cartRepository,
    ) {
        //
    }

    public function handle(QueryInterface $queryInterface): mixed
    {
        if (! $queryInterface instanceof GetCartQuery) {
            throw new \InvalidArgumentException('Unsupported query: '.$queryInterface::class);
        }

        $cart = $this->cartRepository->forUser($queryInterface->userId);

        return $this->cartRepository->buildCartData($cart);
    }
}
