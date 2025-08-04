<?php
// Auto-generated endpoint: /Device/AssignOfficeToDevicesByDeviceId [PUT]
// Operation ID: Device/AssignOfficeToDevicesByDeviceId

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('PUT', '/Device/AssignOfficeToDevicesByDeviceId');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
