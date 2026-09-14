<?php

namespace App\Interfaces;

interface QueryBusInterface
{
    public function ask(QueryInterface $query): mixed;
}
