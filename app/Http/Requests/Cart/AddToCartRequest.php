<?php

namespace App\Http\Requests\Cart;

use App\Models\Furniture;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'furniture_id' => [
                'required',
                'integer',
                Rule::exists(Furniture::class, 'furniture_id'),
            ],
            'quantity' => ['sometimes', 'integer', 'min:1', 'max:99'],
        ];
    }
}
