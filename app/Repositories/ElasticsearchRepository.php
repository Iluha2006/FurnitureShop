<?php

namespace App\Repositories;

use App\Models\Furniture;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use RuntimeException;

class ElasticsearchRepository
{
    public function __construct(private readonly Client $elasticsearch) {}

    public function search(string $query = '', int $limit = 50): Collection
    {
        $query = trim($query);

        if ($query === '') {
            return new Collection;
        }

        return $this->buildCollection($this->searchOnElasticsearch($query, $limit));
    }

    private function searchOnElasticsearch(string $query, int $limit): array
    {
        $items = $this->elasticsearch->search([
            'index' => (new Furniture)->getSearchIndex(),
            'size' => $limit,
            'body' => [
                'query' => [
                    'multi_match' => [
                        'fields' => ['name^5', 'category_name^3', 'description', 'color'],
                        'query' => $query,
                    ],
                ],
            ],
        ]);

        if (! $items instanceof Elasticsearch) {
            throw new RuntimeException('Elasticsearch client is not in sync mode.');
        }

        return $items->asArray();
    }

    private function buildCollection(array $items): Collection
    {
        $ids = Arr::pluck($items['hits']['hits'], '_id');

        $result = Furniture::query()
            ->with(['images', 'manufacturer'])
            ->where('quantity', '>', 0)
            ->findMany($ids);

        return $result->sortBy(
            fn (Furniture $furniture): int|string|false => array_search($furniture->getKey(), $ids),
        )->values();
    }
}
