<?php

namespace App\Command;

use App\Interfaces\CommandInterface;

final class IncrementCartItemCommand implements CommandInterface
{
    public function __construct(
        public readonly int $userId,
        public readonly int $furnitureId,
    ) {
        //
    }
}
