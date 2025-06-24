<?php
// api/get_customers.php
// -------------------------------------------------------------------
// Fully inline, spec-correct Customer/GetCustomers endpoint.
// Matches all required parameters from AllEndpoints.json.
// -------------------------------------------------------------------

declare(strict_types=1);

// 1) Core includes
require_once __DIR__ . '/../includes/env_parser.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/cors.php';    send_cors_headers();
require_once __DIR__ . '/../includes/logger.php';  log_request();
require_once __DIR__ . '/../includes/api_client.php';

// 2) Read & validate inputs, with sensible defaults
$pageNumber  = isset($_GET['PageNumber']) && is_numeric($_GET['PageNumber'])
    ? (int) $_GET['PageNumber']
    : 1;

$pageRows    = isset($_GET['PageRows']) && is_numeric($_GET['PageRows'])
    ? (int) $_GET['PageRows']
    : 15;

// **Required** by the API spec (AllEndpoints.json):
// SortColumn (string) – which field to sort by.
// SortOrder  (string enum ['Asc','Desc']) – sort direction.
$sortColumn  = isset($_GET['SortColumn']) && $_GET['SortColumn'] !== ''
    ? $_GET['SortColumn']
    : 'Description';  // default field

$sortOrder   = isset($_GET['SortOrder']) && in_array($_GET['SortOrder'], ['Asc','Desc'], true)
    ? $_GET['SortOrder']
    : 'Asc';         // default direction

// 3) Build and send downstream request
try {
    $response = api_request('Customer/GetCustomers', [
        'DealerCode'  => DEALER_CODE,
        'PageNumber'  => $pageNumber,
        'PageRows'    => $pageRows,
        'SortColumn'  => $sortColumn,
        'SortOrder'   => $sortOrder,
    ]);

    // 4) Echo back exactly what we get
    echo json_encode($response);

} catch (RuntimeException $e) {
    // 5) On error, return a 502 with details
    http_response_code(502);
    echo json_encode([
        'error'   => 'Upstream request failed',
        'message' => $e->getMessage()
    ]);
}
