<?php
// Auto-generated endpoint: /SdsCustomer/GetNewAssessTemplate [GET]
// Operation ID: SdsCustomer/GetNewAssessTemplate

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/SdsCustomer/GetNewAssessTemplate');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
