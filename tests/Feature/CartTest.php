<?php

use App\Command\AddToCartCommand;
use App\Data\CartData;
use App\Data\CartItemData;
use App\Enums\Currency;
use App\Interfaces\CommandBusInterface;
use App\Interfaces\QueryBusInterface;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Furniture;
use App\Models\User;
use App\Queries\GetCartQuery;
use App\Repositories\CartRepository;
use Inertia\Testing\AssertableInertia as Assert;

test('currency formats ruble amounts with a Руб. suffix', function () {
    expect(Currency::Ruble->format('25000.00'))->toBe('25 000 Руб.')
        ->and(Currency::Ruble->format('1250.50'))->toBe('1 250,5 Руб.')
        ->and(Currency::Ruble->format('0'))->toBe('0 Руб.');
});

test('authenticated user can add furniture to the cart', function () {
    $user = User::factory()->create();
    $furniture = Furniture::factory()->create(['price' => '25000.00']);

    $this->actingAs($user)
        ->post(route('cart.add'), [
            'furniture_id' => $furniture->furniture_id,
            'quantity' => 2,
        ])
        ->assertRedirect();

    $cart = Cart::where('user_id', $user->id)->first();

    expect($cart)->not->toBeNull();

    $item = $cart->items()->where('furniture_id', $furniture->furniture_id)->first();

    expect($item)->not->toBeNull()
        ->and($item->quantity)->toBe(2)
        ->and($item->price_amount)->toBe('25000.00')
        ->and($item->price_currency)->toBe('RUB');
});

test('adding the same furniture twice accumulates the quantity', function () {
    $user = User::factory()->create();
    $furniture = Furniture::factory()->create();

    $this->actingAs($user)->post(route('cart.add'), ['furniture_id' => $furniture->furniture_id]);
    $this->actingAs($user)->post(route('cart.add'), [
        'furniture_id' => $furniture->furniture_id,
        'quantity' => 3,
    ]);

    expect(CartItem::count())->toBe(1)
        ->and(CartItem::first()->quantity)->toBe(4);
});

test('increment increases the item quantity', function () {
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $furniture = Furniture::factory()->create();
    $item = CartItem::factory()->create([
        'cart_id' => $cart->id,
        'furniture_id' => $furniture->furniture_id,
        'quantity' => 2,
    ]);

    $this->actingAs($user)
        ->post(route('cart.increment', ['furniture_id' => $furniture->furniture_id]))
        ->assertRedirect();

    expect($item->refresh()->quantity)->toBe(3);
});

test('decrement reduces the item quantity', function () {
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $furniture = Furniture::factory()->create();
    $item = CartItem::factory()->create([
        'cart_id' => $cart->id,
        'furniture_id' => $furniture->furniture_id,
        'quantity' => 3,
    ]);

    $this->actingAs($user)
        ->post(route('cart.decrement', ['furniture_id' => $furniture->furniture_id]))
        ->assertRedirect();

    expect($item->refresh()->quantity)->toBe(2);
});

test('decrementing below one removes the item from the cart', function () {
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $furniture = Furniture::factory()->create();
    $item = CartItem::factory()->create([
        'cart_id' => $cart->id,
        'furniture_id' => $furniture->furniture_id,
        'quantity' => 1,
    ]);

    $this->actingAs($user)
        ->post(route('cart.decrement', ['furniture_id' => $furniture->furniture_id]))
        ->assertRedirect();

    expect(CartItem::where('id', $item->id)->exists())->toBeFalse();
});

test('user can remove an item from the cart', function () {
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $furniture = Furniture::factory()->create();
    $item = CartItem::factory()->create([
        'cart_id' => $cart->id,
        'furniture_id' => $furniture->furniture_id,
        'quantity' => 4,
    ]);

    $this->actingAs($user)
        ->delete(route('cart.remove', ['furniture_id' => $furniture->furniture_id]))
        ->assertRedirect();

    expect(CartItem::where('id', $item->id)->exists())->toBeFalse();
});

test('user can clear the cart', function () {
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id]);
    CartItem::factory()->count(3)->create(['cart_id' => $cart->id]);

    $this->actingAs($user)->post(route('cart.clear'))->assertRedirect();

    expect(CartItem::where('cart_id', $cart->id)->count())->toBe(0);
});

test('guest cannot modify the cart', function () {
    $furniture = Furniture::factory()->create();

    $this->post(route('cart.add'), ['furniture_id' => $furniture->furniture_id])
        ->assertRedirect(route('login'));
});

test('adding furniture requires a valid furniture id', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('cart.add'), [])
        ->assertSessionHasErrors('furniture_id');

    $this->actingAs($user)
        ->post(route('cart.add'), ['furniture_id' => 999999])
        ->assertSessionHasErrors('furniture_id');
});

test('cart data projection formats prices in rubles', function () {
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $furniture = Furniture::factory()->create(['price' => '25000.00']);
    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'furniture_id' => $furniture->furniture_id,
        'quantity' => 3,
        'price_amount' => '25000.00',
        'price_currency' => 'RUB',
    ]);

    $data = app(CartRepository::class)->buildCartData($cart);

    expect($data)->toBeInstanceOf(CartData::class)
        ->and($data->count)->toBe(3)
        ->and($data->total)->toBe('75000.00')
        ->and($data->total_formatted)->toBe('75 000 Руб.')
        ->and($data->items)->toHaveCount(1);

    $item = $data->items[0];

    expect($item)->toBeInstanceOf(CartItemData::class)
        ->and($item->quantity)->toBe(3)
        ->and($item->price_formatted)->toBe('25 000 Руб.')
        ->and($item->line_total)->toBe('75000.00')
        ->and($item->line_total_formatted)->toBe('75 000 Руб.');
});

test('command bus resolves and dispatches a cart mutation', function () {
    $user = User::factory()->create();
    $furniture = Furniture::factory()->create(['price' => '10000.00']);

    $bus = app(CommandBusInterface::class);

    $bus->dispatch(new AddToCartCommand(
        userId: $user->id,
        furnitureId: $furniture->furniture_id,
        quantity: 1,
    ));

    $cartData = app(QueryBusInterface::class)->ask(new GetCartQuery($user->id));

    expect($cartData)->toBeInstanceOf(CartData::class)
        ->and($cartData->count)->toBe(1)
        ->and($cartData->total_formatted)->toBe('10 000 Руб.')
        ->and(CartItem::count())->toBe(1);
});

test('cart data can be read through the query bus', function () {
    $user = User::factory()->create();
    $furniture = Furniture::factory()->create(['price' => '15000.00']);

    app(CommandBusInterface::class)->dispatch(
        new AddToCartCommand($user->id, $furniture->furniture_id, 2),
    );

    $cartData = app(QueryBusInterface::class)->ask(new GetCartQuery($user->id));

    expect($cartData)->toBeInstanceOf(CartData::class)
        ->and($cartData->count)->toBe(2)
        ->and($cartData->total)->toBe('30000.00')
        ->and($cartData->total_formatted)->toBe('30 000 Руб.')
        ->and($cartData->items)->toHaveCount(1)
        ->and($cartData->items[0]->price_formatted)->toBe('15 000 Руб.');
});

test('query bus returns empty cart for a new user', function () {
    $user = User::factory()->create();

    $cartData = app(QueryBusInterface::class)->ask(new GetCartQuery($user->id));

    expect($cartData)->toBeInstanceOf(CartData::class)
        ->and($cartData->count)->toBe(0)
        ->and($cartData->total)->toBe('0.00')
        ->and($cartData->total_formatted)->toBe('0 Руб.')
        ->and($cartData->items)->toBeEmpty();
});

test('guests are redirected to login when visiting the cart page', function () {
    $this->get(route('cart.index'))->assertRedirect(route('login'));
});

test('authenticated users can fetch cart data through the cart index route', function () {
    $user = User::factory()->create();
    $furniture = Furniture::factory()->create(['price' => '15000.00']);

    app(CommandBusInterface::class)->dispatch(
        new AddToCartCommand($user->id, $furniture->furniture_id, 2),
    );

    $response = $this->actingAs($user)->get(route('cart.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('cart/index')
        ->where('cart.count', 2)
        ->where('cart.total_formatted', '30 000 Руб.')
        ->has('cart.items', 1)
        ->where('cart.items.0.price_formatted', '15 000 Руб.'),
    );
});

test('cart index renders an empty cart for a user without items', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('cart.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('cart/index')
        ->where('cart.count', 0)
        ->where('cart.total_formatted', '0 Руб.')
        ->where('cart.items', []),
    );
});
