<?php

namespace App\Models;

use Database\Factories\FurnitureSpecificationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property float|null $width_cm
 * @property float|null $length_cm
 * @property float|null $height_cm
 * @property string|null $folding_type
 * @property string|null $insert_type
 * @property string|null $materials
 * @property string|null $surface
 * @property float|null $weight_kg
 * @property float|null $package_volume_m3
 * @property string|null $warranty
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Furniture|null $furniture
 */
#[Fillable([
    'width_cm',
    'length_cm',
    'height_cm',
    'folding_type',
    'insert_type',
    'materials',
    'surface',
    'weight_kg',
    'package_volume_m3',
    'warranty',
])]
class FurnitureSpecification extends Model
{
    /** @use HasFactory<FurnitureSpecificationFactory> */
    use HasFactory;

    /**
     * Get the furniture this specification belongs to.
     *
     * @return HasOne<Furniture, $this>
     */
    public function furniture(): HasOne
    {
        return $this->hasOne(Furniture::class, 'specifications_id');
    }
}
