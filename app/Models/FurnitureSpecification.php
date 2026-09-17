<?php

namespace App\Models;

use Database\Factories\FurnitureSpecificationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
