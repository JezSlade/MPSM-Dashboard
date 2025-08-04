<?php
// Auto-generated endpoint: /azuread/GetChallengeUrlRedirect [GET]
// Operation ID: azuread/GetChallengeUrlRedirect

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/azuread/GetChallengeUrlRedirect');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
