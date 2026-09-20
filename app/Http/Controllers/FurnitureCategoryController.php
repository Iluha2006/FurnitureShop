<?php

namespace App\Http\Controllers;

use App\Interfaces\QueryBusInterface;
use App\Queries\GetCategoriesQuery;
use App\Queries\GetFurnitureHitsQuery;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class FurnitureCategoryController extends Controller
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
    ) {
        //
    }

    public function index(): Response
    {
        $categories = $this->queryBus->ask(new GetCategoriesQuery);
        $hits = $this->queryBus->ask(new GetFurnitureHitsQuery(limit: 14));

        return Inertia::render('catalog/categories', [
            'categories' => $categories instanceof Collection ? $categories->values() : $categories,
            'hits' => $hits,
        ]);
    }
}
