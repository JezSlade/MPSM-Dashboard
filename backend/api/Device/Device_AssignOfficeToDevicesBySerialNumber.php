<?php
// Auto-generated endpoint: /Device/AssignOfficeToDevicesBySerialNumber [PUT]
// Operation ID: Device/AssignOfficeToDevicesBySerialNumber

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('PUT', '/Device/AssignOfficeToDevicesBySerialNumber');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
