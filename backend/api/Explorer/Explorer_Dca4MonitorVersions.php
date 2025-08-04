<?php
// Auto-generated endpoint: /Explorer/Dca4MonitorVersions [GET]
// Operation ID: Explorer/Dca4MonitorVersions

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Explorer/Dca4MonitorVersions');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
