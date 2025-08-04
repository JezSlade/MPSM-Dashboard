<?php
// Auto-generated endpoint: /AlertLimit2/Dealer/UpdateProduct [PUT]
// Operation ID: AlertLimit2/Dealer/UpdateProduct

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('PUT', '/AlertLimit2/Dealer/UpdateProduct');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
