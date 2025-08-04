<?php
// Auto-generated endpoint: /Device/GetDeviceAdditionalInfos [GET]
// Operation ID: Device/GetDeviceAdditionalInfos

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Device/GetDeviceAdditionalInfos');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
