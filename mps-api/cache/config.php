<?php
/**
 * Cache configuration for high-traffic MPS actions.
 *
 * Each entry maps an action (or endpoint) to cache behaviour.
 * - ttl: seconds to keep a response
 * - warm_params / warm: optional parameter sets for the background worker
 */
return [
    '__strategy' => getenv('MPS_CACHE_STRATEGY_DEFAULT') ?: 'readthrough',
    '__default_ttl' => getenv('MPS_CACHE_DEFAULT_TTL') ? (int) getenv('MPS_CACHE_DEFAULT_TTL') : 300,

    'Device/List' => [
        'ttl' => getenv('MPS_CACHE_DEVICE_TTL') ? (int) getenv('MPS_CACHE_DEVICE_TTL') : 300,
        'warm_params' => [
            ['pageRows' => 200, 'pageNumber' => 1],
            ['pageRows' => 200, 'pageNumber' => 2],
            ['pageRows' => 200, 'pageNumber' => 3],
        ],
    ],

    'Device/Deleted/List' => [
        'ttl' => 600,
        'warm_params' => [
            ['pageRows' => 200, 'pageNumber' => 1],
            ['pageRows' => 200, 'pageNumber' => 2],
        ],
    ],

    'ApiClient/List' => [
        'ttl' => 600,
        'warm_params' => [
            [],
        ],
    ],

    'CustomField/List' => [
        'ttl' => 900,
        'warm_params' => [
            [],
        ],
    ],

    'Product/GetBrands' => [
        'ttl' => 900,
        'warm_params' => [
            [],
        ],
    ],

    'Product/GetModels' => [
        'ttl' => 900,
        'warm_params' => [
            ['pageRows' => 200, 'pageNumber' => 1],
        ],
    ],

    'Customer/AlertSettings/Get' => [
        'ttl' => 900,
        'warm_params' => [
            [],
        ],
    ],
];

