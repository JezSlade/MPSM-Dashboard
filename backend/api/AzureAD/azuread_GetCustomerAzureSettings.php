<?php
// Auto-generated endpoint: /azuread/GetCustomerAzureSettings [GET]
// Operation ID: azuread/GetCustomerAzureSettings

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/azuread/GetCustomerAzureSettings');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
