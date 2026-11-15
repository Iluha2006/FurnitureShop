<?php

namespace App\Repositories;

use App\Data\OrderData;
use App\Enums\Currency;
use App\Enums\OrderStatus;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Furniture;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderRepository extends BaseRepository
{
    public function __construct(Order $order)
    {
        parent::__construct($order);
    }

    public function forUser(int $userId): Collection
    {
        return $this->query()
            ->with(['items.furniture.images'])
            ->where('user_id', $userId)
            ->latest('id')
            ->get();
    }

    public function place(
        int $userId,
        string $customerName,
        string $customerPhone,
        string $customerEmail,
        string $deliveryAddress,
        string $deliveryMethod = 'delivery',
        ?string $pickupPointId = null,
        ?string $comment = null,
    ): Order {
        return DB::transaction(function () use (
            $userId,
            $customerName,
            $customerPhone,
            $customerEmail,
            $deliveryAddress,
            $deliveryMethod,
            $pickupPointId,
            $comment,
        ): Order {
            $cart = Cart::query()->where('user_id', $userId)->first();

            if (! $cart instanceof Cart) {
                throw ValidationException::withMessages([
                    'cart' => 'Корзина пуста.',
                ]);
            }

            $items = CartItem::query()
                ->where('cart_id', $cart->id)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => 'Корзина пуста.',
                ]);
            }

            $furniture = Furniture::query()
                ->whereIn('furniture_id', $items->pluck('furniture_id')->all())
                ->lockForUpdate()
                ->get()
                ->keyBy('furniture_id');

            $this->ensureStockIsAvailable($items, $furniture);

            $firstItem = $items->first();
            $currency = Currency::fromCode($firstItem->price_currency);
            $total = round(
                $items->sum(fn (CartItem $item) => (float) $item->price_amount * $item->quantity),
                2,
            );

            $order = Order::query()->create([
                'user_id' => $userId,
                'customer_name' => $customerName,
                'customer_phone' => $customerPhone,
                'customer_email' => $customerEmail,
                'delivery_method' => $deliveryMethod,
                'pickup_point_id' => $pickupPointId,
                'delivery_address' => $deliveryAddress,
                'comment' => $comment,
                'status' => OrderStatus::Pending,
                'total_amount' => number_format($total, 2, '.', ''),
                'total_currency' => $currency->value,
            ]);

            foreach ($items as $item) {
                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'furniture_id' => $item->furniture_id,
                    'quantity' => $item->quantity,
                    'price_amount' => $item->price_amount,
                    'price_currency' => $item->price_currency,
                ]);

                $furniture->get($item->furniture_id)?->decrement('quantity', $item->quantity);
            }

            CartItem::query()->where('cart_id', $cart->id)->delete();

            return $order->load(['items.furniture.images']);
        });
    }

    public function buildForUser(int $userId): array
    {
        return OrderData::fromCollection($this->forUser($userId));
    }

    private function ensureStockIsAvailable(Collection $items, Collection $furniture): void
    {
        foreach ($items as $item) {
            $product = $furniture->get($item->furniture_id);

            if (! $product instanceof Furniture) {
                throw ValidationException::withMessages([
                    'cart' => 'Один из товаров больше недоступен.',
                ]);
            }

            $stock = max(0, (int) $product->quantity);

            if ($item->quantity > $stock) {
                throw ValidationException::withMessages([
                    'cart' => $stock === 0
                        ? "Товара «{$product->name}» нет в наличии."
                        : "Товара «{$product->name}» осталось только {$stock} шт.",
                ]);
            }
        }
    }
}
