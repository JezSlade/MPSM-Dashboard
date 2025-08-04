<?php
// Auto-generated endpoint: /Account/ResetPassword [POST]
// Operation ID: Account/ResetPassword

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/Account/ResetPassword');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
