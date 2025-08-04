<?php
// Auto-generated endpoint: /AlertLimit/Customer/Delete [DELETE]
// Operation ID: AlertLimit/Customer/Delete

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('DELETE', '/AlertLimit/Customer/Delete');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
