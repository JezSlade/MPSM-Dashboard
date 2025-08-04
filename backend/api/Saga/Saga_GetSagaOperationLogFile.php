<?php
// Auto-generated endpoint: /Saga/GetSagaOperationLogFile [POST]
// Operation ID: Saga/GetSagaOperationLogFile

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/Saga/GetSagaOperationLogFile');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
