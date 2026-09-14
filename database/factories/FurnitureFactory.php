<?php

namespace Database\Factories;

use App\Models\Furniture;
use App\Models\FurnitureCategory;
use App\Models\FurnitureManufacturer;
use App\Models\FurnitureSpecification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Furniture>
 */
class FurnitureFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'description' => fake()->optional()->paragraph(),
            'color' => fake()->optional()->safeColorName(),
            'price' => fake()->randomFloat(2, 1000, 250000),
            'quantity' => fake()->numberBetween(0, 100),
            'manufacturer_id' => FurnitureManufacturer::factory(),
            'category_id' => FurnitureCategory::factory(),
            'specifications_id' => FurnitureSpecification::factory(),
        ];
    }
}
