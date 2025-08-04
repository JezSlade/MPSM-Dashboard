<?php
// Auto-generated endpoint: /Device/GetDeviceGapInfos [GET]
// Operation ID: Device/GetDeviceGapInfos

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Device/GetDeviceGapInfos');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
