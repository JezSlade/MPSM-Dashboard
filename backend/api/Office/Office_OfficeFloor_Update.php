<?php
// Auto-generated endpoint: /Office/OfficeFloor/Update [PUT]
// Operation ID: Office/OfficeFloor/Update

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('PUT', '/Office/OfficeFloor/Update');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
