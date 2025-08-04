<?php
// Auto-generated endpoint: /azuread/UpdateAzureDomain [PUT]
// Operation ID: azuread/UpdateAzureDomain

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('PUT', '/azuread/UpdateAzureDomain');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
