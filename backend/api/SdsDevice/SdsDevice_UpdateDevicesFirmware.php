<?php
// Auto-generated endpoint: /SdsDevice/UpdateDevicesFirmware [PUT]
// Operation ID: SdsDevice/UpdateDevicesFirmware

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('PUT', '/SdsDevice/UpdateDevicesFirmware');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
