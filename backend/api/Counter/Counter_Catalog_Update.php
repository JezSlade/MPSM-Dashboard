<?php
// Auto-generated endpoint: /Counter/Catalog/Update [PUT]
// Operation ID: Counter/Catalog/Update

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('PUT', '/Counter/Catalog/Update');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
