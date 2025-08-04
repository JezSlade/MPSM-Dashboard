<?php
/**
 * CurationMap.php
 * Maps API endpoints to local cache keys and optional formatters
 */

return [
    'Account/GetProfile' => [
        'method' => 'GET',
        'query' => [],
        'cache_key' => 'account_profile',
        'formatter' => 'DataFormatter::format'
    ],
    'Account/GetAccounts' => [
        'method' => 'POST',
        'payload' => [],
        'cache_key' => 'account_list',
        'formatter' => 'DataFormatter::format'
    ]
    // Add more as needed
];
