<?php

use App\Interfaces\QueryBusInterface;
use App\Models\FurnitureCategory;
use App\Queries\GetCategoriesQuery;
use Database\Seeders\FurnitureCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('category seeder stores the image url', function () {
    $this->seed(FurnitureCategorySeeder::class);

    $category = FurnitureCategory::query()->where('name', 'Диваны')->sole();

    expect($category->image_url)->not->toBeNull()->toStartWith('https://');
});

test('every seeded category has an image url', function () {
    $this->seed(FurnitureCategorySeeder::class);

    $withoutImage = FurnitureCategory::query()
        ->whereNull('image_url')
        ->pluck('name')
        ->all();

    expect($withoutImage)->toBe([]);
});

test('categories query exposes the image url', function () {
    $this->seed(FurnitureCategorySeeder::class);

    $categories = app(QueryBusInterface::class)->ask(new GetCategoriesQuery);

    $sofa = $categories->firstWhere('name', 'Диваны');

    expect($sofa->image_url)->toBe(
        FurnitureCategory::query()->where('name', 'Диваны')->value('image_url'),
    );
});
