<?php
// Auto-generated endpoint: /Integrations/GetJoinedCustomers [GET]
// Operation ID: Integrations/GetJoinedCustomers

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Integrations/GetJoinedCustomers');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
