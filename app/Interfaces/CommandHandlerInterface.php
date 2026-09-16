<?php

namespace App\Interfaces;

interface CommandHandlerInterface
{
    public function handle(CommandInterface $command): void;
}
