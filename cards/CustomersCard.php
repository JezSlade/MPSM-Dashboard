<?php
// cards/CustomersCard.php — Preserve customer selection and keep all functionality
declare(strict_types=1);

require_once __DIR__ . '/../includes/card_base.php';
require_once __DIR__ . '/../includes/env_parser.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/api_client.php';
require_once __DIR__ . '/../includes/table_helper.php';

// 1) Read the current selection up-front: cookie → querystring → default ''
$selected = $_COOKIE['customer'] 
          ?? ($_GET['customer'] ?? '');

// Card identifier
$cardKey = 'CustomersCard';

// Read card settings from cookies (or defaults)
$cacheEnabledFlag     = isset($_COOKIE["{$cardKey}_cache_enabled"])     ? (bool)$_COOKIE["{$cardKey}_cache_enabled"]     : true;
$indicatorDisplayFlag = isset($_COOKIE["{$cardKey}_indicator_display"]) ? (bool)$_COOKIE["{$cardKey}_indicator_display"] : true;
$ttlMinutes           = isset($_COOKIE["{$cardKey}_ttl_minutes"])       ? max(1,(int)$_COOKIE["{$cardKey}_ttl_minutes"]) : 5;
$cacheTTL             = $ttlMinutes * 60;

// 2) Fetch all customers via API
$response = call_api('Customer/GetCustomers', [
    'DealerCode' => DEALER_CODE,
    'PageNumber' => 1,
    'PageRows'   => PHP_INT_MAX,
    'SortColumn' => 'Description',
    'SortOrder'  => 'Asc',
]);
$customers = $response['items'] ?? $response['Result'] ?? [];

// 3) Prepare data for renderDataTable
$rows = array_map(function($c) {
    return [
        'CustomerCode' => $c['CustomerCode'] ?? '',
        'Description'  => $c['Description']  ?? '',
    ];
}, $customers);

// 4) Render using the table helper
renderDataTable(
    $rows,
    [
      'columns'        => ['CustomerCode'=>'Customer Code','Description'=>'Description'],
      'sortable'       => false,
      'searchable'     => true,
      'rowsPerPage'    => 999,
      'rowSelectKey'   => 'CustomerCode',
      'rowSelectParam' => 'customer',
      // Only show Description column by default
      'defaultVisibleColumns' => ['Description'],
      // Pre-select the clicked customer in the helper (highlights row)
      'selectedValue'  => $selected
    ]
);
