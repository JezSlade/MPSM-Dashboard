<?php
// Auto-generated endpoint: /SdsEvent/GetDeviceEvents [GET]
// Operation ID: SdsEvent/GetDeviceEvents

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/SdsEvent/GetDeviceEvents');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
