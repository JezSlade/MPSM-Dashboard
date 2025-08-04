<?php
// Auto-generated endpoint: /Account/GetPsk2faData [GET]
// Operation ID: Account/GetPsk2faData

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Account/GetPsk2faData');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
