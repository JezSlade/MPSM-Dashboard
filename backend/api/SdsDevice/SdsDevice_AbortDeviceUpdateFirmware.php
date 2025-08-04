<?php
// Auto-generated endpoint: /SdsDevice/AbortDeviceUpdateFirmware [POST]
// Operation ID: SdsDevice/AbortDeviceUpdateFirmware

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/SdsDevice/AbortDeviceUpdateFirmware');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
