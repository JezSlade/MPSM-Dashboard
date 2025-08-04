<?php
// Auto-generated endpoint: /Account/SetPreferredDealer [POST]
// Operation ID: Account/SetPreferredDealer

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/Account/SetPreferredDealer');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
