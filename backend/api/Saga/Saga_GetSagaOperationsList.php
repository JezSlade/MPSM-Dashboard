<?php
// Auto-generated endpoint: /Saga/GetSagaOperationsList [POST]
// Operation ID: Saga/GetSagaOperationsList

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/Saga/GetSagaOperationsList');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
