<?php

namespace App\Console\Commands;

use App\Models\Furniture;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Http\Promise\Promise;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use RuntimeException;

#[Signature('search:reindex {index=furniture : Elasticsearch index to rebuild}')]
#[Description('Drop, recreate and re-fill the Elasticsearch index from the furniture table')]
class ReindexCommand extends Command
{
    private const BULK_SIZE = 500;

    public function handle(Client $elasticsearch): int
    {
        $index = (string) $this->argument('index');

        if ($this->confirm("Rebuild the Elasticsearch index [{$index}]?", true)) {
            $this->deleteIndexIfExists($elasticsearch, $index);

            $this->createIndex($elasticsearch, $index);
        }

        $total = $this->indexAllFurniture($elasticsearch, $index);

        $this->info("Indexed {$total} furniture documents into [{$index}].");

        return self::SUCCESS;
    }

    private function deleteIndexIfExists(Client $client, string $index): void
    {
        $exists = $this->sync($client->indices()->exists(['index' => $index]))->asBool();

        if (! $exists) {
            return;
        }

        $this->sync($client->indices()->delete(['index' => $index]));

        $this->info("Deleted existing index [{$index}].");
    }

    private function createIndex(Client $client, string $index): void
    {
        $this->sync($client->indices()->create([
            'index' => $index,
            'body' => [
                'settings' => [
                    'number_of_shards' => 1,
                    'number_of_replicas' => 0,
                ],
                'mappings' => [
                    'properties' => [
                        'furniture_id' => ['type' => 'long'],
                        'name' => [
                            'type' => 'text',
                            'fields' => ['keyword' => ['type' => 'keyword', 'ignore_above' => 256]],
                        ],
                        'description' => ['type' => 'text'],
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
            ],
        ]));

        $this->info("Index [{$index}] created with mapping.");
    }

    private function indexAllFurniture(Client $client, string $index): int
    {
        $bar = $this->output->createProgressBar(Furniture::query()->count());
        $bar->start();

        $position = 0;
        $failed = 0;

        Furniture::query()->chunkById(
            self::BULK_SIZE,
            function (Collection $chunk) use ($client, $index, $bar, &$position, &$failed): void {
                $body = [];

                foreach ($chunk as $furniture) {
                    $body[] = ['index' => ['_index' => $index, '_id' => $furniture->getKey()]];
                    $body[] = $furniture->toSearchArray();
                }

                $failed += $this->bulkIndex($client, $body);
                $position += $chunk->count();

                $bar->advance($chunk->count());
            },
        );

        $bar->finish();
        $this->newLine();

        if ($failed > 0) {
            $this->warn("{$failed} documents failed to index.");
        }

        return $position - $failed;
    }

    /**
     * @param  list<array<mixed>>  $body
     */
    private function bulkIndex(Client $client, array $body): int
    {
        if ($body === []) {
            return 0;
        }

        $result = $this->sync($client->bulk(['body' => $body]))->asArray();

        if (($result['errors'] ?? false) === false) {
            return 0;
        }

        $failed = 0;
        foreach ($result['items'] ?? [] as $item) {
            if (isset($item['index']['error'])) {
                $failed++;
                $this->line(sprintf(
                    '  Failed id=%s: %s',
                    $item['index']['_id'] ?? '?',
                    $item['index']['error']['reason'] ?? 'unknown error',
                ));
            }
        }

        return $failed;
    }

    private function sync(Elasticsearch|Promise $response): Elasticsearch
    {
        if (! $response instanceof Elasticsearch) {
            throw new RuntimeException('Elasticsearch client is not in sync mode.');
        }

        return $response;
    }
}
