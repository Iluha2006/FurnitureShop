<?php

namespace App\Concerns;

use App\Models\Furniture;
use App\Models\FurnitureCategory;
use App\Models\FurnitureManufacturer;
use App\Models\FurnitureSpecification;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait FurnitureValidationRules
{
    /**
     * Get the validation rules used to validate furniture.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function furnitureRules(bool $partial = false): array
    {
        $required = $partial ? ['sometimes', 'required'] : ['required'];

        return [
            'name' => [...$required, 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'color' => ['nullable', 'string', 'max:255'],
            'price' => [...$required, 'numeric', 'between:0,9999999999.99'],
            'quantity' => [...$required, 'integer', 'min:0'],
            'manufacturer_id' => ['nullable', 'integer', Rule::exists(FurnitureManufacturer::class, 'manufacturer_id')],
            'category_id' => [...$required, 'integer', Rule::exists(FurnitureCategory::class, 'category_id')],
            'specifications_id' => ['nullable', 'integer', Rule::exists(FurnitureSpecification::class, 'id')],
        ];
    }

    /**
     * Get the validation rules used to validate furniture categories.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function furnitureCategoryRules(bool $partial = false): array
    {
        $required = $partial ? ['sometimes', 'required'] : ['required'];

        return [
            'name' => [...$required, 'string', 'max:255'],
        ];
    }

    /**
     * Get the validation rules used to validate furniture manufacturers.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function furnitureManufacturerRules(bool $partial = false, ?int $excludeId = null): array
    {
        $required = $partial ? ['sometimes', 'required'] : ['required'];

        return [
            'name' => [...$required, 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('furniture_manufacturers', 'slug')->ignore($excludeId),
            ],
        ];
    }

    /**
     * Get the validation rules used to validate furniture images.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function furnitureImageRules(bool $partial = false): array
    {
        $required = $partial ? ['sometimes', 'required'] : ['required'];

        return [
            'furniture_id' => [...$required, 'integer', Rule::exists(Furniture::class, 'furniture_id')],
            'path_image' => [...$required, 'string', 'max:2048'],
            'is_main' => ['boolean'],
        ];
    }

    /**
     * Get the validation rules used to validate furniture specifications.
     *
     * @return array<string, array<int, ValidationRule|array<mixed>|string>>
     */
    protected function furnitureSpecificationRules(): array
    {
        return [
            'width_cm' => ['nullable', 'numeric', 'between:0,999999.99'],
            'length_cm' => ['nullable', 'numeric', 'between:0,999999.99'],
            'height_cm' => ['nullable', 'numeric', 'between:0,999999.99'],
            'folding_type' => ['nullable', 'string', 'max:255'],
            'insert_type' => ['nullable', 'string', 'max:255'],
            'materials' => ['nullable', 'string', 'max:255'],
            'surface' => ['nullable', 'string', 'max:255'],
            'weight_kg' => ['nullable', 'numeric', 'between:0,999999.99'],
            'package_volume_m3' => ['nullable', 'numeric', 'between:0,99999.999'],
            'warranty' => ['nullable', 'string', 'max:255'],
        ];
    }
}
