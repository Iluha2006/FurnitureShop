<?php

namespace App\Http\Controllers;

use App\Command\PlaceOrderCommand;
use App\Http\Requests\Checkout\PlaceOrderRequest;
use App\Interfaces\CommandBusInterface;
use App\Interfaces\QueryBusInterface;
use App\Queries\GetCartQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CommandBusInterface $commandBus,
        private readonly QueryBusInterface $queryBus,
    ) {
        //
    }

    public function index(Request $request): Response|RedirectResponse
    {
        $cart = $this->queryBus->ask(new GetCartQuery($request->user()->id));

        if ($cart->count === 0) {
            Inertia::flash('toast', [
                'type' => 'info',
                'message' => 'Корзина пуста — добавьте товары перед оформлением.',
            ]);

            return redirect()->route('cart.index');
        }

        $user = $request->user();

        return Inertia::render('checkout/index', [
            'cart' => $cart,
            'customer' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    public function store(PlaceOrderRequest $request): RedirectResponse
    {
        $this->commandBus->dispatch(new PlaceOrderCommand(
            userId: $request->user()->id,
            customerName: $request->string('customer_name')->trim()->toString(),
            customerPhone: $request->string('customer_phone')->trim()->toString(),
            customerEmail: $request->string('customer_email')->trim()->toString(),
            deliveryAddress: $request->string('delivery_address')->trim()->toString(),
            comment: $request->filled('comment')
                ? $request->string('comment')->trim()->toString()
                : null,
        ));

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Заказ оформлен. Мы свяжемся с вами для подтверждения.',
        ]);

        return redirect()->route('account.orders');
    }
}
