<?php

namespace App\Command;

use App\Interfaces\CommandInterface;

final class ClearCartCommand implements CommandInterface
{
    public function __construct(
        public readonly int $userId,
    ) {
        //
    }
}
