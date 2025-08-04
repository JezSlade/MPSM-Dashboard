<?php
// Auto-generated endpoint: /okta/CreateOktaDomain [POST]
// Operation ID: okta/CreateOktaDomain

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/okta/CreateOktaDomain');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
