<?php
// Auto-generated endpoint: /Saga/GetSagaOperationLogList [POST]
// Operation ID: Saga/GetSagaOperationLogList

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/Saga/GetSagaOperationLogList');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
