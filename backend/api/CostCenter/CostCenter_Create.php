<?php
// Auto-generated endpoint: /CostCenter/Create [POST]
// Operation ID: CostCenter/Create

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/CostCenter/Create');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
