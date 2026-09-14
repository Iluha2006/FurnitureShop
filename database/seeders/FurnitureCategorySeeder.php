<?php

namespace Database\Seeders;

use App\Models\FurnitureCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FurnitureCategorySeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * The furniture category names.
     *
     * @var array<int, string>
     */
    private const CATEGORIES = [
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
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::CATEGORIES as $name) {
            FurnitureCategory::firstOrCreate(['name' => $name]);
        }
    }
}
