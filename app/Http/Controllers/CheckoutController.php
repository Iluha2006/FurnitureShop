<?php

namespace App\Http\Controllers;

use App\Command\PlaceOrderCommand;
use App\Http\Requests\Checkout\PlaceOrderRequest;
use App\Interfaces\CommandBusInterface;
use App\Interfaces\QueryBusInterface;
use App\Models\PickupPoint;
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
            'pickupPoints' => $this->pickupPoints(),
            'deliveryCity' => config('yandex.city.name'),
            'cityCenter' => [
                'latitude' => (float) config('yandex.city.latitude'),
                'longitude' => (float) config('yandex.city.longitude'),
            ],
            'yandexMapsApiKey' => (string) config('yandex.maps_api_key'),
        ]);
    }

    public function store(PlaceOrderRequest $request): RedirectResponse
    {
        $deliveryMethod = $request->string('delivery_method')
            ->trim()
            ->toString() ?: 'delivery';
        $pickupPointId = $deliveryMethod === 'pickup'
            ? $request->string('pickup_point_id')->toString()
            : null;
        $deliveryAddress = $deliveryMethod === 'pickup'
            ? $this->pickupDeliveryAddress($pickupPointId)
            : $request->string('delivery_address')->trim()->toString();

        $this->commandBus->dispatch(new PlaceOrderCommand(
            userId: $request->user()->id,
            customerName: $request->string('customer_name')->trim()->toString(),
            customerPhone: $request->string('customer_phone')->trim()->toString(),
            customerEmail: $request->string('customer_email')->trim()->toString(),
            deliveryAddress: $deliveryAddress,
            deliveryMethod: $deliveryMethod,
            pickupPointId: $pickupPointId,
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

    /**
     * Active pickup points for the map and list picker.
     *
     * @return array<int, array<string, mixed>>
     */
    private function pickupPoints(): array
    {
        return PickupPoint::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->orderBy('name')
            ->get()
            ->map(fn (PickupPoint $point): array => [
                'id' => $point->id,
                'name' => $point->name,
                'address' => $point->address,
                'phone' => $point->phone,
                'email' => $point->email,
                'opening_hours' => $point->opening_hours,
                'description' => $point->description,
                'latitude' => (float) $point->latitude,
                'longitude' => (float) $point->longitude,
            ])
            ->all();
    }

    private function pickupDeliveryAddress(?string $pickupPointId): string
    {
        if ($pickupPointId === null) {
            return '';
        }

        $point = PickupPoint::query()->find($pickupPointId);

        return $point instanceof PickupPoint
            ? $point->address
            : '';
    }
}
