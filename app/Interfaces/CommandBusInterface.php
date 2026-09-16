<?php

namespace App\Interfaces;

interface CommandBusInterface
{
    public function dispatch(CommandInterface $command): void;
}
