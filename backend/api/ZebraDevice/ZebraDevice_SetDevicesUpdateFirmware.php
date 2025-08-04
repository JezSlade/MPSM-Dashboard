<?php
// Auto-generated endpoint: /ZebraDevice/SetDevicesUpdateFirmware [POST]
// Operation ID: ZebraDevice/SetDevicesUpdateFirmware

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/ZebraDevice/SetDevicesUpdateFirmware');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
