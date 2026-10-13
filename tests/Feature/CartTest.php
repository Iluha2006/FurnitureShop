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
    $furniture = Furniture::factory()->create([
        'price' => '25000.00',
        'quantity' => 5,
    ]);

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
        // The price is snapshotted from the furniture row, not re-read from the request.
        ->and((float) $item->price_amount)->toBe(25000.0)
        ->and($item->price_currency)->toBe('RUB');
});

test('adding the same furniture twice accumulates the quantity', function () {
    $user = User::factory()->create();
    $furniture = Furniture::factory()->create(['quantity' => 10]);

    $this->actingAs($user)->post(route('cart.add'), ['furniture_id' => $furniture->furniture_id]);
    $this->actingAs($user)->post(route('cart.add'), [
        'furniture_id' => $furniture->furniture_id,
        'quantity' => 3,
    ]);

    expect(CartItem::count())->toBe(1)
        ->and(CartItem::first()->quantity)->toBe(4);
});

test('adding more units than the product has in stock is rejected', function () {
    $user = User::factory()->create();
    $furniture = Furniture::factory()->create(['quantity' => 3]);

    $this->actingAs($user)
        ->post(route('cart.add'), [
            'furniture_id' => $furniture->furniture_id,
            'quantity' => 4,
        ])
        ->assertSessionHasErrors('quantity', 'В наличии только 3 шт.');

    expect(CartItem::count())->toBe(0);
});

test('adding a product that is sold out is rejected', function () {
    $user = User::factory()->create();
    $furniture = Furniture::factory()->create(['quantity' => 0]);

    $this->actingAs($user)
        ->post(route('cart.add'), ['furniture_id' => $furniture->furniture_id])
        ->assertSessionHasErrors('quantity', 'Товара нет в наличии.');

    expect(CartItem::count())->toBe(0);
});

test('adding the same furniture again tops the cart up to the stock', function () {
    $user = User::factory()->create();
    $furniture = Furniture::factory()->create(['quantity' => 5]);

    $this->actingAs($user)->post(route('cart.add'), [
        'furniture_id' => $furniture->furniture_id,
        'quantity' => 4,
    ])->assertRedirect();

    $this->actingAs($user)->post(route('cart.add'), [
        'furniture_id' => $furniture->furniture_id,
        'quantity' => 4,
    ])->assertRedirect();

    expect(CartItem::count())->toBe(1)
        ->and(CartItem::first()->quantity)->toBe(5);
});

test('increment stops at the available stock', function () {
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $furniture = Furniture::factory()->create(['quantity' => 3]);
    $item = CartItem::factory()->create([
        'cart_id' => $cart->id,
        'furniture_id' => $furniture->furniture_id,
        'quantity' => 2,
    ]);

    $this->actingAs($user)
        ->post(route('cart.increment', ['furniture_id' => $furniture->furniture_id]))
        ->assertRedirect();
    $this->actingAs($user)
        ->post(route('cart.increment', ['furniture_id' => $furniture->furniture_id]))
        ->assertRedirect();

    expect($item->refresh()->quantity)->toBe(3);
});

test('the cart page exposes the stock each line can grow to', function () {
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $furniture = Furniture::factory()->create(['quantity' => 8]);
    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'furniture_id' => $furniture->furniture_id,
        'quantity' => 2,
    ]);

    $this->actingAs($user)
        ->get(route('cart.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('cart.items.0.quantity', 2)
            ->where('cart.items.0.stock', 8),
        );
});

test('increment increases the item quantity', function () {
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $furniture = Furniture::factory()->create(['quantity' => 10]);
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
    $furniture = Furniture::factory()->create(['price' => '10000.00', 'quantity' => 10]);

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
    $furniture = Furniture::factory()->create(['price' => '15000.00', 'quantity' => 10]);

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
    $furniture = Furniture::factory()->create(['price' => '15000.00', 'quantity' => 10]);

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

test('cart index shares the item count for the storefront header', function () {
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $furniture = Furniture::factory()->create();
    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'furniture_id' => $furniture->furniture_id,
        'quantity' => 3,
    ]);

    $response = $this->actingAs($user)->get(route('cart.index'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->where('cartCount', 3)
        ->where('cartQuantities', [(string) $furniture->furniture_id => 3]),
    );
});

test('shared cart quantities list every product in the cart', function () {
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $sofa = Furniture::factory()->create();
    $table = Furniture::factory()->create();

    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'furniture_id' => $sofa->furniture_id,
        'quantity' => 2,
    ]);
    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'furniture_id' => $table->furniture_id,
        'quantity' => 5,
    ]);

    $this->actingAs($user)
        ->get(route('catalog'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('cartCount', 7)
            ->where('cartQuantities', [
                (string) $sofa->furniture_id => 2,
                (string) $table->furniture_id => 5,
            ]),
        );
});

test('guests get empty shared cart quantities without a cart record', function () {
    $this->get(route('catalog'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('cartCount', 0)
            ->where('cartQuantities', []),
        );

    expect(Cart::count())->toBe(0);
});

test('shared cart quantities follow cart mutations', function () {
    $user = User::factory()->create();
    $furniture = Furniture::factory()->create(['quantity' => 10]);

    $this->actingAs($user)
        ->post(route('cart.add'), ['furniture_id' => $furniture->furniture_id])
        ->assertRedirect();

    $this->actingAs($user)
        ->get(route('catalog'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('cartCount', 1)
            ->where('cartQuantities', [(string) $furniture->furniture_id => 1]),
        );

    $this->actingAs($user)
        ->post(route('cart.increment', ['furniture_id' => $furniture->furniture_id]))
        ->assertRedirect();

    $this->actingAs($user)
        ->get(route('catalog'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('cartCount', 2)
            ->where('cartQuantities', [(string) $furniture->furniture_id => 2]),
        );

    $this->actingAs($user)
        ->delete(route('cart.remove', ['furniture_id' => $furniture->furniture_id]))
        ->assertRedirect();

    $this->actingAs($user)
        ->get(route('catalog'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('cartCount', 0)
            ->where('cartQuantities', []),
        );
});

test('cart item data exposes the fields rendered by the cart page', function () {
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id]);
    $furniture = Furniture::factory()->create([
        'name' => 'Диван угловой',
        'price' => '42000.00',
    ]);
    CartItem::factory()->create([
        'cart_id' => $cart->id,
        'furniture_id' => $furniture->furniture_id,
        'quantity' => 2,
        'price_amount' => '42000.00',
        'price_currency' => 'RUB',
    ]);

    $this->actingAs($user)
        ->get(route('cart.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('cart.items.0.name', 'Диван угловой')
            ->where('cart.items.0.quantity', 2)
            ->where('cart.items.0.price_formatted', '42 000 Руб.')
            ->where('cart.items.0.line_total_formatted', '84 000 Руб.')
            ->where('cart.total_formatted', '84 000 Руб.'),
        );
});
