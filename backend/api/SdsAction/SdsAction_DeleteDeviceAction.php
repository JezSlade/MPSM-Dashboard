<?php
// Auto-generated endpoint: /SdsAction/DeleteDeviceAction [DELETE]
// Operation ID: SdsAction/DeleteDeviceAction

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('DELETE', '/SdsAction/DeleteDeviceAction');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
