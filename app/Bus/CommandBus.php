<?php

namespace App\Bus;

use App\Interfaces\CommandBusInterface;
use App\Interfaces\CommandHandlerInterface;
use App\Interfaces\CommandInterface;
use Illuminate\Contracts\Container\Container;

final class CommandBus implements CommandBusInterface
{
    public function __construct(
        private readonly Container $container,
    ) {
        //
    }

    public function dispatch(CommandInterface $command): void
    {
        $this->container->make($this->resolveHandler($command))->handle($command);
    }

    private function resolveHandler(CommandInterface $command): string
    {
        $short = class_basename($command);

        if (! str_ends_with($short, 'Command')) {
            throw new \InvalidArgumentException("Command [{$short}] must end with 'Command'.");
        }

        $handler = 'App\\Handlers\\Command\\'.substr($short, 0, -7).'Handler';

        if (! is_a($handler, CommandHandlerInterface::class, true)) {
            throw new \InvalidArgumentException(
                "Handler [{$handler}] does not implement ".CommandHandlerInterface::class.'.',
            );
        }

        return $handler;
    }
}
