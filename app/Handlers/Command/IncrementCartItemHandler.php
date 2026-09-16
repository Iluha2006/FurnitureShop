<?php

namespace App\Handlers\Command;

use App\Command\IncrementCartItemCommand;
use App\Interfaces\CommandHandlerInterface;
use App\Interfaces\CommandInterface;
use App\Repositories\CartRepository;

final class IncrementCartItemHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly CartRepository $cartRepository,
    ) {
        //
    }

    public function handle(CommandInterface $command): void
    {
        if (! $command instanceof IncrementCartItemCommand) {
            throw new \InvalidArgumentException('Unsupported command: '.$command::class);
        }

        $cart = $this->cartRepository->forUser($command->userId);

        $this->cartRepository->increment($cart, $command->furnitureId);
    }
}
