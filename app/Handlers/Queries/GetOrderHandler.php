<?php

namespace App\Handlers\Queries;

use App\Interfaces\QueryHandlerInterface;
use App\Interfaces\QueryInterface;
use Override;

class GetOrderHandler implements QueryHandlerInterface
{
    #[Override]
    public function handle(QueryInterface $queryInterface): mixed
    {
        throw new \Exception('Not implemented');
    }
}
