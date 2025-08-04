<?php
// Auto-generated endpoint: /Device/Deleted/ListByDealer [GET]
// Operation ID: Device/Deleted/ListByDealer

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Device/Deleted/ListByDealer');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
