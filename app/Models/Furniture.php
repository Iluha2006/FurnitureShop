<?php

namespace App\Models;

use App\Search\Indexable;
use App\Search\Searchable;
use Database\Factories\FurnitureFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
class Furniture extends Model implements Indexable
{
    /** @use HasFactory<FurnitureFactory> */
    use HasFactory;

    use Searchable;

    protected $primaryKey = 'furniture_id';

    /**
     * Get the category this furniture belongs to.
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
     * Get the specifications of this furniture.
     *
     * @return BelongsTo<FurnitureSpecification, $this>
     */
    public function specification(): BelongsTo
    {
        return $this->belongsTo(FurnitureSpecification::class, 'specifications_id');
    }

    /**
     * Get the photos of this furniture.
     *
     * @return HasMany<FurnitureImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(FurnitureImage::class, 'furniture_id', 'furniture_id');
    }

    /**
     * Get the cart lines referencing this furniture.
     *
     * @return HasMany<CartItem, $this>
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class, 'furniture_id', 'furniture_id');
    }

    /**
     * Get the order lines referencing this furniture.
     *
     * @return HasMany<OrderItem, $this>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'furniture_id', 'furniture_id');
    }

    /**
     * Get the users who added this furniture to their favourites.
     *
     * @return HasMany<Favorite, $this>
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class, 'furniture_id', 'furniture_id');
    }

    /**
     * Flatten the model for the Elasticsearch document body.
     *
     * @return array<string, mixed>
     */
    public function toSearchArray(): array
    {
        return [
            ...$this->toArray(),
            'category_name' => $this->category?->name,
        ];
    }
}
