<?php

namespace App\Http\Controllers;

use App\Interfaces\QueryBusInterface;
use App\Queries\GetCategoriesQuery;
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

        return Inertia::render('catalog/categories', [
            'categories' => $categories instanceof Collection ? $categories->values() : $categories,
        ]);
    }
}
