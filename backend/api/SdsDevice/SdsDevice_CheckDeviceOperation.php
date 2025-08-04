<?php
// Auto-generated endpoint: /SdsDevice/CheckDeviceOperation [PUT]
// Operation ID: SdsDevice/CheckDeviceOperation

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('PUT', '/SdsDevice/CheckDeviceOperation');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
