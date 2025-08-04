<?php
// Auto-generated endpoint: /ApiClient/Account/List [GET]
// Operation ID: ApiClient/Account/List

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/ApiClient/Account/List');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
