<?php

namespace App\Handlers\Command;

use App\Command\ClearCartCommand;
use App\Interfaces\CommandHandlerInterface;
use App\Interfaces\CommandInterface;
use App\Repositories\CartRepository;

final class ClearCartHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly CartRepository $cartRepository,
    ) {
        //
    }

    public function handle(CommandInterface $command): void
    {
        if (! $command instanceof ClearCartCommand) {
            throw new \InvalidArgumentException('Unsupported command: '.$command::class);
        }

        $cart = $this->cartRepository->forUser($command->userId);

        $this->cartRepository->clear($cart);
    }
}
