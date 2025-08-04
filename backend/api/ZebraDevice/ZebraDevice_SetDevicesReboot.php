<?php
// Auto-generated endpoint: /ZebraDevice/SetDevicesReboot [POST]
// Operation ID: ZebraDevice/SetDevicesReboot

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/ZebraDevice/SetDevicesReboot');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
