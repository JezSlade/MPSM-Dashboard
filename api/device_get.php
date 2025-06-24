<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/env_parser.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/cors.php'; send_cors_headers();
require_once __DIR__ . '/../includes/logger.php'; log_request();
require_once __DIR__ . '/../includes/api_client.php';

$input = json_decode(file_get_contents('php://input'), true) ?? [];

if (empty($input['Id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing Id']);
    exit;
}

try {
    $result = api_request('Device/Get', [
        'DealerCode' => DEALER_CODE,
        'Id'         => $input['Id'],
    ]);
    echo json_encode($result);
} catch (RuntimeException $e) {
    http_response_code(502);
    echo json_encode(['error' => $e->getMessage()]);
}
