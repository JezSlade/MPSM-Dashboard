<?php
// Auto-generated endpoint: /Device/GetLfpCounters [GET]
// Operation ID: Device/GetLfpCounters

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Device/GetLfpCounters');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
