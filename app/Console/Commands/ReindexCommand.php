<?php

namespace App\Console\Commands;

use App\Models\Furniture;
use App\Search\IndexManager;
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

    public function handle(Client $elasticsearch, IndexManager $indexManager): int
    {
        $index = (string) $this->argument('index');

        if ($this->confirm("Rebuild the Elasticsearch index [{$index}]?", true)) {
            if ($indexManager->exists($index)) {
                $indexManager->delete($index);

                $this->info("Deleted existing index [{$index}].");
            }

            $indexManager->create($index);

            $this->info("Index [{$index}] created with mapping.");
        }

        $total = $this->indexAllFurniture($elasticsearch, $index);

        $this->info("Indexed {$total} furniture documents into [{$index}].");

        return self::SUCCESS;
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
     * Send one bulk request and return how many documents were indexed.
     *
     * @param  array<int, array<string, mixed>>  $body
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
