<?php

namespace App\Models;

use Database\Factories\FurnitureFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $furniture_id
 * @property string $name
 * @property string|null $description
 * @property string|null $color
 * @property string $price
 * @property int $quantity
 * @property int|null $manufacturer_id
 * @property int $category_id
 * @property int|null $specifications_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read FurnitureCategory|null $category
 * @property-read FurnitureManufacturer|null $manufacturer
 * @property-read FurnitureSpecification|null $specification
 * @property-read Collection<int, FurnitureImage> $images
 * @property-read Collection<int, CartItem> $cartItems
 * @property-read Collection<int, OrderItem> $orderItems
 */
#[Fillable([
    'name',
    'description',
    'color',
    'price',
    'quantity',
    'manufacturer_id',
    'category_id',
    'specifications_id',
])]
class Furniture extends Model
{
    /** @use HasFactory<FurnitureFactory> */
    use HasFactory;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'furniture_id';

    /**
     * Get the category that this furniture belongs to.
     *
     * @return BelongsTo<FurnitureCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(FurnitureCategory::class, 'category_id');
    }

    /**
     * Get the manufacturer of this furniture.
     *
     * @return BelongsTo<FurnitureManufacturer, $this>
     */
    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(FurnitureManufacturer::class, 'manufacturer_id');
    }

    /**
     * Get the additional specifications for this furniture.
     *
     * @return BelongsTo<FurnitureSpecification, $this>
     */
    public function specification(): BelongsTo
    {
        return $this->belongsTo(FurnitureSpecification::class, 'specifications_id');
    }

    /**
     * Get all images for this furniture.
     *
     * @return HasMany<FurnitureImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(FurnitureImage::class, 'furniture_id', 'furniture_id');
    }

    /**
     * Get all cart items that contain this furniture.
     *
     * @return HasMany<CartItem, $this>
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class, 'furniture_id', 'furniture_id');
    }

    /**
     * Get all order items that contain this furniture.
     *
     * @return HasMany<OrderItem, $this>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'furniture_id', 'furniture_id');
    }
}
