<?php
// Auto-generated endpoint: /okta/GetCustomerOktaSettings [GET]
// Operation ID: okta/GetCustomerOktaSettings

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/okta/GetCustomerOktaSettings');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
