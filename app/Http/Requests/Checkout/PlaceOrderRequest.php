<?php

namespace App\Http\Requests\Checkout;

use Illuminate\Foundation\Http\FormRequest;

class PlaceOrderRequest extends FormRequest
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
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => [
                'required',
                'string',
                'max:30',
                'regex:/^\+?[0-9\s\-()]{10,25}$/',
            ],
            'customer_email' => ['required', 'email', 'max:255'],
            'delivery_method' => ['nullable', 'string', 'in:delivery,pickup'],
            'pickup_point_id' => [
                'nullable',
                'uuid',
                'exists:pickup_points,id',
                'required_if:delivery_method,pickup',
            ],
            'delivery_address' => [
                'nullable',
                'string',
                'max:500',
                'required_unless:delivery_method,pickup',
            ],
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'customer_name.required' => 'Укажите имя получателя.',
            'customer_phone.required' => 'Укажите телефон для связи.',
            'customer_phone.regex' => 'Телефон должен содержать только цифры, пробелы, скобки и дефисы.',
            'customer_email.required' => 'Укажите email.',
            'customer_email.email' => 'Укажите корректный email.',
            'delivery_method.in' => 'Выберите способ получения товара.',
            'pickup_point_id.required' => 'Выберите пункт выдачи на карте или в списке.',
            'pickup_point_id.exists' => 'Выбранный пункт выдачи недоступен.',
            'delivery_address.required' => 'Укажите адрес доставки.',
            'comment.max' => 'Комментарий не должен превышать 1000 символов.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'customer_name' => 'имя получателя',
            'customer_phone' => 'телефон',
            'customer_email' => 'email',
            'delivery_method' => 'способ получения',
            'pickup_point_id' => 'пункт выдачи',
            'delivery_address' => 'адрес доставки',
            'comment' => 'комментарий',
        ];
    }
}
