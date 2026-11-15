<?php

namespace Database\Factories;

use App\Models\PickupPoint;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PickupPoint>
 */
class PickupPointFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'is_active' => true,
            'sort' => 0,
            'name' => 'Пункт выдачи «'.fake()->company().'»',
            'address' => 'Москва, '.fake()->streetAddress(),
            'phone' => '+7 495 000-00-00',
            'email' => fake()->safeEmail(),
            'opening_hours' => "Пн–Пт: 10:00–20:00\nСб–Вс: 10:00–18:00",
            'description' => fake()->sentence(),
            'latitude' => fake()->latitude(55.6, 55.9),
            'longitude' => fake()->longitude(37.2, 38.0),
        ];
    }
}
