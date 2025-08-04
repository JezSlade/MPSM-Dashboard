<?php
// Auto-generated endpoint: /SdsConnector/Associate [POST]
// Operation ID: SdsConnector/Associate

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/SdsConnector/Associate');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
