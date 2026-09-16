<?php

namespace App\Http\Controllers;

use App\Command\AddToCartCommand;
use App\Command\ClearCartCommand;
use App\Command\DecrementCartItemCommand;
use App\Command\IncrementCartItemCommand;
use App\Command\RemoveFromCartCommand;
use App\Http\Requests\Cart\AddToCartRequest;
use App\Interfaces\CommandBusInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        private readonly CommandBusInterface $commandBus,
    ) {
        //
    }

    public function add(AddToCartRequest $request): RedirectResponse
    {
        $this->commandBus->dispatch(new AddToCartCommand(
            userId: $request->user()->id,
            furnitureId: $request->integer('furniture_id'),
            quantity: $request->integer('quantity', 1),
        ));

        return back()->with('status', 'Товар добавлен в корзину.');
    }

    public function increment(Request $request, int $furnitureId): RedirectResponse
    {
        $this->commandBus->dispatch(new IncrementCartItemCommand(
            userId: $request->user()->id,
            furnitureId: $furnitureId,
        ));

        return back();
    }

    public function decrement(Request $request, int $furnitureId): RedirectResponse
    {
        $this->commandBus->dispatch(new DecrementCartItemCommand(
            userId: $request->user()->id,
            furnitureId: $furnitureId,
        ));

        return back();
    }

    public function remove(Request $request, int $furnitureId): RedirectResponse
    {
        $this->commandBus->dispatch(new RemoveFromCartCommand(
            userId: $request->user()->id,
            furnitureId: $furnitureId,
        ));

        return back();
    }

    public function clear(Request $request): RedirectResponse
    {
        $this->commandBus->dispatch(new ClearCartCommand(
            userId: $request->user()->id,
        ));

        return back();
    }
}
