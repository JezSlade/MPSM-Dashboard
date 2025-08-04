<?php
// Auto-generated endpoint: /Explorer/AbortRequestSendLogs [PUT]
// Operation ID: Explorer/AbortRequestSendLogs

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('PUT', '/Explorer/AbortRequestSendLogs');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
