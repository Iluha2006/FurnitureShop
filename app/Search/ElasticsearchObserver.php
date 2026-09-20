<?php

namespace App\Search;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Illuminate\Database\Eloquent\Model;

class ElasticsearchObserver
{
    public function __construct(private readonly Client $elasticsearch) {}

    /**
     * @param  Model&Searchable  $model
     */
    public function saved(Model $model): Elasticsearch
    {
        return $this->elasticsearch->index([
            'index' => $model->getSearchIndex(),
            'id' => $model->getKey(),
            'body' => $model->toSearchArray(),
        ]);
    }

    /**
     * @param  Model&Searchable  $model
     */
    public function deleted(Model $model): Elasticsearch
    {
        return $this->elasticsearch->delete([
            'index' => $model->getSearchIndex(),
            'id' => $model->getKey(),
        ]);
    }
}
