<?php

namespace App\Http\Requests\Cart;

use App\Models\Furniture;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * The storefront omits the quantity and means one unit. Resolving it here
     * lets the stock rule run even when the field is absent.
     */
    protected function prepareForValidation(): void
    {
        if (! $this->has('quantity')) {
            $this->merge(['quantity' => 1]);
        }
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
            'quantity' => [
                'integer',
                'min:1',
                $this->withinStock(),
            ],
        ];
    }

    /**
     * The cart can never hold more units than the product has on hand.
     *
     * Skipped when the furniture id itself is invalid, so a bad id reports only
     * the `furniture_id` error instead of a misleading stock error.
     */
    private function withinStock(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $furniture = Furniture::query()
                ->find($this->integer('furniture_id'));

            if (! $furniture instanceof Furniture || ! is_numeric($value)) {
                return;
            }

            $stock = max(0, (int) $furniture->quantity);

            if ((int) $value > $stock) {
                $fail($stock === 0
                    ? 'Товара нет в наличии.'
                    : "В наличии только {$stock} шт.");
            }
        };
    }
}
