<?php

namespace App\Http\Requests\Furniture;

use App\Concerns\FurnitureValidationRules;
use App\Models\FurnitureManufacturer;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFurnitureManufacturerRequest extends FormRequest
{
    use FurnitureValidationRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $manufacturer = $this->route('manufacturer');

        abort_if(! $manufacturer instanceof FurnitureManufacturer, 404);

        return $this->furnitureManufacturerRules(
            partial: true,
            excludeId: $manufacturer->manufacturer_id,
        );
    }
}
