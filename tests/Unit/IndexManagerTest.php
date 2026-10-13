<?php

use App\Search\IndexManager;
use Elastic\Elasticsearch\ClientBuilder;

function indexManager(): IndexManager
{
    return new IndexManager(ClientBuilder::create()->setHosts(['http://127.0.0.1:9200'])->build());
}

it('declares the russian stemmer token filter and the analyzer built on it', function () {
    $analysis = indexManager()->definition()['settings']['analysis'];

    expect($analysis['filter']['russian_stemmer'])->toBe(['type' => 'stemmer', 'language' => 'russian'])
        ->and($analysis['analyzer']['russian_text']['filter'])->toBe(['lowercase', 'russian_stemmer']);
});

it('analyzes every searchable text field with the russian analyzer', function () {
    $properties = indexManager()->definition()['mappings']['properties'];

    $textFields = array_keys(array_filter(
        $properties,
        fn (array $property): bool => $property['type'] === 'text',
    ));

    expect($textFields)->not->toBeEmpty();

    foreach ($textFields as $field) {
        expect($properties[$field]['analyzer'] ?? null)->toBe('russian_text');
    }
});
