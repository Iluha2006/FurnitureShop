<?php

namespace App\Handlers\Command;

use App\Command\RemoveFromCartCommand;
use App\Interfaces\CommandHandlerInterface;
use App\Interfaces\CommandInterface;
use App\Repositories\CartRepository;

final class RemoveFromCartHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly CartRepository $cartRepository,
    ) {
        //
    }

    public function handle(CommandInterface $command): void
    {
        if (! $command instanceof RemoveFromCartCommand) {
            throw new \InvalidArgumentException('Unsupported command: '.$command::class);
        }

        $cart = $this->cartRepository->forUser($command->userId);

        $this->cartRepository->remove($cart, $command->furnitureId);
    }
}
