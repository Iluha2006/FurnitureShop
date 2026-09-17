<?php

use App\Bus\CachedQueryBus;
use App\Data\FurnitureDetailData;
use App\Data\FurnitureSpecificationData;
use App\Interfaces\CacheableQuery;
use App\Interfaces\QueryBusInterface;
use App\Interfaces\QueryInterface;
use App\Models\Furniture;
use App\Models\FurnitureCategory;
use App\Models\FurnitureImage;
use App\Models\FurnitureManufacturer;
use App\Models\FurnitureSpecification;
use App\Queries\GetCategoriesQuery;
use App\Queries\GetFurnitureByCategoryQuery;
use App\Queries\GetFurnitureHitsQuery;
use App\Queries\GetFurnitureQuery;
use App\Queries\GetFurnitureSpecificationsQuery;
use App\Queries\GetRelatedFurnitureQuery;
use App\Repositories\FurnitureSpecificationRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    Cache::flush();
    DB::flushQueryLog();
});

test('cacheable queries are resolved once and served from cache afterwards', function () {
    $fakeBus = new class implements QueryBusInterface
    {
        public int $calls = 0;

        public function ask(QueryInterface $query): mixed
        {
            $this->calls++;

            return new ArrayObject(['n' => $this->calls]);
        }
    };

    $cacheable = new class implements CacheableQuery
    {
        public function cacheKey(): string
        {
            return 'test.cacheable';
        }

        public function cacheTtlSeconds(): int
        {
            return 60;
        }

        public function cacheTags(): array
        {
            return ['test'];
        }
    };

    $bus = new CachedQueryBus($fakeBus, Cache::store());

    $first = $bus->ask($cacheable);
    $second = $bus->ask($cacheable);

    expect($fakeBus->calls)->toBe(1);
    expect($second)->toBe($first);
});

test('non-cacheable queries bypass the cache', function () {
    $fakeBus = new class implements QueryBusInterface
    {
        public int $calls = 0;

        public function ask(QueryInterface $query): mixed
        {
            $this->calls++;

            return $this->calls;
        }
    };

    $plain = new class implements QueryInterface
    {
        //
    };

    $bus = new CachedQueryBus($fakeBus, Cache::store());

    $bus->ask($plain);
    $bus->ask($plain);

    expect($fakeBus->calls)->toBe(2);
});

test('cached results refresh after the store is flushed', function () {
    $fakeBus = new class implements QueryBusInterface
    {
        public int $calls = 0;

        public function ask(QueryInterface $query): mixed
        {
            $this->calls++;

            return $this->calls;
        }
    };

    $cacheable = new class implements CacheableQuery
    {
        public function cacheKey(): string
        {
            return 'test.cacheable';
        }

        public function cacheTtlSeconds(): int
        {
            return 60;
        }

        public function cacheTags(): array
        {
            return ['test'];
        }
    };

    $bus = new CachedQueryBus($fakeBus, Cache::store());

    expect($bus->ask($cacheable))->toBe(1);

    Cache::store()->flush();

    expect($bus->ask($cacheable))->toBe(2);
    expect($fakeBus->calls)->toBe(2);
});

test('the cached categories query hits the database only once', function () {
    FurnitureCategory::factory()->count(3)->create();

    $bus = app(QueryBusInterface::class);

    DB::enableQueryLog();

    $first = $bus->ask(new GetCategoriesQuery);
    $firstQueryCount = count(DB::getQueryLog());

    $second = $bus->ask(new GetCategoriesQuery);

    expect($second)->toEqual($first);
    expect(count(DB::getQueryLog()))->toBe($firstQueryCount);
});

test('furniture details and its specifications resolve through the bus', function () {
    $category = FurnitureCategory::factory()->create();
    $manufacturer = FurnitureManufacturer::factory()->create();
    $specification = FurnitureSpecification::factory()->create();
    $furniture = Furniture::factory()->create([
        'category_id' => $category->category_id,
        'manufacturer_id' => $manufacturer->manufacturer_id,
        'specifications_id' => $specification->id,
    ]);
    FurnitureImage::factory()->main()->create([
        'furniture_id' => $furniture->furniture_id,
    ]);

    $bus = app(QueryBusInterface::class);

    $detail = $bus->ask(new GetFurnitureQuery(furnitureId: $furniture->furniture_id));
    $specs = $bus->ask(new GetFurnitureSpecificationsQuery(furnitureId: $furniture->furniture_id));

    expect($detail)->toBeInstanceOf(FurnitureDetailData::class);
    expect($detail->furniture->furniture_id)->toBe($furniture->furniture_id);
    expect($detail->category?->category_id)->toBe($category->category_id);
    expect($detail->manufacturer?->manufacturer_id)->toBe($manufacturer->manufacturer_id);
    expect($detail->images)->toHaveCount(1);

    expect($specs)->toBeInstanceOf(FurnitureSpecificationData::class);
    expect($specs->id)->toBe($specification->id);
});

test('category page returns only in-stock furniture of the requested category', function () {
    $category = FurnitureCategory::factory()->create();
    Furniture::factory()->count(2)->create([
        'category_id' => $category->category_id,
        'quantity' => 5,
    ]);
    Furniture::factory()->create([
        'category_id' => $category->category_id,
        'quantity' => 0,
    ]);
    $otherCategory = FurnitureCategory::factory()->create();
    Furniture::factory()->create([
        'category_id' => $otherCategory->category_id,
        'quantity' => 3,
    ]);

    $bus = app(QueryBusInterface::class);

    $result = $bus->ask(
        new GetFurnitureByCategoryQuery(categoryId: $category->category_id, perPage: 15, page: 1),
    );

    expect($result->total())->toBe(2);

    collect($result->items())->each(function ($item) use ($category) {
        expect($item->furniture->category_id)->toBe($category->category_id);
    });
});

test('related query returns in-stock items of the same category excluding the current one', function () {
    $category = FurnitureCategory::factory()->create();
    $current = Furniture::factory()->create([
        'category_id' => $category->category_id,
        'quantity' => 5,
    ]);
    Furniture::factory()->count(3)->create([
        'category_id' => $category->category_id,
        'quantity' => 5,
    ]);
    Furniture::factory()->create([
        'category_id' => $category->category_id,
        'quantity' => 0,
    ]);
    $otherCategory = FurnitureCategory::factory()->create();
    Furniture::factory()->create([
        'category_id' => $otherCategory->category_id,
        'quantity' => 5,
    ]);

    $bus = app(QueryBusInterface::class);

    $related = $bus->ask(new GetRelatedFurnitureQuery(
        categoryId: $category->category_id,
        excludeId: $current->furniture_id,
    ));

    expect($related)->toHaveCount(3);
    expect($related->pluck('furniture.furniture_id'))
        ->not->toContain($current->furniture_id);
    expect($related->every(fn ($card) => $card->furniture->category_id === $category->category_id))
        ->toBeTrue();
    expect($related->every(fn ($card) => $card->furniture->quantity > 0))->toBeTrue();
});

test('furniture hits resolve in-stock items with a main image', function () {
    $category = FurnitureCategory::factory()->create();
    $furniture = Furniture::factory()->count(2)->create([
        'category_id' => $category->category_id,
        'quantity' => 5,
    ]);
    $furniture->each(
        fn (Furniture $item) => FurnitureImage::factory()->main()->create([
            'furniture_id' => $item->furniture_id,
        ]),
    );
    Furniture::factory()->create([
        'category_id' => $category->category_id,
        'quantity' => 0,
    ]);

    $bus = app(QueryBusInterface::class);

    $hits = $bus->ask(new GetFurnitureHitsQuery(limit: 14));

    expect($hits)->toHaveCount(2);
    expect($hits->every(fn ($card) => $card->furniture->quantity > 0))->toBeTrue();
    expect($hits->every(fn ($card) => is_string($card->mainImage)))->toBeTrue();
});

test('furniture specifications use the separated specification repository', function () {
    $specification = FurnitureSpecification::factory()->create();
    $furniture = Furniture::factory()->create([
        'specifications_id' => $specification->id,
    ]);

    $repository = app(FurnitureSpecificationRepository::class);

    $result = $repository->getForFurniture($furniture->furniture_id);

    expect($result)->toBeInstanceOf(FurnitureSpecificationData::class);
    expect($result->id)->toBe($specification->id);

    $result = $repository->getForFurniture(999999);

    expect($result)->toBeNull();
});
