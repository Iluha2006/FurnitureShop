<?php

namespace App\Search;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Http\Promise\Promise;
use RuntimeException;

final class IndexManager
{
    public function __construct(private readonly Client $elasticsearch) {}

    public function exists(string $index): bool
    {
        return $this->sync($this->elasticsearch->indices()->exists(['index' => $index]))->asBool();
    }

    public function create(string $index): void
    {
        $this->sync($this->elasticsearch->indices()->create([
            'index' => $index,
            'body' => $this->definition(),
        ]));
    }

    public function createIfMissing(string $index): void
    {
        if ($this->exists($index)) {
            return;
        }

        try {
            $this->create($index);
        } catch (ClientResponseException $exception) {
            if ($this->isAlreadyExistsError($exception)) {
                return;
            }

            throw $exception;
        }
    }

    public function delete(string $index): void
    {
        $this->sync($this->elasticsearch->indices()->delete(['index' => $index]));
    }

    public function deleteIfExists(string $index): void
    {
        if (! $this->exists($index)) {
            return;
        }

        $this->delete($index);
    }

    /**
     * Index settings and mappings, in the shape Elasticsearch expects.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'settings' => [
                'number_of_shards' => 1,
                'number_of_replicas' => 0,
                'analysis' => [
                    'filter' => [
                        'russian_stemmer' => ['type' => 'stemmer', 'language' => 'russian'],
                    ],
                    'analyzer' => [
                        'russian_text' => [
                            'type' => 'custom',
                            'tokenizer' => 'standard',
                            'filter' => ['lowercase', 'russian_stemmer'],
                        ],
                    ],
                ],
            ],
            'mappings' => [
                'properties' => [
                    'furniture_id' => ['type' => 'long'],
                    'name' => [
                        'type' => 'text',
                        'analyzer' => 'russian_text',
                        'fields' => ['keyword' => ['type' => 'keyword', 'ignore_above' => 256]],
                    ],
                    'description' => ['type' => 'text', 'analyzer' => 'russian_text'],
                    'category_name' => ['type' => 'text', 'analyzer' => 'russian_text'],
                    'color' => ['type' => 'keyword'],
                    'price' => ['type' => 'double'],
                    'quantity' => ['type' => 'integer'],
                    'manufacturer_id' => ['type' => 'long'],
                    'category_id' => ['type' => 'long'],
                    'specifications_id' => ['type' => 'long'],
                    'created_at' => ['type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss||strict_date_optional_time||epoch_millis'],
                    'updated_at' => ['type' => 'date', 'format' => 'yyyy-MM-dd HH:mm:ss||strict_date_optional_time||epoch_millis'],
                ],
            ],
        ];
    }

    private function isAlreadyExistsError(ClientResponseException $exception): bool
    {
        $body = json_decode((string) $exception->getResponse()->getBody(), true);

        return ($body['error']['type'] ?? null) === 'resource_already_exists_exception';
    }

    private function sync(Elasticsearch|Promise $response): Elasticsearch
    {
        if (! $response instanceof Elasticsearch) {
            throw new RuntimeException('Elasticsearch client is not in sync mode.');
        }

        return $response;
    }
}
