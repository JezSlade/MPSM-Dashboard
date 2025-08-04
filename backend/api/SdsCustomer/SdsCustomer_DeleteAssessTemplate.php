<?php
// Auto-generated endpoint: /SdsCustomer/DeleteAssessTemplate [DELETE]
// Operation ID: SdsCustomer/DeleteAssessTemplate

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('DELETE', '/SdsCustomer/DeleteAssessTemplate');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
