<?php

use App\Interfaces\CommandHandlerInterface;
use App\Interfaces\CommandInterface;

class AddOrderHandler implements CommandHandlerInterface
{
    #[Override]
    public function handle(CommandInterface $command): void
    {
        throw new Exception('Not implemented');
    }
}
