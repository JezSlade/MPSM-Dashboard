<?php
// Auto-generated endpoint: /AlertLimit/Dealer/Get [GET]
// Operation ID: AlertLimit/Dealer/Get

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/AlertLimit/Dealer/Get');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
