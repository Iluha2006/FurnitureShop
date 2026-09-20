<?php

namespace App\Models;

use App\Search\Searchable;
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
class Furniture extends Model
{
    use HasFactory;
    use Searchable;

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
