<?php
// Auto-generated endpoint: /AlertLimit/Customer/Product/Delete [POST]
// Operation ID: AlertLimit/Customer/Product/Delete

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/AlertLimit/Customer/Product/Delete');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
