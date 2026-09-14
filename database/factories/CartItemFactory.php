<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Furniture;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CartItem>
 */
class CartItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cart_id' => Cart::factory(),
            'furniture_id' => Furniture::factory(),
            'quantity' => fake()->numberBetween(1, 5),
            'price_amount' => fake()->randomFloat(2, 1000, 250000),
            'price_currency' => 'RUB',
        ];
    }
}
