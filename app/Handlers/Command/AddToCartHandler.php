<?php

namespace App\Handlers\Command;

use App\Command\AddToCartCommand;
use App\Interfaces\CommandHandlerInterface;
use App\Interfaces\CommandInterface;
use App\Models\Furniture;
use App\Repositories\CartRepository;
use App\Repositories\Contracts\FurnitureRepositoryContract;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class AddToCartHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly CartRepository $cartRepository,
        private readonly FurnitureRepositoryContract $furnitureRepository,
    ) {
        //
    }

    public function handle(CommandInterface $command): void
    {
        if (! $command instanceof AddToCartCommand) {
            throw new \InvalidArgumentException('Unsupported command: '.$command::class);
        }

        $furniture = $this->furnitureRepository->find($command->furnitureId);

        if (! $furniture instanceof Furniture) {
            throw (new ModelNotFoundException)->setModel(
                Furniture::class,
                $command->furnitureId,
            );
        }

        $cart = $this->cartRepository->forUser($command->userId);

        $this->cartRepository->addFurniture($cart, $furniture, $command->quantity);
    }
}
