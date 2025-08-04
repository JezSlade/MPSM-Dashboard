<?php
// Auto-generated endpoint: /CostCenter/Update [POST]
// Operation ID: CostCenter/Update

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/CostCenter/Update');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
