<?php
// Auto-generated endpoint: /ApiClient/Account/Update [PUT]
// Operation ID: ApiClient/Account/Update

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('PUT', '/ApiClient/Account/Update');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
