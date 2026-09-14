<?php

namespace App\Models;

use Database\Factories\FurnitureManufacturerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $manufacturer_id
 * @property string $name
 * @property string|null $slug
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Furniture> $furniture
 */
#[Fillable(['name', 'slug'])]
class FurnitureManufacturer extends Model
{
    /** @use HasFactory<FurnitureManufacturerFactory> */
    use HasFactory;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'manufacturer_id';

    /**
     * Get all furniture produced by this manufacturer.
     *
     * @return HasMany<Furniture, $this>
     */
    public function furniture(): HasMany
    {
        return $this->hasMany(Furniture::class, 'manufacturer_id');
    }
}
