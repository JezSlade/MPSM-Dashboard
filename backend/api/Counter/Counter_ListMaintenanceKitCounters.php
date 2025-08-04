<?php
// Auto-generated endpoint: /Counter/ListMaintenanceKitCounters [GET]
// Operation ID: Counter/ListMaintenanceKitCounters

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Counter/ListMaintenanceKitCounters');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
