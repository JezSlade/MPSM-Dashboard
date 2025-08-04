<?php
// Auto-generated endpoint: /SdsEvent/GetDeviceEvent [GET]
// Operation ID: SdsEvent/GetDeviceEvent

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/SdsEvent/GetDeviceEvent');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
