<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/env_parser.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/cors.php'; send_cors_headers();
require_once __DIR__ . '/../includes/logger.php'; log_request();
require_once __DIR__ . '/../includes/api_client.php';

$page   = max(1, (int)($_GET['PageNumber'] ?? 1));
$rows   = max(1, (int)($_GET['PageRows']   ?? 15));
$customer = $_GET['CustomerCode'] ?? '';

try {
    $result = api_request('SupplyAlert/List', [
        'DealerCode'   => DEALER_CODE,
        'CustomerCode' => $customer,
        'PageNumber'   => $page,
        'PageRows'     => $rows,
    ]);
    echo json_encode($result);
} catch (RuntimeException $e) {
    http_response_code(502);
    echo json_encode(['error' => $e->getMessage()]);
}
