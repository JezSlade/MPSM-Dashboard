<?php
// cards/DeviceCountersCard.php
declare(strict_types=1);

require_once __DIR__ . '/../includes/card_base.php';
require_once __DIR__ . '/../includes/env_parser.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/api_client.php';
require_once __DIR__ . '/../includes/table_helper.php';

// 1) Pull in global customer selection
$selectedCustomer = $_COOKIE['customer'] ?? null;

// Card settings
$cardKey = 'DeviceCountersCard';
$cacheEnabledFlag     = isset($_COOKIE["{$cardKey}_cache_enabled"])
    ? (bool)$_COOKIE["{$cardKey}_cache_enabled"]
    : true;
$indicatorDisplayFlag = isset($_COOKIE["{$cardKey}_indicator_display"])
    ? (bool)$_COOKIE["{$cardKey}_indicator_display"]
    : true;
$ttlMinutes = isset($_COOKIE["{$cardKey}_ttl_minutes"])
    ? max(1, (int)$_COOKIE["{$cardKey}_ttl_minutes"])
    : 5;

// 2) Build API request body
$body = [
    'DealerCode'    => DEALER_CODE,
    'CustomerCode'  => $selectedCustomer,  // scopes to selected customer
    'PageNumber'    => 1,
    'PageRows'      => 15,
    'SortColumn'    => 'ExternalIdentifier',
    'SortOrder'     => 'Asc',
];

// 3) Fetch data
$response = call_api('Device/List', $body);
$devices  = $response['items'] ?? $response['Result'] ?? [];

// 4) Normalize for table helper
$rows = array_map(function($d) {
    return [
        'ExternalIdentifier' => $d['ExternalIdentifier'] ?? '',
        'IP'                 => $d['IpAddress']          ?? '',
        'Model'              => $d['ModelName']          ?? '',
        'Warnings'           => implode(', ', $d['Warnings'] ?? []),
    ];
}, $devices);

// 5) Render the table
renderDataTable(
    $rows,
    [
      'columns'              => [
        'ExternalIdentifier' => 'Equipment ID',
        'IP'                 => 'IP Address',
        'Model'              => 'Model',
        'Warnings'           => 'Warnings',
      ],
      'sortable'             => true,
      'searchable'           => false,
      'rowsPerPage'          => 15,
      'rowSelectKey'         => null,      // no row-click here
      'rowSelectParam'       => null,
      'defaultVisibleColumns'=> ['ExternalIdentifier','Model','Warnings'],
    ]
);
