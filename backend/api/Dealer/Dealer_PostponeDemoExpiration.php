<?php
// Auto-generated endpoint: /Dealer/PostponeDemoExpiration [PUT]
// Operation ID: Dealer/PostponeDemoExpiration

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('PUT', '/Dealer/PostponeDemoExpiration');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
