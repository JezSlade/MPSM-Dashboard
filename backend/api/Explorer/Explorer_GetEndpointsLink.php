<?php
// Auto-generated endpoint: /Explorer/GetEndpointsLink [GET]
// Operation ID: Explorer/GetEndpointsLink

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Explorer/GetEndpointsLink');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
