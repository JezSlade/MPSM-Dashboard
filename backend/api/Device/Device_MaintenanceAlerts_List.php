<?php
// Auto-generated endpoint: /Device/MaintenanceAlerts/List [GET]
// Operation ID: Device/MaintenanceAlerts/List

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Device/MaintenanceAlerts/List');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
