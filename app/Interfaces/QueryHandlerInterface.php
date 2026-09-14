<?php

namespace App\Interfaces;

interface QueryHandlerInterface
{
    public function handle(QueryInterface $queryInterface): mixed;
}
