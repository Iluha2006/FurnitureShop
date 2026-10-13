<?php

namespace Database\Seeders;

use App\Models\FurnitureCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FurnitureCategorySeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * The furniture categories mapped to their image url.
     *
     * @var array<string, string>
     */
    private const CATEGORIES = [
        'Комоды и тумбы' => 'https://mebelhouseshop.ru/upload/resize_cache/webp/iblock/5ed/14.webp',
        'Мебель для детской комнаты' => 'https://www.dommebeli.spb.ru/upload/iblock/e75/e755efdbe022465766afedd6d56e4aac.jpg',
        'Мебель для кухни' => 'https://ir.ozone.ru/s3/multimedia-1-9/8400652425.jpg',
        'Мебель для офиса' => 'https://fregat.pro/upload/dev2fun.imagecompress/webp/uf/268/2680d2749dae44904a215d5fa2b542d8.webp',
        'Мебель для ванной комнаты' => 'https://avatars.mds.yandex.net/i?id=0aa438ec997a8f84c85bb57793c6029a_l-9181886-images-thumbs&n=13',
        'Мебель для спальни' => 'https://www.mebelmsk.ru/upload/resize_cache/iblock/004/500_500_1/d15kd25jq3bzc0ogu8x570a4ixb8vn9l.jpg',
        'Стеллажи' => 'https://avatars.mds.yandex.net/i?id=cd21a989455988251969c369258383dc_l-10311516-images-thumbs&n=13',
        'Прихожие' => 'https://avatars.mds.yandex.net/get-mpic/4902598/2a0000019aba65253e1472a42ca4850948be/orig',
        'Стенки для гостиной' => 'https://avatars.mds.yandex.net/get-mpic/16435911/2a0000019ab7e7a1b8413b3d1a62c2e599eb/450x600',
        'Столы' => 'https://cs5.livemaster.ru/storage/ca/e3/3a589c81131609ad680c2997479e--dlya-doma-i-interera-160-40-40sm-obedennyj-razdvizhnoj-stol-i.jpg',
        'Шкафы-купе' => 'https://avatars.mds.yandex.net/get-mpic/11853589/2a0000019b306b56ff9e6f4811a6e41104ba/orig',
        'Раздвижные двери' => 'https://avatars.mds.yandex.net/get-mpic/7666641/2a000001921ee477ece18178581e72ffe335/orig',
        'Товары для дома' => 'https://avatars.mds.yandex.net/i?id=32aa862d7a6d6c90a3a647ecea5fc36a945b77a4-5869878-images-thumbs&n=13',
        'Матрасы' => 'https://ir.ozone.ru/s3/multimedia-1-c/9427652112.jpg',
        'Диваны' => 'https://main-cdn.sbermegamarket.ru/big2/hlr-system/ccs/256363/RFYtMDAxMTEzMThfMTI3MjQ3NTQ1/b0.jpg',
        'Кресла' => 'https://avatars.mds.yandex.net/get-mpic/20368720/2a0000019ed4e7b090cecdf94e49b6e24ea2/orig',
        'Пуфы' => 'https://meb-online.ru/upload/iblock/84b/puf_nyukasl.jpg',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::CATEGORIES as $name => $imageUrl) {
            FurnitureCategory::updateOrCreate(['name' => $name], ['image_url' => $imageUrl]);
        }
    }
}
