<?php
// Auto-generated endpoint: /Dealer/DistributorSettings/Update [PUT]
// Operation ID: Dealer/DistributorSettings/Update

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('PUT', '/Dealer/DistributorSettings/Update');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
