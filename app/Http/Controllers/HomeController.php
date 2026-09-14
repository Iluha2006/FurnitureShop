<?php

namespace App\Http\Controllers;

use App\Interfaces\QueryBusInterface;
use App\Queries\GetAvailableFurnitureCountQuery;
use App\Queries\GetCategoriesQuery;
use App\Queries\GetFurnitureHitsQuery;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
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
        $productCount = $this->queryBus->ask(new GetAvailableFurnitureCountQuery);

        return Inertia::render('welcome', [
            'categories' => $categories instanceof Collection ? $categories->values() : $categories,
            'hits' => $hits,
            'productCount' => $productCount,
        ]);
    }
}
