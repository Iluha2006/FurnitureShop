<?php

namespace App\Handlers\Command;

use App\Command\DecrementCartItemCommand;
use App\Interfaces\CommandHandlerInterface;
use App\Interfaces\CommandInterface;
use App\Repositories\CartRepository;

final class DecrementCartItemHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly CartRepository $cartRepository,
    ) {
        //
    }

    public function handle(CommandInterface $command): void
    {
        if (! $command instanceof DecrementCartItemCommand) {
            throw new \InvalidArgumentException('Unsupported command: '.$command::class);
        }

        $cart = $this->cartRepository->forUser($command->userId);

        $this->cartRepository->decrement($cart, $command->furnitureId);
    }
}
