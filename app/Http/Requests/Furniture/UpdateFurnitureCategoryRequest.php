<?php

namespace App\Http\Requests\Furniture;

use App\Concerns\FurnitureValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFurnitureCategoryRequest extends FormRequest
{
    use FurnitureValidationRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return $this->furnitureCategoryRules(partial: true);
    }
}
