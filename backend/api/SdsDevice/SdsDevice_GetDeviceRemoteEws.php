<?php
// Auto-generated endpoint: /SdsDevice/GetDeviceRemoteEws [GET]
// Operation ID: SdsDevice/GetDeviceRemoteEws

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/SdsDevice/GetDeviceRemoteEws');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
