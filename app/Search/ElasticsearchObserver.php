<?php

namespace App\Search;

use Elastic\Elasticsearch\Client;
use Illuminate\Database\Eloquent\Model;

class ElasticsearchObserver
{
    public function __construct(
        private readonly Client $elasticsearch,
        private readonly IndexManager $indexManager,
    ) {
        //
    }

    /**
     * Keep the index in step with a saved model.
     */
    public function saved(Model&Indexable $model): void
    {
        $index = $model->getSearchIndex();

        $this->indexManager->createIfMissing($index);

        $this->elasticsearch->index([
            'index' => $index,
            'id' => $model->getKey(),
            'body' => $model->toSearchArray(),
        ]);
    }

    /**
     * Drop a deleted model from the index.
     */
    public function deleted(Model&Indexable $model): void
    {
        $this->elasticsearch->delete([
            'index' => $model->getSearchIndex(),
            'id' => $model->getKey(),
        ]);
    }
}
