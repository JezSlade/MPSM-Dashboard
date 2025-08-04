<?php
// Auto-generated endpoint: /Dealer/DemoRequest/Get [GET]
// Operation ID: Dealer/DemoRequest/Get

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Dealer/DemoRequest/Get');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
