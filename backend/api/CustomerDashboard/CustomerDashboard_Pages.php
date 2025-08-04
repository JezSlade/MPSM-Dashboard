<?php
// Auto-generated endpoint: /CustomerDashboard/Pages [GET]
// Operation ID: CustomerDashboard/Pages

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/CustomerDashboard/Pages');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
