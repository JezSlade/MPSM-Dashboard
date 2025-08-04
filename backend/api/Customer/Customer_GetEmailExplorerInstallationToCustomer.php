<?php
// Auto-generated endpoint: /Customer/GetEmailExplorerInstallationToCustomer [POST]
// Operation ID: Customer/GetEmailExplorerInstallationToCustomer

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/Customer/GetEmailExplorerInstallationToCustomer');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
