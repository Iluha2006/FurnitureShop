<?php

namespace App\Http\Controllers;

use App\Interfaces\QueryBusInterface;
use App\Queries\GetCategoriesQuery;
use App\Queries\GetFurnitureByCategoryQuery;
use App\Queries\GetFurnitureQuery;
use App\Queries\GetFurnitureSpecificationsQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class FurnitureController extends Controller
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
    ) {
        //
    }

    /**
     * Show all in-stock furniture within a category.
     */
    public function index(int $categoryId, Request $request): Response
    {
        $categories = $this->queryBus->ask(new GetCategoriesQuery);

        $furniture = $this->queryBus->ask(
            new GetFurnitureByCategoryQuery(
                categoryId: $categoryId,
                perPage: $request->integer('perPage', 15),
                page: $request->integer('page', 1),
            ),
        );

        return Inertia::render('catalog/furniture', [
            'categoryId' => $categoryId,
            'categories' => $categories instanceof Collection ? $categories->values() : $categories,
            'furniture' => $furniture,
        ]);
    }

    /**
     * Show a single furniture item with its full details.
     * Characteristics are resolved through a separate repository.
     */
    public function show(int $furnitureId): Response
    {
        $furniture = $this->queryBus->ask(new GetFurnitureQuery(furnitureId: $furnitureId));

        $specifications = $this->queryBus->ask(
            new GetFurnitureSpecificationsQuery(furnitureId: $furnitureId),
        );

        return Inertia::render('catalog/furniture-show', [
            'furniture' => $furniture,
            'specifications' => $specifications,
        ]);
    }
}
