<?php

namespace App\Handlers\Command;

use App\Command\PlaceOrderCommand;
use App\Interfaces\CommandHandlerInterface;
use App\Interfaces\CommandInterface;
use App\Repositories\OrderRepository;
use App\Support\QueryCacheInvalidator;

final class PlaceOrderHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly QueryCacheInvalidator $cacheInvalidator,
    ) {
        //
    }

    public function handle(CommandInterface $command): void
    {
        if (! $command instanceof PlaceOrderCommand) {
            throw new \InvalidArgumentException('Unsupported command: '.$command::class);
        }

        $this->orderRepository->place(
            userId: $command->userId,
            customerName: $command->customerName,
            customerPhone: $command->customerPhone,
            customerEmail: $command->customerEmail,
            deliveryAddress: $command->deliveryAddress,
            deliveryMethod: $command->deliveryMethod,
            pickupPointId: $command->pickupPointId,
            comment: $command->comment,
        );

        // Placing an order decrements stock, so every cached catalogue read
        // model (detail, hits, counts, filters, category pages) is stale now.
        $this->cacheInvalidator->invalidate(['furniture']);
    }
}
