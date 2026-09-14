<?php

namespace Database\Factories;

use App\Models\Furniture;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'furniture_id' => Furniture::factory(),
            'quantity' => fake()->numberBetween(1, 5),
            'price_amount' => fake()->randomFloat(2, 1000, 250000),
            'price_currency' => 'RUB',
        ];
    }
}
