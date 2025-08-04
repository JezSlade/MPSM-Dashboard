<?php
// Auto-generated endpoint: /Explorer/WorkingDays/Update [POST]
// Operation ID: Explorer/WorkingDays/Update

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/Explorer/WorkingDays/Update');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
