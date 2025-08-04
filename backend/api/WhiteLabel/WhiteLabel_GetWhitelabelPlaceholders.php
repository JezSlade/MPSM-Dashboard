<?php
// Auto-generated endpoint: /WhiteLabel/GetWhitelabelPlaceholders [GET]
// Operation ID: WhiteLabel/GetWhitelabelPlaceholders

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/WhiteLabel/GetWhitelabelPlaceholders');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
