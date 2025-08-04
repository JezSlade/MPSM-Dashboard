<?php
// Auto-generated endpoint: /okta/GetDomainRedirect [GET]
// Operation ID: okta/GetDomainRedirect

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/okta/GetDomainRedirect');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
