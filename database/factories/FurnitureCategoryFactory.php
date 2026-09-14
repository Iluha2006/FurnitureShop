<?php

namespace Database\Factories;

use App\Models\FurnitureCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FurnitureCategory>
 */
class FurnitureCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'Комоды и тумбы',
                'Мебель для детской комнаты',
                'Мебель для кухни',
                'Мебель для офиса',
                'Мебель для ванной комнаты',
                'Мебель для спальни',
                'Стеллажи',
                'Прихожие',
                'Стенки для гостиной',
                'Столы',
                'Шкафы-купе',
                'Раздвижные двери',
                'Товары для дома',
                'Матрасы',
                'Диваны',
                'Кресла',
                'Пуфы',
            ]),
        ];
    }
}
