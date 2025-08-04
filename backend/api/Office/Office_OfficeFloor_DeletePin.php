<?php
// Auto-generated endpoint: /Office/OfficeFloor/DeletePin [DELETE]
// Operation ID: Office/OfficeFloor/DeletePin

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('DELETE', '/Office/OfficeFloor/DeletePin');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
