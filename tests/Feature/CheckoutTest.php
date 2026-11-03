<?php

use App\Data\OrderData;
use App\Interfaces\QueryBusInterface;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Furniture;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Queries\GetOrdersQuery;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to login from checkout', function () {
    $this->get(route('checkout.index'))->assertRedirect(route('login'));
    $this->post(route('checkout.store'))->assertRedirect(route('login'));
});

test('checkout page shows the cart and prefills the customer', function () {
    $user = User::factory()->create(['name' => 'Иван Иванов']);
    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $furniture = Furniture::factory()->create([
        'name' => 'Диван угловой',
        'price' => '42000.00',
        'quantity' => 5,
    ]);
    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'furniture_id' => $furniture->furniture_id,
        'quantity' => 2,
        'price_amount' => '42000.00',
    ]);

    $this->actingAs($user)
        ->get(route('checkout.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('checkout/index')
            ->where('customer.name', 'Иван Иванов')
            ->where('customer.email', $user->email)
            ->where('cart.count', 2)
            ->where('cart.total_formatted', '84 000 Руб.')
            ->where('cart.items.0.name', 'Диван угловой'),
        );
});

test('checkout redirects an empty cart back to the cart page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('checkout.index'))
        ->assertRedirect(route('cart.index'));
});

test('placing an order snapshots cart prices, clears the cart and reserves stock', function () {
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $furniture = Furniture::factory()->create([
        'name' => 'Стол обеденный',
        'price' => '30000.00',
        'quantity' => 10,
    ]);
    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'furniture_id' => $furniture->furniture_id,
        'quantity' => 2,
        // The cart price is snapshotted when the item is added, so it may
        // differ from the current furniture price.
        'price_amount' => '25000.00',
        'price_currency' => 'RUB',
    ]);

    $this->actingAs($user)
        ->post(route('checkout.store'), [
            'customer_name' => 'Иван Иванов',
            'customer_phone' => '+7 900 000-00-00',
            'customer_email' => 'ivan@example.com',
            'delivery_address' => 'Москва, ул. Ленина, д. 1, кв. 10',
            'comment' => 'Позвонить за час до доставки',
        ])
        ->assertRedirect(route('account.orders'));

    $order = Order::query()->where('user_id', $user->id)->first();

    expect($order)->not->toBeNull()
        ->and($order->status->value)->toBe('pending')
        ->and($order->total_amount)->toBe('50000.00')
        ->and($order->total_currency)->toBe('RUB')
        ->and($order->customer_name)->toBe('Иван Иванов')
        ->and($order->customer_phone)->toBe('+7 900 000-00-00')
        ->and($order->customer_email)->toBe('ivan@example.com')
        ->and($order->delivery_address)->toBe('Москва, ул. Ленина, д. 1, кв. 10')
        ->and($order->comment)->toBe('Позвонить за час до доставки');

    $this->assertDatabaseHas('order_items', [
        'order_id' => $order->id,
        'furniture_id' => $furniture->furniture_id,
        'quantity' => 2,
        'price_amount' => '25000.00',
        'price_currency' => 'RUB',
    ]);

    expect(CartItem::where('cart_id', $cart->id)->count())->toBe(0)
        ->and($furniture->refresh()->quantity)->toBe(8);
});

test('checkout validates the customer details', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('checkout.store'), [])
        ->assertSessionHasErrors([
            'customer_name',
            'customer_phone',
            'customer_email',
            'delivery_address',
        ]);

    expect(Order::count())->toBe(0);
});

test('an order cannot be placed when a cart line exceeds the stock', function () {
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $furniture = Furniture::factory()->create([
        'name' => 'Кресло',
        'quantity' => 2,
    ]);
    $item = CartItem::factory()->create([
        'cart_id' => $cart->id,
        'furniture_id' => $furniture->furniture_id,
        'quantity' => 5,
    ]);

    $this->actingAs($user)
        ->post(route('checkout.store'), [
            'customer_name' => 'Иван Иванов',
            'customer_phone' => '+7 900 000-00-00',
            'customer_email' => 'ivan@example.com',
            'delivery_address' => 'Москва, ул. Ленина, д. 1',
        ])
        ->assertSessionHasErrors('cart');

    expect(Order::count())->toBe(0)
        ->and(CartItem::where('id', $item->id)->exists())->toBeTrue()
        ->and($furniture->refresh()->quantity)->toBe(2);
});

test('account orders page lists only the current user orders', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'total_amount' => '42000.00',
        'total_currency' => 'RUB',
    ]);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'quantity' => 2,
        'price_amount' => '21000.00',
    ]);

    Order::factory()->create(['user_id' => $otherUser->id]);

    $this->actingAs($user)
        ->get(route('account.orders'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('account/orders')
            ->has('orders', 1)
            ->where('orders.0.id', $order->id)
            ->where('orders.0.status_label', 'Ожидает подтверждения')
            ->where('orders.0.total_formatted', '42 000 Руб.')
            ->where('orders.0.count', 2)
            ->has('orders.0.items', 1),
        );
});

test('account orders page renders an empty state without orders', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('account.orders'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('account/orders')
            ->where('orders', []),
        );
});

test('orders can be read through the query bus', function () {
    $user = User::factory()->create();
    $furniture = Furniture::factory()->create(['price' => '15000.00']);
    $order = Order::factory()->create([
        'user_id' => $user->id,
        'total_amount' => '30000.00',
        'total_currency' => 'RUB',
    ]);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'furniture_id' => $furniture->furniture_id,
        'quantity' => 2,
        'price_amount' => '15000.00',
    ]);

    $orders = app(QueryBusInterface::class)->ask(new GetOrdersQuery($user->id));

    expect($orders)->toHaveCount(1)
        ->and($orders[0])->toBeInstanceOf(OrderData::class)
        ->and($orders[0]->total_formatted)->toBe('30 000 Руб.')
        ->and($orders[0]->count)->toBe(2)
        ->and($orders[0]->items)->toHaveCount(1)
        ->and($orders[0]->items[0]->line_total_formatted)->toBe('30 000 Руб.');
});
