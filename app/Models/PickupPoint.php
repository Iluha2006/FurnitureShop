<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PickupPointFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PickupPoint extends Model
{
    /** @use HasFactory<PickupPointFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'is_active',
        'sort',
        'name',
        'address',
        'phone',
        'email',
        'opening_hours',
        'description',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_active' => 'boolean',
            'sort' => 'integer',
        ];
    }
}
