<?php
// Auto-generated endpoint: /Dealer/Customizations/Update [PUT]
// Operation ID: Dealer/Customizations/Update

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('PUT', '/Dealer/Customizations/Update');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
