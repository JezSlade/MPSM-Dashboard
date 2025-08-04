<?php
// Auto-generated endpoint: /Saga/GetSagaOperationLogMessage [GET]
// Operation ID: Saga/GetSagaOperationLogMessage

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Saga/GetSagaOperationLogMessage');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
