<?php
// Auto-generated endpoint: /SdsDevice/GetAssessTemplate [GET]
// Operation ID: SdsDevice/GetAssessTemplate

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/SdsDevice/GetAssessTemplate');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
