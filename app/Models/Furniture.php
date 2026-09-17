<?php

namespace App\Models;

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
 * @property-read Collection<int, Favorite> $favorites
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
    use HasFactory;

    protected $primaryKey = 'furniture_id';

    public function category(): BelongsTo
    {
        return $this->belongsTo(FurnitureCategory::class, 'category_id');
    }

    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(FurnitureManufacturer::class, 'manufacturer_id');
    }

    public function specification(): BelongsTo
    {
        return $this->belongsTo(FurnitureSpecification::class, 'specifications_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(FurnitureImage::class, 'furniture_id', 'furniture_id');
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class, 'furniture_id', 'furniture_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'furniture_id', 'furniture_id');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class, 'furniture_id', 'furniture_id');
    }
}
