<?php

return [
    'enabled' => (bool) env('ELASTICSEARCH_ENABLED', false),

    'hosts' => array_map(
        static fn (string $host): string => str_contains($host, '://')
            ? $host
            : 'http://'.$host,
        array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('ELASTICSEARCH_HOSTS', env('ELASTICSEARCH_HOST', 'http://127.0.0.1:9200'))),
        ))),
    ),
];
