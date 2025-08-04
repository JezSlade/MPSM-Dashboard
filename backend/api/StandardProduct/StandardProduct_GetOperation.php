<?php
// Auto-generated endpoint: /StandardProduct/GetOperation [GET]
// Operation ID: StandardProduct/GetOperation

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/StandardProduct/GetOperation');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
