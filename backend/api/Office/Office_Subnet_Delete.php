<?php
// Auto-generated endpoint: /Office/Subnet/Delete [DELETE]
// Operation ID: Office/Subnet/Delete

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('DELETE', '/Office/Subnet/Delete');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
