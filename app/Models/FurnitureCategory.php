<?php

namespace App\Models;

use Database\Factories\FurnitureCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $category_id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Furniture> $furniture
 */
#[Fillable(['name'])]
class FurnitureCategory extends Model
{
    /** @use HasFactory<FurnitureCategoryFactory> */
    use HasFactory;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'category_id';

    /**
     * Get all furniture in this category.
     *
     * @return HasMany<Furniture, $this>
     */
    public function furniture(): HasMany
    {
        return $this->hasMany(Furniture::class, 'category_id');
    }
}
