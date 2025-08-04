<?php
// Auto-generated endpoint: /Explorer/Configuration/Get [GET]
// Operation ID: Explorer/Configuration/Get

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Explorer/Configuration/Get');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
