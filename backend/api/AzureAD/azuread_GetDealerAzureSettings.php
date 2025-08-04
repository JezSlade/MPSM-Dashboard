<?php
// Auto-generated endpoint: /azuread/GetDealerAzureSettings [GET]
// Operation ID: azuread/GetDealerAzureSettings

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/azuread/GetDealerAzureSettings');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
