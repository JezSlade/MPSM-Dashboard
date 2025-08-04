<?php
// Auto-generated endpoint: /Dealer/AlertSettings/Update [PUT]
// Operation ID: Dealer/AlertSettings/Update

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('PUT', '/Dealer/AlertSettings/Update');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
