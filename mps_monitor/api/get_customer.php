<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/logger.php';
/**
 * get_customer.php
 * Retrieves a list of all customers from MPS Monitor.
 *
 * ✅ Fully aligned with Swagger and SDK
 * ✅ POST method with correct body
 */

$method = 'POST';
$path = 'Customer/GetCustomers';
$useCache = true; // Enables caching

$body = [
    "DealerCode"    => "NY06AGDWUQ",
    "Code"          => null,
    "HasHpSds"      => null,
    "FilterText"    => null,
    "PageNumber"    => 1,
    "PageRows"      => 2147483647,
    "SortColumn"    => "Id",
    "SortOrder"     => 0
];

require __DIR__ . '/../includes/api_bootstrap.php';

// api_bootstrap.php will:
// - handle token loading and refresh
// - invoke the API using $method, $path, $body
// - emit the JSON response to client
?>
