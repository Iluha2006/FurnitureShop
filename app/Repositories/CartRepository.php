<?php

namespace App\Repositories;

use App\Data\CartData;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Furniture;

/**
 * @extends BaseRepository<Cart>
 */
class CartRepository extends BaseRepository
{
    public function __construct(Cart $cart)
    {
        parent::__construct($cart);
    }

    public function forUser(int $userId): Cart
    {
        return $this->query()->firstOrCreate(['user_id' => $userId]);
    }

    public function addFurniture(Cart $cart, Furniture $furniture, int $quantity = 1): CartItem
    {
        $item = CartItem::query()
            ->where('cart_id', $cart->id)
            ->where('furniture_id', $furniture->furniture_id)
            ->first();

        if ($item === null) {
            return CartItem::query()->create([
                'cart_id' => $cart->id,
                'furniture_id' => $furniture->furniture_id,
                'quantity' => $quantity,
                'price_amount' => $furniture->price,
                'price_currency' => 'RUB',
            ]);
        }

        $item->increment('quantity', $quantity);

        return $item;
    }

    public function increment(Cart $cart, int $furnitureId): CartItem
    {
        $item = $this->item($cart, $furnitureId);

        $item->increment('quantity');

        return $item;
    }

    public function decrement(Cart $cart, int $furnitureId): ?CartItem
    {
        $item = $this->item($cart, $furnitureId);

        if ($item->quantity <= 1) {
            $item->delete();

            return null;
        }

        $item->decrement('quantity');

        return $item;
    }

    public function remove(Cart $cart, int $furnitureId): bool
    {
        return (bool) CartItem::query()
            ->where('cart_id', $cart->id)
            ->where('furniture_id', $furnitureId)
            ->delete();
    }

    public function clear(Cart $cart): void
    {
        CartItem::query()->where('cart_id', $cart->id)->delete();
    }

    public function count(Cart $cart): int
    {
        return (int) CartItem::query()->where('cart_id', $cart->id)->sum('quantity');
    }

    public function buildCartData(Cart $cart): CartData
    {
        $items = CartItem::query()
            ->with('furniture.images')
            ->where('cart_id', $cart->id)
            ->orderBy('id')
            ->get();

        return CartData::fromItems($items);
    }

    private function item(Cart $cart, int $furnitureId): CartItem
    {
        return CartItem::query()
            ->where('cart_id', $cart->id)
            ->where('furniture_id', $furnitureId)
            ->firstOrFail();
    }
}
