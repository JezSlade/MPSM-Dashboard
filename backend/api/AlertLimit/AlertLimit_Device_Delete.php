<?php
// Auto-generated endpoint: /AlertLimit/Device/Delete [DELETE]
// Operation ID: AlertLimit/Device/Delete

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('DELETE', '/AlertLimit/Device/Delete');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
