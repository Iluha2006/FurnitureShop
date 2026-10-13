<?php

namespace Database\Seeders;

use App\Models\Furniture;
use App\Models\FurnitureCategory;
use App\Models\FurnitureImage;
use App\Models\FurnitureManufacturer;
use App\Models\FurnitureSpecification;
use App\Support\FurnitureSvgRenderer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class FurnitureSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Consecutive failed Openverse requests before the API is skipped for the
     * rest of the run and the SVG fallback is used instead. Keeps seeding fast
     * when Openverse is rate-limiting the network (HTTP 429).
     */
    private const OPENVERSE_FAILURE_LIMIT = 6;

    /**
     * Number of consecutive failed Openverse requests so far.
     */
    private int $openverseFailures = 0;

    /**
     * Whether Openverse is still considered usable in this run.
     */
    private bool $openverseAvailable = true;

    /**
     * English furniture type keywords mapped by furniture category.
     *
     * @var array<string, string>
     */
    private const CATEGORY_EN = [
        'Комоды и тумбы' => 'chest of drawers, elegant dresser',
        'Мебель для детской комнаты' => 'kids room furniture, children bedroom furniture',
        'Мебель для кухни' => 'kitchen furniture, kitchen cabinet set',
        'Мебель для офиса' => 'office furniture, executive furniture',
        'Мебель для ванной комнаты' => 'bathroom furniture, waterproof vanity unit',
        'Мебель для спальни' => 'bedroom furniture',
        'Стеллажи' => 'bookcase, shelving unit, bookshelf',
        'Прихожие' => 'hallway furniture, entryway console with coat rack',
        'Стенки для гостиной' => 'living room wall unit, media center cabinet',
        'Столы' => 'table',
        'Шкафы-купе' => 'sliding door wardrobe',
        'Раздвижные двери' => 'sliding glass door',
        'Товары для дома' => 'home furniture, home accessory',
        'Матрасы' => 'mattress',
        'Диваны' => 'sofa, couch',
        'Кресла' => 'armchair, cozy chair',
        'Пуфы' => 'ottoman, pouf, accent stool',
    ];

    /**
     * English color names mapped from Russian.
     *
     * @var array<string, string>
     */
    private const COLOR_EN = [
        'Белый' => 'white',
        'Бежевый' => 'beige',
        'Венге' => 'wenge dark brown',
        'Дуб сонома' => 'sonoma oak wood',
        'Графит' => 'graphite gray',
        'Орех' => 'walnut wood',
        'Дуб' => 'oak wood',
        'Серый' => 'gray',
        'Чёрный' => 'black',
        'Зелёный' => 'green',
        'Синий' => 'blue',
        'Розовый' => 'pink',
        'Коричневый' => 'brown',
    ];

    /**
     * English material names mapped from Russian.
     *
     * @var array<string, string>
     */
    private const MATERIAL_EN = [
        'ЛДСП' => 'laminated chipboard',
        'МДФ' => 'MDF board',
        'Массив сосны' => 'solid pine wood',
        'Массив бука' => 'solid beech wood',
        'Массив берёзы' => 'solid birch wood',
        'Влагостойкий МДФ' => 'moisture-resistant MDF',
        'Экокожа' => 'eco-leather',
        'Велюр' => 'velvet fabric',
        'Рогожка' => 'linen weave fabric',
        'Флок' => 'flock fabric',
        'Шенилл' => 'chenille fabric',
        'Латекс, хлопок' => 'latex and cotton',
        'Войлок, ППУ' => 'felt and polyurethane foam',
        'Кокосовая койра' => 'coconut coir',
        'Пена Memory' => 'memory foam',
        'Вельвет, ППУ' => 'velvet and polyurethane foam',
        'Экокожа, фанера' => 'eco-leather and plywood',
        'Рогожка, ППУ' => 'linen fabric and polyurethane foam',
        'Металл' => 'steel metal',
        'Алюминий' => 'aluminum',
        'Стекло' => 'glass',
    ];

    /**
     * The image placeholder colors mapped to furniture color names.
     *
     * @var array<string, string>
     */
    private const COLOR_HEX = [
        'Белый' => '#ece6dc',
        'Бежевый' => '#d9c9a8',
        'Венге' => '#4a3a34',
        'Дуб сонома' => '#c4a47c',
        'Графит' => '#5a5f66',
        'Орех' => '#8a6a4f',
        'Дуб' => '#b98d5f',
        'Серый' => '#9aa0a6',
        'Чёрный' => '#2e2e2e',
        'Зелёный' => '#7f9f7a',
        'Синий' => '#6689a8',
        'Розовый' => '#d9a4a8',
        'Коричневый' => '#6f5234',
    ];

    /**
     * How many photos are generated for every furniture item. The gallery on
     * the product page uses all of them.
     */
    private const IMAGE_COUNT = 5;

    /**
     * Words that indicate a photograph with people in it. Matched case-insensitively
     * on whole words against image titles and tags.
     */
    private const PERSON_PATTERN = '/\b(people|person|man|men|woman|women|girl|boy|baby|friend|family|child|children|kid|kids|couple|guy|human|portrait)\b/';

    /**
     * Stop words that are useless when scoring image candidates.
     *
     * @var string[]
     */
    private const STOP_WORDS = ['of', 'the', 'a', 'an', 'and', 'with', 'for', 'in', 'on', 'at'];

    /**
     * Image URLs already assigned to another furniture item during this run.
     *
     * @var array<string, true>
     */
    private array $usedImageUrls = [];

    /**
     * The 70 furniture items to seed.
     *
     * @var array<int, array<string, mixed>>
     */
    private const ITEMS = [
        // Комоды и тумбы
        [
            'name' => 'Комод Мальта 5 ящиков',
            'description' => 'Компактный комод с пятью выдвижными ящиками на металлических направляющих. Подойдёт для спальни или гостиной.',
            'color' => 'Белый',
            'price' => 8900.00,
            'quantity' => 12,
            'category' => 'Комоды и тумбы',
            'manufacturer' => 'Шатура',
            'images' => 2,
            'spec' => ['width_cm' => 80, 'length_cm' => 40, 'height_cm' => 90, 'materials' => 'ЛДСП', 'surface' => 'Лакированная', 'weight_kg' => 35, 'package_volume_m3' => 0.28, 'warranty' => '12 месяцев'],
        ],
        [
            'name' => 'Комод Грация с зеркалом',
            'description' => 'Изящный комод с распашным зеркалом и тремя просторными ящиками. Хромированные ручки в комплекте.',
            'color' => 'Бежевый',
            'price' => 12400.00,
            'quantity' => 8,
            'category' => 'Комоды и тумбы',
            'manufacturer' => 'Ангстрем',
            'images' => 3,
            'spec' => ['width_cm' => 90, 'length_cm' => 45, 'height_cm' => 75, 'materials' => 'МДФ', 'surface' => 'Матовый лак', 'weight_kg' => 42, 'package_volume_m3' => 0.32, 'warranty' => '24 месяца'],
        ],
        [
            'name' => 'Тумба ТВ Верни 120',
            'description' => 'Тумба под телевизор с двумя выдвижными ящиками и открытой нишей для техники. Отверстия для проводов сзади.',
            'color' => 'Венге',
            'price' => 7600.00,
            'quantity' => 20,
            'category' => 'Комоды и тумбы',
            'manufacturer' => 'Столплит',
            'images' => 2,
            'spec' => ['width_cm' => 120, 'length_cm' => 40, 'height_cm' => 50, 'materials' => 'ЛДСП', 'surface' => 'Плёнка ПВХ', 'weight_kg' => 28, 'package_volume_m3' => 0.24, 'warranty' => '12 месяцев'],
        ],
        [
            'name' => 'Тумба прикроватная Норд',
            'description' => 'Компактная прикроватная тумба с одним ящиком и открытой нишей. Хромированные ножки в комплекте.',
            'color' => 'Бежевый',
            'price' => 5200.00,
            'quantity' => 18,
            'category' => 'Комоды и тумбы',
            'manufacturer' => 'Ангстрем',
            'images' => 2,
            'spec' => ['width_cm' => 45, 'length_cm' => 40, 'height_cm' => 55, 'materials' => 'ЛДСП', 'surface' => 'Меламиновая кромка', 'weight_kg' => 14, 'package_volume_m3' => 0.09, 'warranty' => '12 месяцев'],
        ],

        // Мебель для детской комнаты
        [
            'name' => 'Кровать детская Соня 80x160',
            'description' => 'Кроватка из массива сосны с бортиками и спальным местом 80х160 см. Безопасные закруглённые углы.',
            'color' => 'Дуб сонома',
            'price' => 11800.00,
            'quantity' => 10,
            'category' => 'Мебель для детской комнаты',
            'manufacturer' => 'Много мебели',
            'images' => 3,
            'spec' => ['width_cm' => 82, 'length_cm' => 164, 'height_cm' => 90, 'materials' => 'Массив сосны', 'surface' => 'Водная эмаль', 'weight_kg' => 38, 'package_volume_m3' => 0.55, 'warranty' => '24 месяца'],
        ],
        [
            'name' => 'Стол школьника Юниор',
            'description' => 'Письменный стол с регулировкой наклона столешницы и ящиком для канцелярии. Выдерживает нагрузку до 40 кг.',
            'color' => 'Графит',
            'price' => 9800.00,
            'quantity' => 14,
            'category' => 'Мебель для детской комнаты',
            'manufacturer' => 'Лазурит',
            'images' => 2,
            'spec' => ['width_cm' => 100, 'length_cm' => 60, 'height_cm' => 72, 'materials' => 'ЛДСП', 'surface' => 'Меламиновая кромка', 'weight_kg' => 24, 'package_volume_m3' => 0.2, 'warranty' => '12 месяцев'],
        ],
        [
            'name' => 'Комод детский Мишутка',
            'description' => 'Яркий комод с четырьмя ящиками и мягкими уголками. Рисунок медвежонка не выцветает со временем.',
            'color' => 'Бежевый',
            'price' => 8600.00,
            'quantity' => 9,
            'category' => 'Мебель для детской комнаты',
            'manufacturer' => 'ТриЯ',
            'images' => 2,
            'spec' => ['width_cm' => 70, 'length_cm' => 38, 'height_cm' => 88, 'materials' => 'МДФ', 'surface' => 'Эмаль', 'weight_kg' => 30, 'package_volume_m3' => 0.26, 'warranty' => '24 месяца'],
        ],
        [
            'name' => 'Кровать детская Малыш 70x140',
            'description' => 'Кроватка 70х140 см из массива сосны с защитными бортиками и выдвижным ящиком для белья.',
            'color' => 'Дуб',
            'price' => 9400.00,
            'quantity' => 11,
            'category' => 'Мебель для детской комнаты',
            'manufacturer' => 'Много мебели',
            'images' => 3,
            'spec' => ['width_cm' => 72, 'length_cm' => 144, 'height_cm' => 88, 'materials' => 'Массив сосны', 'surface' => 'Водная эмаль', 'weight_kg' => 30, 'package_volume_m3' => 0.4, 'warranty' => '24 месяца'],
        ],

        // Мебель для кухни
        [
            'name' => 'Кухонный гарнитур Афина',
            'description' => 'Гарнитур из четырёх модулей: тумба с мойкой, шкаф с духовкой и навесные шкафы. Столешница толщиной 28 мм.',
            'color' => 'Белый',
            'price' => 34800.00,
            'quantity' => 4,
            'category' => 'Мебель для кухни',
            'manufacturer' => 'Столплит',
            'images' => 4,
            'spec' => ['width_cm' => 240, 'length_cm' => 60, 'height_cm' => 210, 'materials' => 'ЛДСП', 'surface' => 'Лакированная', 'weight_kg' => 120, 'package_volume_m3' => 1.9, 'warranty' => '24 месяца'],
        ],
        [
            'name' => 'Обеденная группа Орфей',
            'description' => 'Стол 120х80 и четыре табурета из массива бука. Покрытие термостойким лаком, устойчивость к влаге.',
            'color' => 'Орех',
            'price' => 18500.00,
            'quantity' => 6,
            'category' => 'Мебель для кухни',
            'manufacturer' => 'Дятьково',
            'images' => 3,
            'spec' => ['width_cm' => 120, 'length_cm' => 80, 'height_cm' => 75, 'materials' => 'Массив бука', 'surface' => 'Термостойкий лак', 'weight_kg' => 55, 'package_volume_m3' => 0.8, 'warranty' => '24 месяца'],
        ],
        [
            'name' => 'Шкаф навесной для кухни Лира',
            'description' => 'Навесной шкаф с двумя дверцами и выдвижной полкой. Подходит для кухонных фасадов шириной 60 см.',
            'color' => 'Белый',
            'price' => 4200.00,
            'quantity' => 18,
            'category' => 'Мебель для кухни',
            'manufacturer' => 'Лазурит',
            'images' => 2,
            'spec' => ['width_cm' => 60, 'length_cm' => 30, 'height_cm' => 72, 'materials' => 'ЛДСП', 'surface' => 'Глянец', 'weight_kg' => 18, 'package_volume_m3' => 0.13, 'warranty' => '12 месяцев'],
        ],
        [
            'name' => 'Кухонный гарнитур Виктория',
            'description' => 'Классический гарнитур из пяти модулей с фасадами под эмаль. Петли с доводчиками.',
            'color' => 'Белый',
            'price' => 41200.00,
            'quantity' => 3,
            'category' => 'Мебель для кухни',
            'manufacturer' => 'Столплит',
            'images' => 4,
            'spec' => ['width_cm' => 260, 'length_cm' => 60, 'height_cm' => 215, 'materials' => 'ЛДСП', 'surface' => 'Глянец', 'weight_kg' => 130, 'package_volume_m3' => 2.1, 'warranty' => '24 месяца'],
        ],

        // Мебель для офиса
        [
            'name' => 'Стол руководителя Атлант',
            'description' => 'Офисный стол с приставной тумбой, органайзером и кабель-каналом. Столешница толщиной 25 мм.',
            'color' => 'Венге',
            'price' => 22400.00,
            'quantity' => 5,
            'category' => 'Мебель для офиса',
            'manufacturer' => 'Ангстрем',
            'images' => 3,
            'spec' => ['width_cm' => 180, 'length_cm' => 90, 'height_cm' => 75, 'materials' => 'ЛДСП', 'surface' => 'Меламиновая кромка', 'weight_kg' => 78, 'package_volume_m3' => 1.2, 'warranty' => '24 месяца'],
        ],
        [
            'name' => 'Кресло офисное Престиж',
            'description' => 'Кресло с высокой спинкой, поясничной поддержкой и механизмом качания. Нагрузка до 150 кг.',
            'color' => 'Чёрный',
            'price' => 13400.00,
            'quantity' => 11,
            'category' => 'Мебель для офиса',
            'manufacturer' => 'Много мебели',
            'images' => 2,
            'spec' => ['width_cm' => 68, 'length_cm' => 68, 'height_cm' => 128, 'materials' => 'Экокожа', 'weight_kg' => 16, 'package_volume_m3' => 0.45, 'warranty' => '12 месяцев'],
        ],
        [
            'name' => 'Шкаф для документов Бизнес',
            'description' => 'Шкаф с четырьмя полками и двумя распашными дверцами. Ручки и врезной замок в комплекте.',
            'color' => 'Серый',
            'price' => 15600.00,
            'quantity' => 7,
            'category' => 'Мебель для офиса',
            'manufacturer' => 'Сандэр',
            'images' => 2,
            'spec' => ['width_cm' => 100, 'length_cm' => 45, 'height_cm' => 180, 'materials' => 'ЛДСП', 'surface' => 'Лакированная', 'weight_kg' => 65, 'package_volume_m3' => 0.85, 'warranty' => '24 месяца'],
        ],
        [
            'name' => 'Стол для переговоров Капитолий',
            'description' => 'Просторный офисный стол для переговоров с кабель-каналом и опорой-трапецией.',
            'color' => 'Орех',
            'price' => 24800.00,
            'quantity' => 4,
            'category' => 'Мебель для офиса',
            'manufacturer' => 'Ангстрем',
            'images' => 3,
            'spec' => ['width_cm' => 200, 'length_cm' => 100, 'height_cm' => 75, 'materials' => 'ЛДСП', 'surface' => 'Меламиновая кромка', 'weight_kg' => 85, 'package_volume_m3' => 1.5, 'warranty' => '24 месяца'],
        ],

        // Мебель для ванной комнаты
        [
            'name' => 'Тумба под раковину Волна',
            'description' => 'Влагостойкая тумба с фасадами из МДФ и выдвижным ящиком. Подходит для раковины шириной 60 см.',
            'color' => 'Белый',
            'price' => 9400.00,
            'quantity' => 13,
            'category' => 'Мебель для ванной комнаты',
            'manufacturer' => 'Миасс-Мебель',
            'images' => 2,
            'spec' => ['width_cm' => 60, 'length_cm' => 45, 'height_cm' => 85, 'materials' => 'Влагостойкий МДФ', 'surface' => 'Глянец', 'weight_kg' => 22, 'package_volume_m3' => 0.25, 'warranty' => '12 месяцев'],
        ],
        [
            'name' => 'Пенал для ванной Альба',
            'description' => 'Узкий пенал с тремя полками для хранения косметики и полотенец. Устойчив к повышенной влажности.',
            'color' => 'Графит',
            'price' => 7800.00,
            'quantity' => 6,
            'category' => 'Мебель для ванной комнаты',
            'manufacturer' => 'Миасс-Мебель',
            'images' => 2,
            'spec' => ['width_cm' => 40, 'length_cm' => 25, 'height_cm' => 160, 'materials' => 'Влагостойкий МДФ', 'surface' => 'Матовый лак', 'weight_kg' => 24, 'package_volume_m3' => 0.18, 'warranty' => '12 месяцев'],
        ],
        [
            'name' => 'Зеркало для ванной Капля',
            'description' => 'Зеркало каплевидной формы с влагостойкой рамой из МДФ. Крепёж в комплекте.',
            'color' => 'Белый',
            'price' => 4600.00,
            'quantity' => 15,
            'category' => 'Мебель для ванной комнаты',
            'manufacturer' => 'Миасс-Мебель',
            'images' => 2,
            'spec' => ['width_cm' => 50, 'length_cm' => 4, 'height_cm' => 70, 'materials' => 'Влагостойкий МДФ', 'surface' => 'Глянец', 'weight_kg' => 6, 'package_volume_m3' => 0.08, 'warranty' => '12 месяцев'],
        ],
        [
            'name' => 'Тумба для ванной Веста',
            'description' => 'Напольная тумба для ванной с двумя открытыми полками. Подходит для раковины 50 см.',
            'color' => 'Графит',
            'price' => 6800.00,
            'quantity' => 9,
            'category' => 'Мебель для ванной комнаты',
            'manufacturer' => 'Миасс-Мебель',
            'images' => 2,
            'spec' => ['width_cm' => 50, 'length_cm' => 40, 'height_cm' => 60, 'materials' => 'Влагостойкий МДФ', 'surface' => 'Матовый лак', 'weight_kg' => 16, 'package_volume_m3' => 0.12, 'warranty' => '12 месяцев'],
        ],

        // Мебель для спальни
        [
            'name' => 'Кровать двуспальная Венеция 160x200',
            'description' => 'Двуспальная кровать с высоким изголовьем на металлическом каркасе. Два ящика для белья в комплекте.',
            'color' => 'Дуб',
            'price' => 26900.00,
            'quantity' => 5,
            'category' => 'Мебель для спальни',
            'manufacturer' => 'Шатура',
            'images' => 4,
            'spec' => ['width_cm' => 170, 'length_cm' => 215, 'height_cm' => 120, 'materials' => 'МДФ', 'surface' => 'Эмаль', 'weight_kg' => 85, 'package_volume_m3' => 1.4, 'warranty' => '24 месяца'],
        ],
        [
            'name' => 'Шкаф для спальни Флоренция',
            'description' => 'Трёхдверный шкаф с антресолью, штангой и полками. Фасады из шпона дуба.',
            'color' => 'Орех',
            'price' => 35200.00,
            'quantity' => 3,
            'category' => 'Мебель для спальни',
            'manufacturer' => 'Лазурит',
            'images' => 3,
            'spec' => ['width_cm' => 180, 'length_cm' => 58, 'height_cm' => 220, 'folding_type' => null, 'insert_type' => 'Зеркало', 'materials' => 'ЛДСП', 'surface' => 'Шпон дуба', 'weight_kg' => 140, 'package_volume_m3' => 2.3, 'warranty' => '24 месяца'],
        ],
        [
            'name' => 'Прикроватная тумба Лорен',
            'description' => 'Тумба с одним ящиком и открытой нишей, ножки из массива. Комплектуется с кроватью «Венеция».',
            'color' => 'Дуб',
            'price' => 4900.00,
            'quantity' => 16,
            'category' => 'Мебель для спальни',
            'manufacturer' => 'Шатура',
            'images' => 2,
            'spec' => ['width_cm' => 45, 'length_cm' => 40, 'height_cm' => 55, 'materials' => 'ЛДСП', 'surface' => 'Эмаль', 'weight_kg' => 15, 'package_volume_m3' => 0.1, 'warranty' => '12 месяцев'],
        ],
        [
            'name' => 'Кровать односпальная Орхидея 90x200',
            'description' => 'Односпальная кровать 90х200 с мягким изголовьем и металлическими ножками.',
            'color' => 'Графит',
            'price' => 19800.00,
            'quantity' => 6,
            'category' => 'Мебель для спальни',
            'manufacturer' => 'Шатура',
            'images' => 3,
            'spec' => ['width_cm' => 104, 'length_cm' => 212, 'height_cm' => 110, 'materials' => 'МДФ', 'surface' => 'Эмаль', 'weight_kg' => 45, 'package_volume_m3' => 0.9, 'warranty' => '24 месяца'],
        ],
        [
            'name' => 'Шкаф трёхдверный Венеция',
            'description' => 'Трёхдверный шкаф для спальни с зеркальной средней дверью и антресолью.',
            'color' => 'Дуб',
            'price' => 32800.00,
            'quantity' => 4,
            'category' => 'Мебель для спальни',
            'manufacturer' => 'Лазурит',
            'images' => 3,
            'spec' => ['width_cm' => 180, 'length_cm' => 55, 'height_cm' => 230, 'insert_type' => 'Зеркало', 'materials' => 'ЛДСП', 'surface' => 'Шпон дуба', 'weight_kg' => 120, 'package_volume_m3' => 2.2, 'warranty' => '24 месяца'],
        ],

        // Стеллажи
        [
            'name' => 'Стеллаж книжный Мадрид',
            'description' => 'Стеллаж с пятью регулируемыми полками. Полка выдерживает нагрузку до 20 кг.',
            'color' => 'Дуб сонома',
            'price' => 11000.00,
            'quantity' => 9,
            'category' => 'Стеллажи',
            'manufacturer' => 'Ангстрем',
            'images' => 3,
            'spec' => ['width_cm' => 90, 'length_cm' => 35, 'height_cm' => 180, 'materials' => 'ЛДСП', 'surface' => 'Меламиновая кромка', 'weight_kg' => 48, 'package_volume_m3' => 0.6, 'warranty' => '12 месяцев'],
        ],
        [
            'name' => 'Стеллаж открытый Модерн 4 полки',
            'description' => 'Лёгкий открытый стеллаж из четырёх полок. Используется в зале, кабинете и детской.',
            'color' => 'Графит',
            'price' => 7200.00,
            'quantity' => 15,
            'category' => 'Стеллажи',
            'manufacturer' => 'Столплит',
            'images' => 2,
            'spec' => ['width_cm' => 70, 'length_cm' => 32, 'height_cm' => 140, 'materials' => 'ЛДСП', 'surface' => 'Лакированная', 'weight_kg' => 26, 'package_volume_m3' => 0.34, 'warranty' => '12 месяцев'],
        ],
        [
            'name' => 'Стеллаж угловой Венеция',
            'description' => 'Угловой стеллаж с тремя полками для экономного использования пространства комнаты.',
            'color' => 'Венге',
            'price' => 8900.00,
            'quantity' => 8,
            'category' => 'Стеллажи',
            'manufacturer' => 'Много мебели',
            'images' => 2,
            'spec' => ['width_cm' => 60, 'length_cm' => 60, 'height_cm' => 160, 'materials' => 'ЛДСП', 'surface' => 'Плёнка ПВХ', 'weight_kg' => 34, 'package_volume_m3' => 0.4, 'warranty' => '12 месяцев'],
        ],
        [
            'name' => 'Стеллаж модульный Лондон 6 полок',
            'description' => 'Модульный стеллаж на шесть полок. Секции можно комбинировать с тумбами.',
            'color' => 'Орех',
            'price' => 13200.00,
            'quantity' => 7,
            'category' => 'Стеллажи',
            'manufacturer' => 'Ангстрем',
            'images' => 2,
            'spec' => ['width_cm' => 80, 'length_cm' => 34, 'height_cm' => 200, 'materials' => 'ЛДСП', 'surface' => 'Меламиновая кромка', 'weight_kg' => 42, 'package_volume_m3' => 0.55, 'warranty' => '12 месяцев'],
        ],

        // Прихожие
        [
            'name' => 'Прихожая Камелия',
            'description' => 'Набор из навесной полки, тумбы и вешалки с крючками. Зеркало с полочкой для ключей.',
            'color' => 'Бежевый',
            'price' => 16400.00,
            'quantity' => 5,
            'category' => 'Прихожие',
            'manufacturer' => 'ТриЯ',
            'images' => 3,
            'spec' => ['width_cm' => 120, 'length_cm' => 35, 'height_cm' => 200, 'materials' => 'ЛДСП', 'surface' => 'Эмаль', 'weight_kg' => 55, 'package_volume_m3' => 0.85, 'warranty' => '24 месяца'],
        ],
        [
            'name' => 'Прихожая Эдем с зеркалом',
            'description' => 'Компактная прихожая с распашным шкафом, зеркалом и открытым сиденьем для обуви.',
            'color' => 'Орех',
            'price' => 19900.00,
            'quantity' => 4,
            'category' => 'Прихожие',
            'manufacturer' => 'Миасс-Мебель',
            'images' => 3,
            'spec' => ['width_cm' => 150, 'length_cm' => 40, 'height_cm' => 200, 'insert_type' => 'Зеркало', 'materials' => 'МДФ', 'surface' => 'Шпон ореха', 'weight_kg' => 68, 'package_volume_m3' => 1.1, 'warranty' => '24 месяца'],
        ],
        [
            'name' => 'Прихожая Венеция с сиденьем',
            'description' => 'Прихожая с открытым сиденьем, зеркалом и вешалами. Верхняя полка для сумок и головных уборов.',
            'color' => 'Венге',
            'price' => 18600.00,
            'quantity' => 5,
            'category' => 'Прихожие',
            'manufacturer' => 'ТриЯ',
            'images' => 3,
            'spec' => ['width_cm' => 130, 'length_cm' => 38, 'height_cm' => 205, 'insert_type' => 'Зеркало', 'materials' => 'МДФ', 'surface' => 'Эмаль', 'weight_kg' => 60, 'package_volume_m3' => 1.0, 'warranty' => '24 месяца'],
        ],

        // Стенки для гостиной
        [
            'name' => 'Стенка гостиная Монте-Карло',
            'description' => 'Модульная стенка для гостиной: шкаф, витрина, тумба под ТВ и полки. Сборка в один день.',
            'color' => 'Венге',
            'price' => 42800.00,
            'quantity' => 3,
            'category' => 'Стенки для гостиной',
            'manufacturer' => 'Сандэр',
            'images' => 4,
            'spec' => ['width_cm' => 260, 'length_cm' => 45, 'height_cm' => 220, 'insert_type' => 'Стекло', 'materials' => 'ЛДСП', 'surface' => 'Плёнка ПВХ', 'weight_kg' => 185, 'package_volume_m3' => 3.1, 'warranty' => '24 месяца'],
        ],
        [
            'name' => 'Стенка модульная Белла',
            'description' => 'Классическая стенка с антресолью, барной секцией и зеркальным фасадом. Роликовые ящики.',
            'color' => 'Белый',
            'price' => 31500.00,
            'quantity' => 4,
            'category' => 'Стенки для гостиной',
            'manufacturer' => 'Шатура',
            'images' => 3,
            'spec' => ['width_cm' => 220, 'length_cm' => 42, 'height_cm' => 210, 'insert_type' => 'Зеркало', 'materials' => 'МДФ', 'surface' => 'Эмаль', 'weight_kg' => 150, 'package_volume_m3' => 2.4, 'warranty' => '24 месяца'],
        ],
        [
            'name' => 'Стенка гостиная Прага',
            'description' => 'Современная стенка с открытыми полками, тумбой под ТВ и витриной под стеклом.',
            'color' => 'Дуб сонома',
            'price' => 45600.00,
            'quantity' => 3,
            'category' => 'Стенки для гостиной',
            'manufacturer' => 'Сандэр',
            'images' => 4,
            'spec' => ['width_cm' => 280, 'length_cm' => 44, 'height_cm' => 220, 'insert_type' => 'Стекло', 'materials' => 'ЛДСП', 'surface' => 'Плёнка ПВХ', 'weight_kg' => 190, 'package_volume_m3' => 3.2, 'warranty' => '24 месяца'],
        ],

        // Столы
        [
            'name' => 'Стол обеденный Классика 120x80',
            'description' => 'Обеденный стол на фигурных ножках из массива. Раскладной механизм увеличивает площадь на 40%.',
            'color' => 'Орех',
            'price' => 9600.00,
            'quantity' => 12,
            'category' => 'Столы',
            'manufacturer' => 'Дятьково',
            'images' => 3,
            'spec' => ['width_cm' => 120, 'length_cm' => 80, 'height_cm' => 75, 'materials' => 'Массив сосны', 'surface' => 'Термостойкий лак', 'weight_kg' => 42, 'package_volume_m3' => 0.65, 'warranty' => '24 месяца'],
        ],
        [
            'name' => 'Стол журнальный Прованс',
            'description' => 'Журнальный стол с открытой полкой и гнутыми ножками. Выдерживает нагрузку до 30 кг.',
            'color' => 'Белый',
            'price' => 5800.00,
            'quantity' => 17,
            'category' => 'Столы',
            'manufacturer' => 'ТриЯ',
            'images' => 2,
            'spec' => ['width_cm' => 70, 'length_cm' => 45, 'height_cm' => 45, 'materials' => 'МДФ', 'surface' => 'Матовый лак', 'weight_kg' => 12, 'package_volume_m3' => 0.15, 'warranty' => '12 месяцев'],
        ],
        [
            'name' => 'Стол компьютерный Грация',
            'description' => 'Компьютерный стол с выдвижной клавиатурной полкой, подставкой под системный блок и надстройкой.',
            'color' => 'Серый',
            'price' => 11200.00,
            'quantity' => 10,
            'category' => 'Столы',
            'manufacturer' => 'Ангстрем',
            'images' => 3,
            'spec' => ['width_cm' => 120, 'length_cm' => 60, 'height_cm' => 140, 'materials' => 'ЛДСП', 'surface' => 'Меламиновая кромка', 'weight_kg' => 38, 'package_volume_m3' => 0.5, 'warranty' => '12 месяцев'],
        ],
        [
            'name' => 'Стол раскладной Дельфин',
            'description' => 'Раскладной стол с механизмом «дельфин» и ящиком для белья. Спальное место 70х190 см.',
            'color' => 'Венге',
            'price' => 16800.00,
            'quantity' => 7,
            'category' => 'Столы',
            'manufacturer' => 'Много мебели',
            'images' => 3,
            'spec' => ['width_cm' => 140, 'length_cm' => 45, 'height_cm' => 75, 'folding_type' => 'Дельфин', 'materials' => 'ЛДСП', 'surface' => 'Плёнка ПВХ', 'weight_kg' => 72, 'package_volume_m3' => 0.9, 'warranty' => '24 месяца'],
        ],
        [
            'name' => 'Стол обеденный Монако 160x90',
            'description' => 'Большой обеденный стол 160х90 см из массива бука. Раскладывается до 240 см.',
            'color' => 'Орех',
            'price' => 17400.00,
            'quantity' => 6,
            'category' => 'Столы',
            'manufacturer' => 'Дятьково',
            'images' => 3,
            'spec' => ['width_cm' => 160, 'length_cm' => 90, 'height_cm' => 75, 'materials' => 'Массив бука', 'surface' => 'Термостойкий лак', 'weight_kg' => 58, 'package_volume_m3' => 0.9, 'warranty' => '24 месяца'],
        ],
        [
            'name' => 'Стол-трансформер Гнездо 150x80',
            'description' => 'Стол-трансформер с механизмом «книжка». В сложенном виде занимает всего 60 см.',
            'color' => 'Дуб',
            'price' => 15200.00,
            'quantity' => 8,
            'category' => 'Столы',
            'manufacturer' => 'Много мебели',
            'images' => 3,
            'spec' => ['width_cm' => 150, 'length_cm' => 80, 'height_cm' => 75, 'folding_type' => 'Книжка', 'materials' => 'Массив сосны', 'surface' => 'Термостойкий лак', 'weight_kg' => 50, 'package_volume_m3' => 0.8, 'warranty' => '24 месяца'],
        ],

        // Шкафы-купе
        [
            'name' => 'Шкаф-купе Трио с зеркалом',
            'description' => 'Трёхдверный шкаф-купе с зеркальной секцией, штангой и антресолью. Профили алюминиевые.',
            'color' => 'Дуб сонома',
            'price' => 29800.00,
            'quantity' => 5,
            'category' => 'Шкафы-купе',
            'manufacturer' => 'Лазурит',
            'images' => 4,
            'spec' => ['width_cm' => 200, 'length_cm' => 60, 'height_cm' => 240, 'insert_type' => 'Зеркало', 'materials' => 'ЛДСП', 'surface' => 'Лакированная', 'weight_kg' => 160, 'package_volume_m3' => 2.9, 'warranty' => '24 месяца'],
        ],
        [
            'name' => 'Шкаф-купе Стелла 3 двери',
            'description' => 'Шкаф-купе с тремя раздвижными дверцами, полками и ящиками. Не требует сверления пола.',
            'color' => 'Бежевый',
            'price' => 26400.00,
            'quantity' => 6,
            'category' => 'Шкафы-купе',
            'manufacturer' => 'Столплит',
            'images' => 3,
            'spec' => ['width_cm' => 180, 'length_cm' => 62, 'height_cm' => 230, 'materials' => 'ЛДСП', 'surface' => 'Плёнка ПВХ', 'weight_kg' => 145, 'package_volume_m3' => 2.6, 'warranty' => '24 месяца'],
        ],
        [
            'name' => 'Шкаф-купе угловой Милан',
            'description' => 'Угловой шкаф для эргономичного хранения вещей. Внутренние полки и штанга в комплекте.',
            'color' => 'Графит',
            'price' => 38600.00,
            'quantity' => 3,
            'category' => 'Шкафы-купе',
            'manufacturer' => 'Сандэр',
            'images' => 3,
            'spec' => ['width_cm' => 160, 'length_cm' => 160, 'height_cm' => 240, 'materials' => 'ЛДСП', 'surface' => 'Глянец', 'weight_kg' => 175, 'package_volume_m3' => 3.0, 'warranty' => '24 месяца'],
        ],
        [
            'name' => 'Шкаф-купе Прованс 2 двери',
            'description' => 'Двухдверный шкаф-купе с матовыми фасадами и внутренним зеркалом.',
            'color' => 'Белый',
            'price' => 23600.00,
            'quantity' => 5,
            'category' => 'Шкафы-купе',
            'manufacturer' => 'Столплит',
            'images' => 3,
            'spec' => ['width_cm' => 160, 'length_cm' => 58, 'height_cm' => 230, 'insert_type' => 'Зеркало', 'materials' => 'ЛДСП', 'surface' => 'Матовый лак', 'weight_kg' => 130, 'package_volume_m3' => 2.4, 'warranty' => '24 месяца'],
        ],

        // Раздвижные двери
        [
            'name' => 'Дверь раздвижная Стекло Мат',
            'description' => 'Раздвижная дверь в матовом стекле с алюминиевой рамой. Ширина проёма до 90 см.',
            'color' => 'Серый',
            'price' => 14900.00,
            'quantity' => 8,
            'category' => 'Раздвижные двери',
            'manufacturer' => 'Ангстрем',
            'images' => 2,
            'spec' => ['width_cm' => 90, 'length_cm' => 12, 'height_cm' => 210, 'insert_type' => 'Стекло матовое', 'materials' => 'Алюминий', 'surface' => 'Стекло', 'weight_kg' => 32, 'package_volume_m3' => 0.25, 'warranty' => '12 месяцев'],
        ],
        [
            'name' => 'Дверь-купе Зеркало Венеция',
            'description' => 'Раздвижная дверь с зеркальным полотном и пескоструйным рисунком. Фурнитура в комплекте.',
            'color' => 'Бежевый',
            'price' => 17200.00,
            'quantity' => 5,
            'category' => 'Раздвижные двери',
            'manufacturer' => 'Лазурит',
            'images' => 2,
            'spec' => ['width_cm' => 100, 'length_cm' => 12, 'height_cm' => 220, 'insert_type' => 'Зеркало', 'materials' => 'Алюминий', 'surface' => 'Пескоструй', 'weight_kg' => 38, 'package_volume_m3' => 0.28, 'warranty' => '12 месяцев'],
        ],
        [
            'name' => 'Дверь раздвижная Белое Дерево',
            'description' => 'Раздвижная дверь с матовым белым стеклом и алюминиевым профилем.',
            'color' => 'Дуб',
            'price' => 15800.00,
            'quantity' => 6,
            'category' => 'Раздвижные двери',
            'manufacturer' => 'Лазурит',
            'images' => 2,
            'spec' => ['width_cm' => 95, 'length_cm' => 12, 'height_cm' => 210, 'insert_type' => 'Стекло матовое', 'materials' => 'Алюминий', 'surface' => 'Стекло', 'weight_kg' => 30, 'package_volume_m3' => 0.24, 'warranty' => '12 месяцев'],
        ],

        // Товары для дома
        [
            'name' => 'Вешалка напольная Рига',
            'description' => 'Напольная вешалка с шестью крючками и полкой для головных уборов. Устойчивое основание.',
            'color' => 'Венге',
            'price' => 2400.00,
            'quantity' => 25,
            'category' => 'Товары для дома',
            'manufacturer' => 'Столплит',
            'images' => 2,
            'spec' => ['width_cm' => 60, 'length_cm' => 45, 'height_cm' => 180, 'materials' => 'Металл', 'surface' => 'Порошковая эмаль', 'weight_kg' => 6, 'package_volume_m3' => 0.1, 'warranty' => '12 месяцев'],
        ],
        [
            'name' => 'Зеркало напольное Арлекин',
            'description' => 'Напольное зеркало в раме из МДФ с регулировкой наклона. Высота полотна 160 см.',
            'color' => 'Белый',
            'price' => 6900.00,
            'quantity' => 11,
            'category' => 'Товары для дома',
            'manufacturer' => 'ТриЯ',
            'images' => 2,
            'spec' => ['width_cm' => 60, 'length_cm' => 25, 'height_cm' => 160, 'materials' => 'МДФ', 'surface' => 'Эмаль', 'weight_kg' => 14, 'package_volume_m3' => 0.12, 'warranty' => '12 месяцев'],
        ],
        [
            'name' => 'Этажерка для книг Лофт',
            'description' => 'Минималистичная этажерка из металла с четырьмя полками для книг и декора.',
            'color' => 'Чёрный',
            'price' => 5800.00,
            'quantity' => 12,
            'category' => 'Товары для дома',
            'manufacturer' => 'Столплит',
            'images' => 2,
            'spec' => ['width_cm' => 50, 'length_cm' => 30, 'height_cm' => 160, 'materials' => 'Металл', 'surface' => 'Порошковая эмаль', 'weight_kg' => 12, 'package_volume_m3' => 0.2, 'warranty' => '12 месяцев'],
        ],

        // Матрасы
        [
            'name' => 'Матрас Ортопед Люкс 160x200',
            'description' => 'Ортопедический матрас с независимым пружинным блоком и прослойкой из латекса. Средняя жёсткость.',
            'color' => 'Серый',
            'price' => 24400.00,
            'quantity' => 9,
            'category' => 'Матрасы',
            'manufacturer' => 'Дятьково',
            'images' => 3,
            'spec' => ['width_cm' => 160, 'length_cm' => 200, 'height_cm' => 24, 'materials' => 'Латекс, хлопок', 'surface' => 'Жаккард', 'weight_kg' => 32, 'package_volume_m3' => 0.77, 'warranty' => '36 месяцев'],
        ],
        [
            'name' => 'Матрас Дабл Пружинный 90x200',
            'description' => 'Матрас с зависимым пружинным блоком Bonnel и войлочной прокладкой. Подходит для спальни и дачи.',
            'color' => 'Бежевый',
            'price' => 9200.00,
            'quantity' => 15,
            'category' => 'Матрасы',
            'manufacturer' => 'Миасс-Мебель',
            'images' => 2,
            'spec' => ['width_cm' => 90, 'length_cm' => 200, 'height_cm' => 20, 'materials' => 'Войлок, ППУ', 'surface' => 'Трикотаж', 'weight_kg' => 18, 'package_volume_m3' => 0.36, 'warranty' => '12 месяцев'],
        ],
        [
            'name' => 'Матрас детский Кокос 80x160',
            'description' => 'Детский матрас с кокосовой койрой. Жёсткая сторона для малышей и средняя для школьников.',
            'color' => 'Белый',
            'price' => 8100.00,
            'quantity' => 12,
            'category' => 'Матрасы',
            'manufacturer' => 'Шатура',
            'images' => 2,
            'spec' => ['width_cm' => 80, 'length_cm' => 160, 'height_cm' => 10, 'materials' => 'Кокосовая койра', 'surface' => 'Хлопковый жаккард', 'weight_kg' => 10, 'package_volume_m3' => 0.13, 'warranty' => '24 месяца'],
        ],
        [
            'name' => 'Матрас Мемори 140x200',
            'description' => 'Матрас с эффектом памяти пены Memory Foam. Рекомендуется людям с проблемами позвоночника.',
            'color' => 'Графит',
            'price' => 19800.00,
            'quantity' => 7,
            'category' => 'Матрасы',
            'manufacturer' => 'Лазурит',
            'images' => 3,
            'spec' => ['width_cm' => 140, 'length_cm' => 200, 'height_cm' => 22, 'materials' => 'Пена Memory', 'surface' => 'Флок', 'weight_kg' => 24, 'package_volume_m3' => 0.62, 'warranty' => '24 месяца'],
        ],
        [
            'name' => 'Матрас Прованс пружинный 120x200',
            'description' => 'Матрас с пружинным блоком Bonnel и войлочной прослойкой. Комфортная жёсткость.',
            'color' => 'Бежевый',
            'price' => 11800.00,
            'quantity' => 13,
            'category' => 'Матрасы',
            'manufacturer' => 'Дятьково',
            'images' => 2,
            'spec' => ['width_cm' => 120, 'length_cm' => 200, 'height_cm' => 20, 'materials' => 'Войлок, ППУ', 'surface' => 'Трикотаж', 'weight_kg' => 20, 'package_volume_m3' => 0.48, 'warranty' => '12 месяцев'],
        ],

        // Диваны
        [
            'name' => 'Диван Модерн трёхместный',
            'description' => 'Трёхместный диван с механизмом «еврокнижка». Съёмные чехлы из износостойкой ткани.',
            'color' => 'Серый',
            'price' => 26900.00,
            'quantity' => 6,
            'category' => 'Диваны',
            'manufacturer' => 'Много мебели',
            'images' => 4,
            'spec' => ['width_cm' => 210, 'length_cm' => 95, 'height_cm' => 85, 'folding_type' => 'Еврокнижка', 'materials' => 'Велюр', 'weight_kg' => 58, 'package_volume_m3' => 1.7, 'warranty' => '24 месяца'],
        ],
        [
            'name' => 'Диван Классика с подлокотниками',
            'description' => 'Классический диван с высокими подлокотниками и механизмом «книжка». Спальное место 140х190 см.',
            'color' => 'Бежевый',
            'price' => 22900.00,
            'quantity' => 8,
            'category' => 'Диваны',
            'manufacturer' => 'Шатура',
            'images' => 3,
            'spec' => ['width_cm' => 200, 'length_cm' => 90, 'height_cm' => 95, 'folding_type' => 'Книжка', 'materials' => 'Рогожка', 'weight_kg' => 55, 'package_volume_m3' => 1.6, 'warranty' => '24 месяца'],
        ],
        [
            'name' => 'Диван-кровать Уют еврокнижка',
            'description' => 'Компактный диван-кровать с глубоким сиденьем и вместительным бельевым ящиком.',
            'color' => 'Зелёный',
            'price' => 19800.00,
            'quantity' => 10,
            'category' => 'Диваны',
            'manufacturer' => 'ТриЯ',
            'images' => 3,
            'spec' => ['width_cm' => 180, 'length_cm' => 92, 'height_cm' => 80, 'folding_type' => 'Еврокнижка', 'materials' => 'Флок', 'weight_kg' => 48, 'package_volume_m3' => 1.4, 'warranty' => '24 месяца'],
        ],
        [
            'name' => 'Диван угловой Гостиная',
            'description' => 'Угловой диван-трансформер с механизмом «дельфин» и подушками в комплекте.',
            'color' => 'Графит',
            'price' => 39400.00,
            'quantity' => 4,
            'category' => 'Диваны',
            'manufacturer' => 'Сандэр',
            'images' => 4,
            'spec' => ['width_cm' => 260, 'length_cm' => 150, 'height_cm' => 90, 'folding_type' => 'Дельфин', 'materials' => 'Велюр', 'weight_kg' => 110, 'package_volume_m3' => 3.5, 'warranty' => '24 месяца'],
        ],
        [
            'name' => 'Диван Компакт Аккордеон',
            'description' => 'Диван с механизмом «аккордеон» для небольших квартир. Спальное место 120х200 см.',
            'color' => 'Синий',
            'price' => 21800.00,
            'quantity' => 7,
            'category' => 'Диваны',
            'manufacturer' => 'Миасс-Мебель',
            'images' => 3,
            'spec' => ['width_cm' => 160, 'length_cm' => 90, 'height_cm' => 85, 'folding_type' => 'Аккордеон', 'materials' => 'Шенилл', 'weight_kg' => 45, 'package_volume_m3' => 1.3, 'warranty' => '24 месяца'],
        ],
        [
            'name' => 'Диван Монако угловой',
            'description' => 'Угловой диван с механизмом «еврокнижка» и вместительным бельевым ящиком.',
            'color' => 'Коричневый',
            'price' => 42800.00,
            'quantity' => 4,
            'category' => 'Диваны',
            'manufacturer' => 'Сандэр',
            'images' => 4,
            'spec' => ['width_cm' => 250, 'length_cm' => 150, 'height_cm' => 88, 'folding_type' => 'Еврокнижка', 'materials' => 'Велюр', 'weight_kg' => 100, 'package_volume_m3' => 3.2, 'warranty' => '24 месяца'],
        ],

        // Кресла
        [
            'name' => 'Кресло Классик для отдыха',
            'description' => 'Кресло с мягким сиденьем и откидной спинкой для чтения и отдыха.',
            'color' => 'Бежевый',
            'price' => 12900.00,
            'quantity' => 9,
            'category' => 'Кресла',
            'manufacturer' => 'Шатура',
            'images' => 3,
            'spec' => ['width_cm' => 75, 'length_cm' => 80, 'height_cm' => 100, 'materials' => 'Рогожка', 'weight_kg' => 25, 'package_volume_m3' => 0.6, 'warranty' => '12 месяцев'],
        ],
        [
            'name' => 'Кресло-кровать Дельфин',
            'description' => 'Кресло-кровать с механизмом «дельфин» и бельевым ящиком. Спальное место 130х190 см.',
            'color' => 'Серый',
            'price' => 16800.00,
            'quantity' => 6,
            'category' => 'Кресла',
            'manufacturer' => 'Ангстрем',
            'images' => 3,
            'spec' => ['width_cm' => 85, 'length_cm' => 95, 'height_cm' => 75, 'folding_type' => 'Дельфин', 'materials' => 'Велюр', 'weight_kg' => 35, 'package_volume_m3' => 1.0, 'warranty' => '24 месяца'],
        ],
        [
            'name' => 'Кресло-качалка Мечта',
            'description' => 'Кресло-качалка из массива с мягким сиденьем и подлокотниками. Максимальная нагрузка 120 кг.',
            'color' => 'Орех',
            'price' => 14600.00,
            'quantity' => 5,
            'category' => 'Кресла',
            'manufacturer' => 'Лазурит',
            'images' => 2,
            'spec' => ['width_cm' => 68, 'length_cm' => 95, 'height_cm' => 105, 'materials' => 'Массив берёзы', 'surface' => 'Лак', 'weight_kg' => 22, 'package_volume_m3' => 0.7, 'warranty' => '24 месяца'],
        ],
        [
            'name' => 'Кресло релакс Орбита',
            'description' => 'Кресло для отдыха с высокой спинкой и механизмом релакс.',
            'color' => 'Синий',
            'price' => 15400.00,
            'quantity' => 7,
            'category' => 'Кресла',
            'manufacturer' => 'Шатура',
            'images' => 2,
            'spec' => ['width_cm' => 78, 'length_cm' => 84, 'height_cm' => 112, 'folding_type' => 'Релакс', 'materials' => 'Велюр', 'weight_kg' => 24, 'package_volume_m3' => 0.65, 'warranty' => '24 месяца'],
        ],

        // Пуфы
        [
            'name' => 'Пуф квадратный Вельвет',
            'description' => 'Квадратный пуф с чехлом из вельвета и наполнителем из пенополиуретана.',
            'color' => 'Розовый',
            'price' => 3900.00,
            'quantity' => 14,
            'category' => 'Пуфы',
            'manufacturer' => 'Много мебели',
            'images' => 2,
            'spec' => ['width_cm' => 40, 'length_cm' => 40, 'height_cm' => 40, 'materials' => 'Вельвет, ППУ', 'weight_kg' => 4, 'package_volume_m3' => 0.07, 'warranty' => '12 месяцев'],
        ],
        [
            'name' => 'Пуф-банкетка с ящиком',
            'description' => 'Пуф-банкетка с подъёмным сиденьем и ящиком для хранения обуви или пледов.',
            'color' => 'Коричневый',
            'price' => 6900.00,
            'quantity' => 10,
            'category' => 'Пуфы',
            'manufacturer' => 'ТриЯ',
            'images' => 2,
            'spec' => ['width_cm' => 80, 'length_cm' => 35, 'height_cm' => 45, 'materials' => 'Экокожа, фанера', 'weight_kg' => 9, 'package_volume_m3' => 0.15, 'warranty' => '12 месяцев'],
        ],
        [
            'name' => 'Пуф круглый Кокос',
            'description' => 'Круглый пуф-капля с плотным наполнителем. Подходит для гостиной и детской.',
            'color' => 'Бежевый',
            'price' => 3300.00,
            'quantity' => 16,
            'category' => 'Пуфы',
            'manufacturer' => 'Ангстрем',
            'images' => 2,
            'spec' => ['width_cm' => 45, 'length_cm' => 45, 'height_cm' => 42, 'materials' => 'Рогожка, ППУ', 'weight_kg' => 5, 'package_volume_m3' => 0.09, 'warranty' => '12 месяцев'],
        ],
        [
            'name' => 'Пуф цилиндр Классик',
            'description' => 'Цилиндрический пуф с жёстким каркасом и съёмным чехлом из рогожки.',
            'color' => 'Серый',
            'price' => 4200.00,
            'quantity' => 14,
            'category' => 'Пуфы',
            'manufacturer' => 'Много мебели',
            'images' => 2,
            'spec' => ['width_cm' => 45, 'length_cm' => 45, 'height_cm' => 45, 'materials' => 'Рогожка, ППУ', 'weight_kg' => 4, 'package_volume_m3' => 0.08, 'warranty' => '12 месяцев'],
        ],
    ];

    public function run(): void
    {
        Model::withoutEvents(function (): void {
            $this->seedManufacturers();
            $this->seedItems();

            if (config('elasticsearch.enabled')) {
                Artisan::call('search:reindex', ['--no-interaction' => true]);
            }
        });
    }

    private function seedManufacturers(): void
    {
        foreach (['Шатура', 'Ангстрем', 'Столплит', 'Много мебели', 'Лазурит', 'ТриЯ', 'Миасс-Мебель', 'Сандэр', 'Дятьково'] as $brand) {
            FurnitureManufacturer::firstOrCreate(['name' => $brand], ['slug' => Str::slug($brand)]);
        }
    }

    private function seedItems(): void
    {
        Storage::disk('public')->makeDirectory('furniture');

        foreach (self::ITEMS as $item) {
            $category = FurnitureCategory::where('name', $item['category'])->first();
            if ($category === null) {
                continue;
            }

            $manufacturer = FurnitureManufacturer::where('name', $item['manufacturer'])->first();

            $furniture = Furniture::updateOrCreate(
                ['name' => $item['name']],
                [
                    'description' => $item['description'],
                    'color' => $item['color'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'manufacturer_id' => $manufacturer?->manufacturer_id,
                    'category_id' => $category->category_id,
                ],
            );

            if ($furniture->specifications_id !== null) {
                FurnitureSpecification::where('id', $furniture->specifications_id)->delete();
            }

            $spec = FurnitureSpecification::create($item['spec']);
            $furniture->update(['specifications_id' => $spec->id]);

            $furniture->images()->delete();
            $this->seedImages($furniture, $item);
        }
    }

    /**
     * Generate exactly IMAGE_COUNT photos for the furniture item. Tries DALL-E
     * first, tops up with real photographs from Openverse, and pads any
     * remaining slots with catalog SVG illustrations, so every item always
     * ends up with the full gallery of five pictures.
     *
     * @param  array<string, mixed>  $item
     */
    private function seedImages(Furniture $furniture, array $item): void
    {
        $slug = $this->slug($item['name']);
        $index = 0;

        if ($this->dalleConfigured()) {
            $index = $this->seedWithDalle($furniture, $slug, $item, $index);
        }

        if ($index < self::IMAGE_COUNT) {
            $index = $this->seedWithOpenverse($furniture, $item, $slug, $index);
        }

        if ($index < self::IMAGE_COUNT) {
            $this->seedFallbackSvg($furniture, $item['name'], $slug, $index);
        }
    }

    /**
     * Generate one photo per remaining slot with OpenAI DALL-E 3.
     *
     * @param  array<string, mixed>  $item
     */
    private function seedWithDalle(Furniture $furniture, string $slug, array $item, int $index): int
    {
        for (; $index < self::IMAGE_COUNT; $index++) {
            $imageData = $this->generateWithDalle($this->buildPrompt($item, $index + 1));

            if ($imageData === null || $imageData === '') {
                break;
            }

            $this->storeImage($furniture, $slug, 'png', $imageData, $index);

            sleep(2);
        }

        return $index;
    }

    /**
     * Persist raw image bytes to the local storage and create a model row.
     */
    private function storeImage(Furniture $furniture, string $slug, string $extension, string $data, int $index): void
    {
        $path = "furniture/{$slug}-".($index + 1).".{$extension}";

        Storage::disk('public')->put($path, $data);

        FurnitureImage::create([
            'furniture_id' => $furniture->furniture_id,
            'path_image' => $path,
            'is_main' => $index === 0,
        ]);
    }

    /**
     * Whether an OpenAI API key is configured and can be used for DALL-E.
     */
    private function dalleConfigured(): bool
    {
        $apiKey = config('services.openai.api_key');

        return filled($apiKey) && $apiKey !== 'your_openai_api_key_here';
    }

    /**
     * Build a detailed English prompt that accurately describes the furniture piece.
     *
     * @param  array<string, mixed>  $item
     */
    private function buildPrompt(array $item, int $viewIndex): string
    {
        $categoryName = $item['category'];
        $categoryEn = self::CATEGORY_EN[$categoryName] ?? 'modern furniture';
        $colorEn = self::COLOR_EN[$item['color'] ?? ''] ?? 'natural wood';
        $materialsEn = self::MATERIAL_EN[$item['spec']['materials'] ?? ''] ?? 'quality materials';
        $angles = ['front view', 'three-quarter angle', 'side view', 'close-up detail', 'interior room setting'];
        $angle = $angles[$viewIndex - 1] ?? $angles[0];
        $furnitureName = $this->toEnglishName($item['name']);

        return "Professional e-commerce product photograph of {$furnitureName}, "
            ."a {$colorEn} {$categoryEn}"
            .", made of {$materialsEn}"
            .". {$categoryName} style furniture. {$item['description']}"
            ." {$angle}"
            .'. Studio lighting, clean light-gray seamless background, centered composition, '
            .'subtle realistic shadow, ultra-detailed, sharp focus, full item fully visible, '
            .'no people, no text, no watermark, no labels.';
    }

    /**
     * Translate a furniture keyword to its English equivalent.
     */
    private function translateFurnitureType(string $type): string
    {
        $map = [
            'Комод' => 'chest of drawers',
            'Тумба' => 'cabinet',
            'Кровать' => 'bed',
            'Стол' => 'table',
            'Шкаф' => 'wardrobe',
            'Диван' => 'sofa',
            'Кресло' => 'armchair',
            'Пуф' => 'ottoman',
            'Матрас' => 'mattress',
            'Стеллаж' => 'bookshelf',
            'Прихожая' => 'hallway console',
            'Зеркало' => 'floor mirror',
            'Вешалка' => 'coat rack',
            'Стенка' => 'wall unit',
            'Дверь' => 'sliding door',
            'Пенал' => 'tall cabinet',
            'Гарнитур' => 'kitchen set',
            'Группа' => 'dining set',
        ];

        foreach ($map as $russian => $english) {
            if (str_starts_with($type, $russian)) {
                return $english;
            }
        }

        return 'furniture piece';
    }

    /**
     * Transliterate a Russian furniture name into an English brand-style name.
     *
     * Recognized model names are kept, otherwise each Cyrillic word is mapped
     * against a small phrase dictionary to preserve the meaning.
     */
    private function toEnglishName(string $name): string
    {
        $known = [
            'Комод Мальта 5 ящиков' => 'Malta chest of drawers with five drawers',
            'Комод Грация с зеркалом' => 'Grazia chest of drawers with mirror',
            'Тумба ТВ Верни 120' => 'Verni TV console 120 cm',
            'Кровать детская Соня 80x160' => 'Sonya kids bed 80x160',
            'Стол школьника Юниор' => 'Junior student desk',
            'Комод детский Мишутка' => 'Mishutka kids chest of drawers',
            'Кухонный гарнитур Афина' => 'Afina kitchen set',
            'Обеденная группа Орфей' => 'Orpheus dining set with chairs',
            'Шкаф навесной для кухни Лира' => 'Lira wall kitchen cabinet',
            'Стол руководителя Атлант' => 'Atlant executive office desk',
            'Кресло офисное Престиж' => 'Prestige office chair',
            'Шкаф для документов Бизнес' => 'Biznes document cabinet',
            'Тумба под раковину Волна' => 'Volna bathroom vanity with sink',
            'Пенал для ванной Альба' => 'Alba tall bathroom cabinet',
            'Кровать двуспальная Венеция 160x200' => 'Venice double bed 160x200',
            'Шкаф для спальни Флоренция' => 'Florence bedroom wardrobe',
            'Прикроватная тумба Лорен' => 'Loren nightstand',
            'Стеллаж книжный Мадрид' => 'Madrid bookcase',
            'Стеллаж открытый Модерн 4 полки' => 'Modern open shelving unit with four shelves',
            'Стеллаж угловой Венеция' => 'Venice corner bookshelf',
            'Прихожая Камелия' => 'Camellia hallway set with coat rack',
            'Прихожая Эдем с зеркалом' => 'Eden hallway set with mirror',
            'Стенка гостиная Монте-Карло' => 'Monte-Carlo living room wall unit',
            'Стенка модульная Белла' => 'Bella modular wall unit',
            'Стол обеденный Классика 120x80' => 'Classic dining table 120x80',
            'Стол журнальный Прованс' => 'Provence coffee table',
            'Стол компьютерный Грация' => 'Grazia computer desk',
            'Стол раскладной Дельфин' => 'Dolphin folding table',
            'Шкаф-купе Трио с зеркалом' => 'Trio sliding wardrobe with mirror',
            'Шкаф-купе Стелла 3 двери' => 'Stella three-door sliding wardrobe',
            'Шкаф-купе угловой Милан' => 'Milan corner sliding wardrobe',
            'Дверь раздвижная Стекло Мат' => 'Frosted glass sliding door',
            'Дверь-купе Зеркало Венеция' => 'Venice mirrored sliding door',
            'Вешалка напольная Рига' => 'Riga freestanding coat rack',
            'Зеркало напольное Арлекин' => 'Harlequin full-length floor mirror',
            'Матрас Ортопед Люкс 160x200' => 'Orthoped Lux orthopedic mattress 160x200',
            'Матрас Дабл Пружинный 90x200' => 'Double spring mattress 90x200',
            'Матрас детский Кокос 80x160' => 'Cocos kids coconut mattress 80x160',
            'Матрас Мемори 140x200' => 'Memory foam mattress 140x200',
            'Диван Модерн трёхместный' => 'Modern three-seat sofa',
            'Диван Классика с подлокотниками' => 'Classic sofa with armrests',
            'Диван-кровать Уют еврокнижка' => 'Uyut sofa-bed eurolift',
            'Диван угловой Гостиная' => 'L-shaped corner sofa',
            'Диван Компакт Аккордеон' => 'Compact accordion sofa-bed',
            'Кресло Классик для отдыха' => 'Classic lounge armchair',
            'Кресло-кровать Дельфин' => 'Dolphin armchair bed',
            'Кресло-качалка Мечта' => 'Mechta wooden rocking chair',
            'Пуф квадратный Вельвет' => 'Square velvet pouf',
            'Пуф-банкетка с ящиком' => 'Storage ottoman bench',
            'Пуф круглый Кокос' => 'Round Cocos pouf',
            'Тумба прикроватная Норд' => 'Nord nightstand',
            'Кровать детская Малыш 70x140' => 'Malysh kids bed 70x140',
            'Кухонный гарнитур Виктория' => 'Victoria kitchen set',
            'Стол для переговоров Капитолий' => 'Capitol conference table',
            'Зеркало для ванной Капля' => 'Kaplya bathroom mirror',
            'Тумба для ванной Веста' => 'Vesta bathroom cabinet',
            'Кровать односпальная Орхидея 90x200' => 'Orchidea single bed 90x200',
            'Шкаф трёхдверный Венеция' => 'Venice three-door wardrobe with mirror',
            'Стеллаж модульный Лондон 6 полок' => 'London modular bookcase with six shelves',
            'Прихожая Венеция с сиденьем' => 'Venice hallway set with seat',
            'Стенка гостиная Прага' => 'Prague living room wall unit',
            'Стол обеденный Монако 160x90' => 'Monaco dining table 160x90',
            'Стол-трансформер Гнездо 150x80' => 'Gnezdo folding table 150x80',
            'Шкаф-купе Прованс 2 двери' => 'Provence two-door sliding wardrobe',
            'Дверь раздвижная Белое Дерево' => 'White frosted glass sliding door',
            'Этажерка для книг Лофт' => 'Loft metal bookcase',
            'Матрас Прованс пружинный 120x200' => 'Provence spring mattress 120x200',
            'Диван Монако угловой' => 'Monaco corner sofa',
            'Кресло релакс Орбита' => 'Orbita relaxation armchair',
            'Пуф цилиндр Классик' => 'Classic cylinder pouf',
        ];

        return $known[$name] ?? 'modern '.$this->translateFurnitureType($name);
    }

    /**
     * Request an image from the OpenAI DALL-E 3 API.
     */
    private function generateWithDalle(string $prompt): ?string
    {
        $apiKey = config('services.openai.api_key');

        if (blank($apiKey) || $apiKey === 'your_openai_api_key_here') {
            return null;
        }

        try {
            $response = Http::withToken($apiKey)
                ->timeout(120)
                ->retry(3, 2000)
                ->post('https://api.openai.com/v1/images/generations', [
                    'model' => 'dall-e-3',
                    'prompt' => $prompt,
                    'n' => 1,
                    'size' => '1024x1024',
                    'quality' => 'standard',
                    'response_format' => 'b64_json',
                ]);
        } catch (Throwable) {
            return null;
        }

        if ($response->failed()) {
            return null;
        }

        $base64 = data_get($response->json(), 'data.0.b64_json');

        if (! is_string($base64) || $base64 === '') {
            return null;
        }

        return base64_decode($base64, true) ?: null;
    }

    /**
     * Search the Openverse API for photographs matching the furniture item and
     * persist the best results to the local storage. Returns the number of
     * images persisted, starting from the given index.
     *
     * @param  array<string, mixed>  $item
     */
    private function seedWithOpenverse(Furniture $furniture, array $item, string $slug, int $index): int
    {
        if ($this->openverseFailures >= self::OPENVERSE_FAILURE_LIMIT) {
            $this->openverseAvailable = false;
        }

        if (! $this->openverseAvailable) {
            return $index;
        }

        foreach ($this->openverseQueries($item) as $query) {
            if ($query === '') {
                continue;
            }

            $results = $this->searchOpenverse($query, 30);

            if ($results === null || $results === []) {
                continue;
            }

            $index = $this->persistOpenverseResults($furniture, $item, $slug, $index, $query, $results);

            if ($index >= self::IMAGE_COUNT) {
                break;
            }
        }

        return $index;
    }

    /**
     * Build prioritized Openverse search queries for the furniture item. The
     * keywords are always common English furniture terms, because long phrases
     * return no matches.
     *
     * @param  array<string, mixed>  $item
     * @return string[]
     */
    private function openverseQueries(array $item): array
    {
        $typeEn = $this->translateFurnitureType($item['name']);
        $categoryEn = self::CATEGORY_EN[$item['category']] ?? 'furniture';
        $colorEn = self::COLOR_EN[$item['color'] ?? ''] ?? '';

        $keywords = array_values(array_unique(array_filter([
            $typeEn !== 'furniture piece' ? $typeEn : null,
            $this->primaryCategoryTerm($categoryEn),
            $colorEn,
        ])));

        if ($keywords === []) {
            return [];
        }

        return [
            implode(' ', array_slice($keywords, 0, min(2, count($keywords)))),
            implode(' ', array_slice($keywords, 0, min(3, count($keywords)))),
            $keywords[0],
        ];
    }

    private function primaryCategoryTerm(string $category): string
    {
        return trim(explode(',', $category)[0]);
    }

    /**
     * Download and persist the best Openverse results for a single item.
     * Photographs containing people, 3D renders, and images already assigned to
     * another item are rejected; the rest are ranked by how well the title and
     * tags match the furniture keywords.
     *
     * @param  array<string, mixed>  $item
     * @param  array<int, array<string, mixed>>  $results
     */
    private function persistOpenverseResults(Furniture $furniture, array $item, string $slug, int $index, string $query, array $results): int
    {
        $colorEn = self::COLOR_EN[$item['color'] ?? ''] ?? '';
        $keywords = collect(explode(' ', $query))
            ->reject(fn (string $token): bool => in_array($token, self::STOP_WORDS, true) || $token === '')
            ->unique();

        $candidates = collect($results)
            ->reject(fn (array $image): bool => $this->hasPeople($image) || strtolower((string) ($image['provider'] ?? '')) === 'sketchfab')
            ->filter(fn (array $image): bool => filled($image['url'] ?? null) && ($image['mature'] ?? false) === false)
            ->map(fn (array $image): array => [
                'image' => $image,
                'rank' => $this->scoreImageCandidate($image, $keywords, $colorEn),
                'area' => (int) ($image['width'] ?? 0) * (int) ($image['height'] ?? 0),
            ])
            ->filter(fn (array $entry): bool => $entry['rank'] > 0 && ! isset($this->usedImageUrls[$entry['image']['url']]))
            ->sortByDesc('rank')
            ->sortByDesc('area')
            ->values();

        foreach ($candidates as $entry) {
            if ($index >= self::IMAGE_COUNT) {
                break;
            }

            $image = $entry['image'];
            $imageData = $this->downloadImage($image['url']);

            if ($imageData === null) {
                continue;
            }

            $this->usedImageUrls[$image['url']] = true;

            $extension = $this->extensionFromUrl($image['url']);
            $this->storeImage($furniture, $slug, $extension, $imageData, $index);

            $index++;

            usleep(300000);
        }

        return $index;
    }

    /**
     * Reject photographs that likely contain people, based on the image title
     * and tags.
     *
     * @param  array<string, mixed>  $image
     */
    private function hasPeople(array $image): bool
    {
        $text = mb_strtolower(
            (string) ($image['title'] ?? '').' '.implode(' ', array_column($image['tags'] ?? [], 'name')),
        );

        return (bool) preg_match(self::PERSON_PATTERN, $text);
    }

    /**
     * Rank how well a photograph matches the searched furniture keywords.
     * A keyword appearing in the title weighs more than a tag match, and a
     * matching furniture color gives a small bonus.
     *
     * @param  array<string, mixed>  $image
     * @param  iterable<string>  $keywords
     */
    private function scoreImageCandidate(array $image, iterable $keywords, string $colorEn): int
    {
        $title = mb_strtolower((string) ($image['title'] ?? ''));
        $tags = mb_strtolower(implode(' ', array_column($image['tags'] ?? [], 'name')));
        $score = 0;

        foreach ($keywords as $keyword) {
            if (str_contains($title, $keyword)) {
                $score += 4;
            } elseif (str_contains($tags, $keyword)) {
                $score += 2;
            }
        }

        if ($colorEn !== '') {
            foreach (explode(' ', $colorEn) as $word) {
                if ($word === '') {
                    continue;
                }

                if (str_contains($title, $word) || str_contains($tags, $word)) {
                    $score += 1;

                    break;
                }
            }
        }

        return $score;
    }

    /**
     * Query the Openverse image search API and return the raw results array, or
     * null when the request could not be completed.
     *
     * @return array<int, array<string, mixed>>|null
     */
    private function searchOpenverse(string $query, int $pageSize): ?array
    {
        for ($attempt = 0; $attempt < 2; $attempt++) {
            if ($attempt > 0) {
                sleep(2);
            }

            try {
                $response = Http::withHeaders(['User-Agent' => 'ArchiWeb furniture seeder'])
                    ->timeout(60)
                    ->get('https://api.openverse.org/v1/images/', [
                        'q' => $query,
                        'page_size' => $pageSize,
                        'categories' => 'photograph',
                    ]);
            } catch (Throwable) {
                $this->openverseFailures++;

                continue;
            }

            if ($response->status() === 429) {
                $this->openverseFailures++;
                sleep(1);

                continue;
            }

            if ($response->failed() || $response->json() === null) {
                $this->openverseFailures++;

                continue;
            }

            $this->openverseFailures = 0;

            return $response->json('results') ?? [];
        }

        return null;
    }

    /**
     * Download the raw image bytes from an external URL.
     */
    private function downloadImage(string $url): ?string
    {
        try {
            $response = Http::timeout(30)->get($url);
        } catch (Throwable) {
            return null;
        }

        if ($response->failed() || $response->body() === '') {
            return null;
        }

        return $response->body();
    }

    /**
     * Resolve a safe file extension from an image URL, defaulting to jpg.
     */
    private function extensionFromUrl(string $url): string
    {
        $path = (string) parse_url($url, PHP_URL_PATH);
        $extension = mb_strtolower((string) pathinfo($path, PATHINFO_EXTENSION));

        return in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif'], true)
            ? $extension
            : 'jpg';
    }

    /**
     * Catalog SVG illustrations when image generation is unavailable or has not
     * produced enough photos. Each picture actually depicts the furniture item
     * from a different angle. Pads the gallery up to IMAGE_COUNT images.
     */
    private function seedFallbackSvg(Furniture $furniture, string $name, string $slug, int $index): void
    {
        $color = self::COLOR_HEX[$furniture->color] ?? '#a08c7a';
        $category = $furniture->category->name ?? 'Мебель';
        $renderer = new FurnitureSvgRenderer;

        while ($index < self::IMAGE_COUNT) {
            $this->storeImage($furniture, $slug, 'svg', $renderer->render($name, $category, $color, $index + 1), $index);
            $index++;
        }
    }

    private function slug(string $name): string
    {
        $map = [
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e',
            'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm',
            'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u',
            'ф' => 'f', 'х' => 'kh', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch',
            'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
        ];

        $slug = mb_strtolower($name);
        $slug = strtr($slug, $map);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');

        return $slug ?: Str::slug($name);
    }
}
