<?php

namespace Database\Seeders;

use App\Models\Furniture;
use App\Models\FurnitureCategory;
use App\Models\FurnitureImage;
use App\Models\FurnitureManufacturer;
use App\Models\FurnitureSpecification;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FurnitureSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Flickr photo tags mapped by furniture name (comma = AND, pipe = OR).
     *
     * @var array<string, string>
     */
    private const SEARCH_KEYWORDS = [
        // Комоды и тумбы
        'Комод Мальта 5 ящиков' => 'dresser,white,bedroom',
        'Комод Грация с зеркалом' => 'dresser,mirror,furniture',
        'Тумба ТВ Верни 120' => 'television,stand',
        // Мебель для детской комнаты
        'Кровать детская Соня 80x160' => 'kids,bed,child',
        'Стол школьника Юниор' => 'desk,school,kids',
        'Комод детский Мишутка' => 'kids,dresser,children',
        // Мебель для кухни
        'Кухонный гарнитур Афина' => 'kitchen,cabinet,white',
        'Обеденная группа Орфей' => 'dining,table,wood',
        'Шкаф навесной для кухни Лира' => 'kitchen,cabinet,wall',
        // Мебель для офиса
        'Стол руководителя Атлант' => 'desk,office,executive',
        'Кресло офисное Престиж' => 'office,chair,black',
        'Шкаф для документов Бизнес' => 'filing,cabinet,office',
        // Мебель для ванной комнаты
        'Тумба под раковину Волна' => 'bathroom,vanity,sink',
        'Пенал для ванной Альба' => 'bathroom,cabinet,tall',
        // Мебель для спальни
        'Кровать двуспальная Венеция 160x200' => 'double,bed,frame',
        'Шкаф для спальни Флоренция' => 'wardrobe,bedroom,closet',
        'Прикроватная тумба Лорен' => 'bedside,table',
        // Стеллажи
        'Стеллаж книжный Мадрид' => 'bookshelf,books,wood',
        'Стеллаж открытый Модерн 4 полки' => 'shelf,open,unit',
        'Стеллаж угловой Венеция' => 'corner,shelf,bookcase',
        // Прихожие
        'Прихожая Камелия' => 'hallway,bench,coat',
        'Прихожая Эдем с зеркалом' => 'hallway,wardrobe,mirror',
        // Стенки для гостиной
        'Стенка гостиная Монте-Карло' => 'tv,wall,cabinet',
        'Стенка модульная Белла' => 'living,room,cabinet',
        // Столы
        'Стол обеденный Классика 120x80' => 'dining,table,wood',
        'Стол журнальный Прованс' => 'coffee,table,white',
        'Стол компьютерный Грация' => 'computer,desk',
        'Стол раскладной Дельфин' => 'folding,table,wood',
        // Шкафы-купе
        'Шкаф-купе Трио с зеркалом' => 'wardrobe,mirror,sliding',
        'Шкаф-купе Стелла 3 двери' => 'wardrobe,sliding',
        'Шкаф-купе угловой Милан' => 'corner,wardrobe,closet',
        // Раздвижные двери
        'Дверь раздвижная Стекло Мат' => 'sliding,glass,door',
        'Дверь-купе Зеркало Венеция' => 'mirrored,sliding,door',
        // Товары для дома
        'Вешалка напольная Рига' => 'coat,rack,wood',
        'Зеркало напольное Арлекин' => 'mirror,full,length',
        // Матрасы
        'Матрас Ортопед Люкс 160x200' => 'mattress,bed,orthopedic',
        'Матрас Дабл Пружинный 90x200' => 'mattress,spring,bed',
        'Матрас детский Кокос 80x160' => 'kids,mattress,child',
        'Матрас Мемори 140x200' => 'mattress,memory,foam',
        // Диваны
        'Диван Модерн трёхместный' => 'sofa,modern,grey',
        'Диван Классика с подлокотниками' => 'sofa,classic,beige',
        'Диван-кровать Уют еврокнижка' => 'sofa,bed,convertible',
        'Диван угловой Гостиная' => 'corner,sofa,sectional',
        'Диван Компакт Аккордеон' => 'sofa,compact,living',
        // Кресла
        'Кресло Классик для отдыха' => 'armchair,lounge,comfort',
        'Кресло-кровать Дельфин' => 'armchair,convertible,bed',
        'Кресло-качалка Мечта' => 'rocking,chair,wood',
        // Пуфы
        'Пуф квадратный Вельвет' => 'ottoman,pink,velvet',
        'Пуф-банкетка с ящиком' => 'ottoman,storage,bench',
        'Пуф круглый Кокос' => 'ottoman,round,pouf',
    ];

    /**
     * Fallback tags by furniture category.
     *
     * @var array<string, string>
     */
    private const CATEGORY_TAGS = [
        'Комоды и тумбы' => 'dresser,bedroom,furniture',
        'Мебель для детской комнаты' => 'kids,bedroom,furniture',
        'Мебель для кухни' => 'kitchen,furniture,modern',
        'Мебель для офиса' => 'office,furniture,desk',
        'Мебель для ванной комнаты' => 'bathroom,furniture,modern',
        'Мебель для спальни' => 'bedroom,furniture,modern',
        'Стеллажи' => 'shelf,bookcase,wood',
        'Прихожие' => 'hallway,furniture,entry',
        'Стенки для гостиной' => 'living,room,furniture',
        'Столы' => 'table,wood,furniture',
        'Шкафы-купе' => 'wardrobe,closet,sliding',
        'Раздвижные двери' => 'sliding,door,interior',
        'Товары для дома' => 'home,decor,room',
        'Матрасы' => 'mattress,bed,sleep',
        'Диваны' => 'sofa,couch,living',
        'Кресла' => 'armchair,lounge,chair',
        'Пуфы' => 'ottoman,pouf,stool',
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
     * The 50 furniture items to seed.
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
    ];

    public function run(): void
    {
        $this->seedManufacturers();
        $this->seedItems();
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
            $this->seedImages($furniture, $item['name'], $item['images']);
        }
    }

    /**
     * Download real photos from Loremflickr (Flickr, no API key) by furniture tags.
     */
    private function seedImages(Furniture $furniture, string $name, int $count): void
    {
        $tags = self::SEARCH_KEYWORDS[$name] ?? self::CATEGORY_TAGS[$furniture->category?->name] ?? 'furniture';

        foreach (range(1, $count) as $index) {
            $url = 'https://loremflickr.com/800/600/'.urlencode($tags);

            try {
                $response = Http::timeout(30)
                    ->retry(3, 1500)
                    ->get($url);

                $imageData = $response->body();
            } catch (RequestException $e) {
                $this->seedFallbackSvg($furniture, $name, $count);

                return;
            }

            if ($imageData === '' || strlen($imageData) < 500) {
                $this->seedFallbackSvg($furniture, $name, $count);

                return;
            }

            $slug = $this->slug($name);
            $filename = "{$slug}-{$index}.jpg";
            $path = "furniture/{$filename}";

            Storage::disk('public')->put($path, $imageData);

            FurnitureImage::create([
                'furniture_id' => $furniture->furniture_id,
                'path_image' => $path,
                'is_main' => $index === 1,
            ]);
        }
    }

    /**
     * SVG placeholder when Loremflickr is unreachable.
     */
    private function seedFallbackSvg(Furniture $furniture, string $name, int $count): void
    {
        $base = $this->slug($name);
        $color = self::COLOR_HEX[$furniture->color] ?? '#a08c7a';
        $category = $furniture->category?->name ?? 'Мебель';

        foreach (range(1, $count) as $index) {
            $path = "furniture/{$base}-{$index}.svg";

            Storage::disk('public')->put($path, $this->buildPlaceholderSvg($name, $category, $color));

            FurnitureImage::create([
                'furniture_id' => $furniture->furniture_id,
                'path_image' => $path,
                'is_main' => $index === 1,
            ]);
        }
    }

    private function buildPlaceholderSvg(string $name, string $category, string $color): string
    {
        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeCategory = htmlspecialchars($category, ENT_QUOTES, 'UTF-8');

        return <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" width="800" height="600" viewBox="0 0 800 600">
          <defs>
            <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
              <stop offset="0%" stop-color="{$color}" stop-opacity="0.9"/>
              <stop offset="100%" stop-color="{$color}" stop-opacity="0.55"/>
            </linearGradient>
          </defs>
          <rect width="800" height="600" fill="url(#bg)"/>
          <rect x="40" y="40" width="720" height="520" fill="none" stroke="#ffffff" stroke-opacity="0.4" stroke-width="2" rx="8"/>
          <text x="400" y="295" font-family="Arial, sans-serif" font-size="34" font-weight="bold" fill="#ffffff" text-anchor="middle">{$safeName}</text>
          <text x="400" y="330" font-family="Arial, sans-serif" font-size="20" fill="#ffffff" fill-opacity="0.85" text-anchor="middle">{$safeCategory}</text>
        </svg>
        SVG;
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
