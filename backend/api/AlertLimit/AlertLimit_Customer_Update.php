<?php
// Auto-generated endpoint: /AlertLimit/Customer/Update [PUT]
// Operation ID: AlertLimit/Customer/Update

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('PUT', '/AlertLimit/Customer/Update');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
