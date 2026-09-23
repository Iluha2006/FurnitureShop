<?php

namespace App\Http\Controllers;

use App\Data\Filter\FurnitureFilterData;
use App\Data\FurnitureCategoryData;
use App\Interfaces\QueryBusInterface;
use App\Models\FurnitureCategory;
use App\Queries\GetCategoriesQuery;
use App\Queries\GetFurnitureByCategoryQuery;
use App\Queries\GetFurnitureFilterOptionsQuery;
use App\Queries\GetFurnitureQuery;
use App\Queries\GetFurnitureSpecificationsQuery;
use App\Queries\GetRelatedFurnitureQuery;
use App\Queries\SearchFurnitureQuery;
use Elastic\Elasticsearch\Exception\ElasticsearchException;
use Elastic\Transport\Exception\TransportException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
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

    public function index(int $categoryId, Request $request): Response
    {
        return Inertia::render('catalog/furniture', $this->catalogPageProps($categoryId, $request));
    }

    public function filter(int $categoryId, Request $request): Response
    {
        return Inertia::render('catalog/furniture', $this->catalogPageProps($categoryId, $request));
    }

    public function show(int $furnitureId): Response
    {
        $furniture = $this->queryBus->ask(new GetFurnitureQuery(furnitureId: $furnitureId));

        $specifications = $this->queryBus->ask(
            new GetFurnitureSpecificationsQuery(furnitureId: $furnitureId),
        );

        $related = $this->queryBus->ask(
            new GetRelatedFurnitureQuery(
                categoryId: $furniture->furniture->category_id,
                excludeId: $furnitureId,
            ),
        );

        return Inertia::render('catalog/furniture-show', [
            'furniture' => $furniture,
            'specifications' => $specifications,
            'related' => $related,
        ]);
    }

    public function searchApi(Request $request): JsonResponse
    {
        $query = $request->string('q')->trim()->toString();

        if ($query === '') {
            return response()->json([
                'categories' => [],
                'furniture' => [],
            ]);
        }

        $categories = FurnitureCategory::query()
            ->where('name', 'ilike', "%{$query}%")
            ->orderBy('name')
            ->get()
            ->map(fn (FurnitureCategory $category) => FurnitureCategoryData::fromModel($category))
            ->all();

        try {
            $furniture = $this->queryBus->ask(new SearchFurnitureQuery($query));
        } catch (ElasticsearchException|TransportException) {
            $furniture = new Collection;
        }

        return response()->json([
            'categories' => $categories,
            'furniture' => $furniture,
        ]);
    }

    private function catalogPageProps(int $categoryId, Request $request): array
    {
        $categories = $this->queryBus->ask(new GetCategoriesQuery);

        return [
            'categoryId' => $categoryId,
            'categories' => $categories instanceof Collection ? $categories->values() : $categories,
            'furniture' => $this->catalogFurniture($categoryId, $request),
            'filterOptions' => $this->catalogFilterOptions($categoryId),
            'activeFilter' => $this->activeFilter($request),
        ];
    }

    private function catalogFurniture(int $categoryId, Request $request): LengthAwarePaginator
    {
        return $this->queryBus->ask(
            new GetFurnitureByCategoryQuery(
                categoryId: $categoryId,
                perPage: $request->integer('perPage', 15),
                page: $request->integer('page', 1),
                filter: $this->activeFilter($request),
            ),
        );
    }

    private function catalogFilterOptions(int $categoryId): mixed
    {
        return $this->queryBus->ask(new GetFurnitureFilterOptionsQuery(categoryId: $categoryId));
    }

    private function activeFilter(Request $request): FurnitureFilterData
    {
        return FurnitureFilterData::from($request->input('filter', []));
    }
}
