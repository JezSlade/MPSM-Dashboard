<?php
// Auto-generated endpoint: /Counter/Catalog/Suggestions [GET]
// Operation ID: Counter/Catalog/Suggestions

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Counter/Catalog/Suggestions');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
