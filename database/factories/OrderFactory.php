<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'customer_name' => fake()->name(),
            'customer_phone' => '+7 900 000-00-00',
            'customer_email' => fake()->safeEmail(),
            'delivery_address' => fake()->address(),
            'comment' => null,
            'status' => OrderStatus::Pending,
            'total_amount' => fake()->randomFloat(2, 1000, 500000),
            'total_currency' => 'RUB',
        ];
    }
}
