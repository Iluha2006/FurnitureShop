<?php

namespace Database\Seeders;

use App\Models\PickupPoint;
use Illuminate\Database\Seeder;

class PickupPointSeeder extends Seeder
{
    /**
     * Seed pickup points with Moscow geodata for the Yandex Maps picker.
     */
    public function run(): void
    {
        $points = [
            [
                'name' => 'Шоурум «МебельДом» — Тверская',
                'address' => 'Москва, ул. Тверская, д. 9, строение 1',
                'latitude' => 55.7613840,
                'longitude' => 37.6112820,
                'sort' => 1,
            ],
            [
                'name' => 'Пункт выдачи — Арбат',
                'address' => 'Москва, ул. Арбат, д. 24',
                'latitude' => 55.7473800,
                'longitude' => 37.5898420,
                'sort' => 2,
            ],
            [
                'name' => 'Пункт выдачи — Хороший Выбор, МЕГА Химки',
                'address' => 'Химки, Ленинградское ш., 5, ТРЦ МЕГА',
                'latitude' => 55.8893010,
                'longitude' => 37.4153630,
                'sort' => 3,
            ],
            [
                'name' => 'Пункт выдачи — Тёплый Стан',
                'address' => 'Москва, ул. Профсоюзная, д. 128А',
                'latitude' => 55.6302030,
                'longitude' => 37.5015030,
                'sort' => 4,
            ],
            [
                'name' => 'Шоурум «МебельДом» — Сокол',
                'address' => 'Москва, Ленинградский пр-т, д. 75, корп. 8',
                'latitude' => 55.8014810,
                'longitude' => 37.5158150,
                'sort' => 5,
            ],
            [
                'name' => 'Пункт выдачи — Печатники',
                'address' => 'Москва, ул. Гурьянова, д. 43',
                'latitude' => 55.6832810,
                'longitude' => 37.7366440,
                'sort' => 6,
            ],
        ];

        foreach ($points as $point) {
            PickupPoint::query()->updateOrCreate(
                ['name' => $point['name']],
                [
                    'is_active' => true,
                    'sort' => $point['sort'],
                    'name' => $point['name'],
                    'address' => $point['address'],
                    'phone' => '+7 495 640-40-40',
                    'email' => 'pickup@mebeldom.ru',
                    'opening_hours' => "Пн–Пт: 10:00–20:00\nСб–Вс: 10:00–18:00",
                    'description' => 'Подъём на этаж, парковка рядом, выдача по номеру заказа.',
                    'latitude' => $point['latitude'],
                    'longitude' => $point['longitude'],
                ],
            );
        }
    }
}
