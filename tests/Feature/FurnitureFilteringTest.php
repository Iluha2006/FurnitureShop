<?php

use App\Data\Filter\FilterOptionsData;
use App\Data\Filter\FurnitureFilterData;
use App\Interfaces\QueryBusInterface;
use App\Models\Furniture;
use App\Models\FurnitureCategory;
use App\Models\FurnitureManufacturer;
use App\Models\FurnitureSpecification;
use App\Queries\GetFurnitureByCategoryQuery;
use App\Queries\GetFurnitureFilterOptionsQuery;
use Inertia\Testing\AssertableInertia as Assert;

function makeFurnitureWithSpec(int $categoryId, array $spec, array $furniture = []): Furniture
{
    $specification = FurnitureSpecification::factory()->create($spec);

    return Furniture::factory()->create([
        'category_id' => $categoryId,
        'quantity' => 5,
        'specifications_id' => $specification->id,
        ...$furniture,
    ]);
}

test('category furniture is ordered newest first by default', function () {
    $category = FurnitureCategory::factory()->create();

    Furniture::factory()->count(3)->sequence(
        ['created_at' => now()->subDays(3)],
        ['created_at' => now()],
        ['created_at' => now()->subDay()],
    )->create(['category_id' => $category->category_id, 'quantity' => 5]);

    $result = app(QueryBusInterface::class)->ask(new GetFurnitureByCategoryQuery(
        categoryId: $category->category_id,
    ));

    $ids = collect($result->items())->pluck('furniture.furniture_id')->all();
    $expected = Furniture::query()
        ->where('category_id', $category->category_id)
        ->orderByDesc('created_at')
        ->orderByDesc('furniture_id')
        ->pluck('furniture_id')
        ->all();

    expect($ids)->toBe($expected);
});

test('category furniture filters by a price range', function () {
    $category = FurnitureCategory::factory()->create();

    Furniture::factory()->count(3)->sequence(
        ['price' => 100.00],
        ['price' => 250.00],
        ['price' => 500.00],
    )->create(['category_id' => $category->category_id, 'quantity' => 5]);

    $result = app(QueryBusInterface::class)->ask(new GetFurnitureByCategoryQuery(
        categoryId: $category->category_id,
        filter: new FurnitureFilterData(price_min: 200, price_max: 400),
    ));

    $prices = collect($result->items())
        ->map(fn ($card) => (float) $card->furniture->price)
        ->all();

    expect($prices)->toBe([250.0]);
});

test('category furniture filters by a specification range', function () {
    $category = FurnitureCategory::factory()->create();

    makeFurnitureWithSpec($category->category_id, ['width_cm' => 60]);
    makeFurnitureWithSpec($category->category_id, ['width_cm' => 200]);

    $result = app(QueryBusInterface::class)->ask(new GetFurnitureByCategoryQuery(
        categoryId: $category->category_id,
        filter: new FurnitureFilterData(width_min: 100, width_max: 250),
    ));

    $specIds = collect($result->items())->pluck('furniture.specifications_id');
    $specs = FurnitureSpecification::whereIn('id', $specIds)->get()->keyBy('id');
    $widths = collect($result->items())
        ->map(fn ($card) => (float) $specs[$card->furniture->specifications_id]->width_cm)
        ->all();

    expect($widths)->toBe([200.0]);
});

test('category furniture filters by an enumerable specification value', function () {
    $category = FurnitureCategory::factory()->create();

    makeFurnitureWithSpec($category->category_id, ['folding_type' => 'Книжка']);
    makeFurnitureWithSpec($category->category_id, ['folding_type' => 'Дельфин']);
    makeFurnitureWithSpec($category->category_id, ['folding_type' => null]);

    $result = app(QueryBusInterface::class)->ask(new GetFurnitureByCategoryQuery(
        categoryId: $category->category_id,
        filter: new FurnitureFilterData(foldingType: ['Книжка']),
    ));

    $foldingTypes = FurnitureSpecification::whereIn(
        'id',
        collect($result->items())->pluck('furniture.specifications_id'),
    )->pluck('folding_type')->toArray();

    expect($foldingTypes)->toBe(['Книжка']);
});

test('category furniture filters by manufacturer', function () {
    $category = FurnitureCategory::factory()->create();
    $manufacturer = FurnitureManufacturer::factory()->create();

    makeFurnitureWithSpec($category->category_id, [], ['manufacturer_id' => $manufacturer->manufacturer_id]);
    makeFurnitureWithSpec($category->category_id, []);

    $result = app(QueryBusInterface::class)->ask(new GetFurnitureByCategoryQuery(
        categoryId: $category->category_id,
        filter: new FurnitureFilterData(manufacture: [(int) $manufacturer->manufacturer_id]),
    ));

    expect($result->total())->toBe(1);
    expect($result->items()[0]->furniture->manufacturer_id)->toBe($manufacturer->manufacturer_id);
});

test('filtering applies only to in-stock items of the requested category', function () {
    $category = FurnitureCategory::factory()->create();
    $otherCategory = FurnitureCategory::factory()->create();

    $expected = makeFurnitureWithSpec($category->category_id, ['surface' => 'Шпон']);
    makeFurnitureWithSpec($category->category_id, ['surface' => 'Шпон'], ['quantity' => 0]);
    makeFurnitureWithSpec($otherCategory->category_id, ['surface' => 'Шпон']);

    $result = app(QueryBusInterface::class)->ask(new GetFurnitureByCategoryQuery(
        categoryId: $category->category_id,
        filter: new FurnitureFilterData(surface: ['Шпон']),
    ));

    $ids = collect($result->items())->pluck('furniture.furniture_id')->all();

    expect($ids)->toBe([$expected->furniture_id]);
    expect($result->total())->toBe(1);
});

test('different filter selections are served from distinct cache entries', function () {
    $category = FurnitureCategory::factory()->create();

    $narrow = makeFurnitureWithSpec($category->category_id, ['width_cm' => 60]);
    $wide = makeFurnitureWithSpec($category->category_id, ['width_cm' => 200]);

    $bus = app(QueryBusInterface::class);

    $narrowResult = $bus->ask(new GetFurnitureByCategoryQuery(
        categoryId: $category->category_id,
        filter: new FurnitureFilterData(width_min: 50, width_max: 80),
    ));
    $wideResult = $bus->ask(new GetFurnitureByCategoryQuery(
        categoryId: $category->category_id,
        filter: new FurnitureFilterData(width_min: 150, width_max: 250),
    ));

    expect($narrowResult->items()[0]->furniture->furniture_id)->toBe($narrow->furniture_id);
    expect($wideResult->items()[0]->furniture->furniture_id)->toBe($wide->furniture_id);
});

test('filter options reflect ranges and enumerable values of the available category items', function () {
    $category = FurnitureCategory::factory()->create();
    $manufacturer = FurnitureManufacturer::factory()->create();

    makeFurnitureWithSpec(
        $category->category_id,
        ['width_cm' => 60, 'folding_type' => 'Книжка', 'surface' => 'Шпон'],
        ['price' => 1000.00, 'manufacturer_id' => $manufacturer->manufacturer_id],
    );
    makeFurnitureWithSpec(
        $category->category_id,
        ['width_cm' => 200, 'folding_type' => 'Дельфин', 'materials' => 'Массив дерева'],
        ['price' => 5000.00],
    );
    makeFurnitureWithSpec(
        $category->category_id,
        ['width_cm' => 999, 'folding_type' => 'Аккордеон'],
        ['price' => 99999.00, 'quantity' => 0],
    );

    $options = app(QueryBusInterface::class)->ask(
        new GetFurnitureFilterOptionsQuery(categoryId: $category->category_id),
    );

    expect($options)->toBeInstanceOf(FilterOptionsData::class);
    expect($options->price_range->min_cm)->toBe(1000.0);
    expect($options->price_range->max_cm)->toBe(5000.0);
    expect($options->widthRangeData->min_cm)->toBe(60.0);
    expect($options->widthRangeData->max_cm)->toBe(200.0);

    $foldingTypes = collect($options->folding_type)->pluck('value');
    expect($foldingTypes)->toContain('Книжка');
    expect($foldingTypes)->toContain('Дельфин');
    expect($foldingTypes)->not->toContain('Аккордеон');

    expect(collect($options->manufacture)->pluck('value'))->toContain((string) $manufacturer->manufacturer_id);
});

test('catalog page passes filter options with the active selection', function () {
    $category = FurnitureCategory::factory()->create();

    makeFurnitureWithSpec($category->category_id, ['width_cm' => 150], ['price' => 5000.00]);

    $this->get('/catalog/categories/'.$category->category_id.'?filter[width_min]=100')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('catalog/furniture')
            ->has('filterOptions')
            ->where('filterOptions.widthRangeData.min_cm', 150)
            ->where('activeFilter.width_min', 100)
            ->where('furniture.total', 1));
});

test('filter route renders the catalog page through inertia with filtered results', function () {
    $category = FurnitureCategory::factory()->create();

    $wide = makeFurnitureWithSpec($category->category_id, ['width_cm' => 150], ['price' => 5000.00]);
    makeFurnitureWithSpec($category->category_id, ['width_cm' => 60], ['price' => 1000.00]);

    $this->get('/catalog/categories/'.$category->category_id.'/furniture?filter[width_min]=100')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('catalog/furniture')
            ->has('filterOptions')
            ->where('furniture.total', 1)
            ->where('furniture.data.0.furniture.furniture_id', $wide->furniture_id)
            ->where('activeFilter.width_min', 100));
});
