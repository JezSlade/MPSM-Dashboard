<?php
// api/get_customers.php
// -------------------------------------------------------------------
// Spec-correct Customer/GetCustomers endpoint with plugin auth guard.
// -------------------------------------------------------------------

declare(strict_types=1);

// 0) Load .env (must define PLUGIN_BEARER_TOKEN)
require_once __DIR__ . '/../includes/env_parser.php';

// 1) Plugin auth check
require_once __DIR__ . '/../includes/plugin_auth.php';
verify_plugin_bearer();

// 2) Core includes
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/cors.php';   send_cors_headers();
require_once __DIR__ . '/../includes/logger.php'; log_request();
require_once __DIR__ . '/../includes/api_client.php';

// 3) Sanity: ensure DEALER_CODE exists
if (!defined('DEALER_CODE') || constant('DEALER_CODE') === '') {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'error'   => 'Server misconfiguration',
        'message' => 'DEALER_CODE is not defined'
    ]);
    exit;
}

header('Content-Type: application/json');

// 4) Read & validate inputs
$pageNumber = (isset($_GET['PageNumber']) && is_numeric($_GET['PageNumber']))
    ? (int) $_GET['PageNumber'] : 1;

$pageRows = (isset($_GET['PageRows']) && is_numeric($_GET['PageRows']))
    ? (int) $_GET['PageRows'] : 15;

$sortColumn = (isset($_GET['SortColumn']) && $_GET['SortColumn'] !== '')
    ? $_GET['SortColumn'] : 'Description';

$sortOrder = (isset($_GET['SortOrder']) && in_array($_GET['SortOrder'], ['Asc','Desc'], true))
    ? $_GET['SortOrder'] : 'Asc';

try {
    // 5) Downstream API call
    $result = api_request('Customer/GetCustomers', [
        'DealerCode' => DEALER_CODE,
        'PageNumber' => $pageNumber,
        'PageRows'   => $pageRows,
        'SortColumn' => $sortColumn,
        'SortOrder'  => $sortOrder,
    ]);

    // 6) Echo the JSON response
    echo json_encode($result);

} catch (RuntimeException $e) {
    http_response_code(502);
    echo json_encode([
        'error'   => 'Upstream request failed',
        'message' => $e->getMessage()
    ]);
}
