<?php

namespace App\Models;

use Database\Factories\CartItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $cart_id
 * @property int $furniture_id
 * @property int $quantity
 * @property string $price_amount
 * @property string $price_currency
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Cart $cart
 * @property-read Furniture $furniture
 */
#[Fillable(['cart_id', 'furniture_id', 'quantity', 'price_amount', 'price_currency'])]
class CartItem extends Model
{
    /** @use HasFactory<CartItemFactory> */
    use HasFactory;

    /**
     * Get the cart that this item belongs to.
     *
     * @return BelongsTo<Cart, $this>
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Get the furniture that this cart item refers to.
     *
     * @return BelongsTo<Furniture, $this>
     */
    public function furniture(): BelongsTo
    {
        return $this->belongsTo(Furniture::class, 'furniture_id', 'furniture_id');
    }
}
