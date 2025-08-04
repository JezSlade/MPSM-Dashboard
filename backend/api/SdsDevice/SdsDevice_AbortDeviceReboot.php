<?php
// Auto-generated endpoint: /SdsDevice/AbortDeviceReboot [POST]
// Operation ID: SdsDevice/AbortDeviceReboot

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/SdsDevice/AbortDeviceReboot');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
