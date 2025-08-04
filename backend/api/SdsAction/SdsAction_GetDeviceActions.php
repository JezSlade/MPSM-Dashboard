<?php
// Auto-generated endpoint: /SdsAction/GetDeviceActions [GET]
// Operation ID: SdsAction/GetDeviceActions

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/SdsAction/GetDeviceActions');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
