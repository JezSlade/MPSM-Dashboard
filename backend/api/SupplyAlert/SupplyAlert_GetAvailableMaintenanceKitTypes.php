<?php
// Auto-generated endpoint: /SupplyAlert/GetAvailableMaintenanceKitTypes [GET]
// Operation ID: SupplyAlert/GetAvailableMaintenanceKitTypes

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/SupplyAlert/GetAvailableMaintenanceKitTypes');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
