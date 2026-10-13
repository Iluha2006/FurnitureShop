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

    /**
     * Add furniture to the cart, never taking the cart past the stock on hand.
     *
     * Returns the cart item, or null when nothing could be added because the
     * furniture is sold out or the cart already holds every available unit.
     */
    public function addFurniture(Cart $cart, Furniture $furniture, int $quantity = 1): ?CartItem
    {
        $item = CartItem::query()
            ->where('cart_id', $cart->id)
            ->where('furniture_id', $furniture->furniture_id)
            ->first();

        $allowed = min($quantity, $this->stock($furniture) - ($item->quantity ?? 0));

        if ($allowed < 1) {
            return $item;
        }

        if ($item === null) {
            return CartItem::query()->create([
                'cart_id' => $cart->id,
                'furniture_id' => $furniture->furniture_id,
                'quantity' => $allowed,
                'price_amount' => $furniture->price,
                'price_currency' => 'RUB',
            ]);
        }

        $item->increment('quantity', $allowed);

        return $item;
    }

    /**
     * Add one more unit, keeping the item within the stock on hand.
     */
    public function increment(Cart $cart, int $furnitureId): CartItem
    {
        $item = $this->item($cart, $furnitureId);

        if ($item->quantity < $this->stock($item->furniture)) {
            $item->increment('quantity');
        }

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

    /**
     * Sum the item quantities for a user without creating a cart as a side effect.
     */
    public function countForUser(int $userId): int
    {
        return array_sum($this->quantitiesForUser($userId));
    }

    /**
     * Map of furniture id to its quantity in the user's cart.
     *
     * Lets the storefront tell which products are already in the cart without
     * creating a cart as a side effect.
     *
     * @return array<string, int>
     */
    public function quantitiesForUser(int $userId): array
    {
        $cartId = $this->cartIdForUser($userId);

        if ($cartId === null) {
            return [];
        }

        $rows = CartItem::query()
            ->where('cart_id', $cartId)
            ->orderBy('id')
            ->pluck('quantity', 'furniture_id');

        $quantities = [];

        foreach ($rows as $furnitureId => $quantity) {
            $quantities[(string) $furnitureId] = (int) $quantity;
        }

        return $quantities;
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

    /**
     * How many units of this furniture are on hand.
     */
    private function stock(Furniture $furniture): int
    {
        return max(0, (int) $furniture->quantity);
    }

    private function item(Cart $cart, int $furnitureId): CartItem
    {
        return CartItem::query()
            ->where('cart_id', $cart->id)
            ->where('furniture_id', $furnitureId)
            ->firstOrFail();
    }

    private function cartIdForUser(int $userId): ?int
    {
        $cartId = Cart::query()->where('user_id', $userId)->value('id');

        return $cartId === null ? null : (int) $cartId;
    }
}
