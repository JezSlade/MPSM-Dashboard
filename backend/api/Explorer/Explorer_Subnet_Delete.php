<?php
// Auto-generated endpoint: /Explorer/Subnet/Delete [DELETE]
// Operation ID: Explorer/Subnet/Delete

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('DELETE', '/Explorer/Subnet/Delete');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
