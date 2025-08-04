<?php
// Auto-generated endpoint: /Office/OfficeFloor/List [GET]
// Operation ID: Office/OfficeFloor/List

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Office/OfficeFloor/List');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
