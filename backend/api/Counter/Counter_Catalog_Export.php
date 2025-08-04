<?php
// Auto-generated endpoint: /Counter/Catalog/Export [POST]
// Operation ID: Counter/Catalog/Export

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/Counter/Catalog/Export');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
