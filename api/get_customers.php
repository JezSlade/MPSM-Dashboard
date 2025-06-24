<?php
// api/get_customers.php
// -------------------------------------------------------------------
// Customer/GetCustomers endpoint—uses new api_client without header calls.
// -------------------------------------------------------------------

declare(strict_types=1);

require_once __DIR__ . '/../includes/env_parser.php';
require_once __DIR__ . '/../includes/plugin_auth.php'; verify_plugin_bearer();
require_once __DIR__ . '/../includes/cors.php';    send_cors_headers();
require_once __DIR__ . '/../includes/logger.php';  log_request();
require_once __DIR__ . '/../includes/api_client.php';

header('Content-Type: application/json');

// Read & validate inputs
$page  = isset($_GET['PageNumber']) && is_numeric($_GET['PageNumber']) ? (int)$_GET['PageNumber'] : 1;
$rows  = isset($_GET['PageRows'])   && is_numeric($_GET['PageRows'])   ? (int)$_GET['PageRows']   : 15;
$sortC = $_GET['SortColumn']  ?? 'Description';
$sortO = in_array($_GET['SortOrder'] ?? 'Asc', ['Asc','Desc'], true) ? $_GET['SortOrder'] : 'Asc';

// Delegate
try {
    $resp = api_request('Customer/GetCustomers', [
        'DealerCode' => DEALER_CODE,
        'PageNumber' => $page,
        'PageRows'   => $rows,
        'SortColumn' => $sortC,
        'SortOrder'  => $sortO,
    ]);

    http_response_code($resp['status']);
    echo json_encode($resp['data']);

} catch (RuntimeException $e) {
    http_response_code(502);
    echo json_encode([
        'error'   => 'Upstream request failed',
        'message' => $e->getMessage()
    ]);
}
