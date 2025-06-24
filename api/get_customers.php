<?php
// api/get_customers.php
declare(strict_types=1);

// 1) Core includes (env, auth, cors, logger)
require_once __DIR__ . '/../includes/env_parser.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/cors.php'; send_cors_headers();
require_once __DIR__ . '/../includes/logger.php'; log_request();

// 2) Shared HTTP helper
require_once __DIR__ . '/../includes/api_client.php';

// 3) Input
$page  = max(1, (int)($_GET['PageNumber']  ?? 1));
$rows  = max(1, (int)($_GET['PageRows']    ?? 15));

// 4) Delegate to shared client
try {
    $result = api_request('Customer/GetCustomers', [
        'DealerCode' => DEALER_CODE,
        'PageNumber' => $page,
        'PageRows'   => $rows,
    ]);
    echo json_encode($result);
} catch (RuntimeException $e) {
    http_response_code(502);
    echo json_encode(['error' => $e->getMessage()]);
}
