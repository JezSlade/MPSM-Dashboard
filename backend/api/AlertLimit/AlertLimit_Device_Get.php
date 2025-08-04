<?php
// Auto-generated endpoint: /AlertLimit/Device/Get [GET]
// Operation ID: AlertLimit/Device/Get

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/AlertLimit/Device/Get');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
