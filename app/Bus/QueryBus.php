<?php

namespace App\Bus;

use App\Interfaces\QueryBusInterface;
use App\Interfaces\QueryHandlerInterface;
use App\Interfaces\QueryInterface;
use Illuminate\Contracts\Container\Container;

final class QueryBus implements QueryBusInterface
{
    public function __construct(
        private readonly Container $container,
    ) {
        //
    }

    public function ask(QueryInterface $query): mixed
    {
        return $this->container->make($this->resolveHandler($query))->handle($query);
    }

    private function resolveHandler(QueryInterface $query): string
    {
        $short = class_basename($query);

        if (! str_ends_with($short, 'Query')) {
            throw new \InvalidArgumentException("Query [{$short}] must end with 'Query'.");
        }

        $handler = 'App\\Handlers\\Queries\\'.substr($short, 0, -5).'Handler';

        if (! is_a($handler, QueryHandlerInterface::class, true)) {
            throw new \InvalidArgumentException("Handler [{$handler}] does not implement ".QueryHandlerInterface::class.'.');
        }

        return $handler;
    }
}
