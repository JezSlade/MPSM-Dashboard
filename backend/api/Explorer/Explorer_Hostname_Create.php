<?php
// Auto-generated endpoint: /Explorer/Hostname/Create [POST]
// Operation ID: Explorer/Hostname/Create

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/Explorer/Hostname/Create');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
