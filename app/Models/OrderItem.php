<?php

namespace App\Models;

use Database\Factories\OrderItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $order_id
 * @property int $furniture_id
 * @property int $quantity
 * @property string $price_amount
 * @property string $price_currency
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Order $order
 * @property-read Furniture $furniture
 */
#[Fillable(['order_id', 'furniture_id', 'quantity', 'price_amount', 'price_currency'])]
class OrderItem extends Model
{
    /** @use HasFactory<OrderItemFactory> */
    use HasFactory;

    /**
     * Get the order that this item belongs to.
     *
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the furniture that this order item refers to.
     *
     * @return BelongsTo<Furniture, $this>
     */
    public function furniture(): BelongsTo
    {
        return $this->belongsTo(Furniture::class, 'furniture_id', 'furniture_id');
    }
}
