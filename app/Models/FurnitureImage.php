<?php

namespace App\Models;

use Database\Factories\FurnitureImageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $images_id
 * @property int $furniture_id
 * @property string $path_image
 * @property bool $is_main
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Furniture $furniture
 */
#[Fillable(['furniture_id', 'path_image', 'is_main'])]
class FurnitureImage extends Model
{
    /** @use HasFactory<FurnitureImageFactory> */
    use HasFactory;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'images_id';

    /**
     * Get the furniture that this image belongs to.
     *
     * @return BelongsTo<Furniture, $this>
     */
    public function furniture(): BelongsTo
    {
        return $this->belongsTo(Furniture::class, 'furniture_id', 'furniture_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_main' => 'boolean',
        ];
    }
}
