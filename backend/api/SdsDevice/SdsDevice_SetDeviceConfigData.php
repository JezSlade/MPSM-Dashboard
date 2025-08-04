<?php
// Auto-generated endpoint: /SdsDevice/SetDeviceConfigData [POST]
// Operation ID: SdsDevice/SetDeviceConfigData

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/SdsDevice/SetDeviceConfigData');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
