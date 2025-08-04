<?php
// Auto-generated endpoint: /SdsDevice/GetDeviceOperation [GET]
// Operation ID: SdsDevice/GetDeviceOperation

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/SdsDevice/GetDeviceOperation');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
