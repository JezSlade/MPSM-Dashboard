<?php
// Auto-generated endpoint: /SdsAction/ChangeDeviceActionStatus [POST]
// Operation ID: SdsAction/ChangeDeviceActionStatus

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/SdsAction/ChangeDeviceActionStatus');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
