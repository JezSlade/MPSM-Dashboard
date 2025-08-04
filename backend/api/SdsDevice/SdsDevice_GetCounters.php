<?php
// Auto-generated endpoint: /SdsDevice/GetCounters [GET]
// Operation ID: SdsDevice/GetCounters

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/SdsDevice/GetCounters');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
