<?php

namespace Database\Factories;

use App\Models\FurnitureSpecification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FurnitureSpecification>
 */
class FurnitureSpecificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'width_cm' => fake()->randomFloat(1, 40, 240),
            'length_cm' => fake()->randomFloat(1, 40, 240),
            'height_cm' => fake()->randomFloat(1, 20, 220),
            'folding_type' => fake()->optional()->randomElement(['Книжка', 'Еврокнижка', 'Аккордеон', 'Дельфин']),
            'insert_type' => fake()->optional()->randomElement(['Стекло', 'Зеркало', 'ЛДСП', 'МДФ']),
            'materials' => fake()->optional()->randomElement(['ДСП', 'МДФ', 'Массив дерева', 'Металл']),
            'surface' => fake()->optional()->randomElement(['Лакированная', 'Матовый лак', 'Эмаль', 'Шпон']),
            'weight_kg' => fake()->randomFloat(1, 2, 150),
            'package_volume_m3' => fake()->randomFloat(3, 0.05, 2.5),
            'warranty' => fake()->optional()->randomElement(['12 месяцев', '24 месяца', '36 месяцев']),
        ];
    }
}
