<?php
// Auto-generated endpoint: /SdsAction/GetDeviceAction [GET]
// Operation ID: SdsAction/GetDeviceAction

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/SdsAction/GetDeviceAction');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
