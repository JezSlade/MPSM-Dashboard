<?php
// Auto-generated endpoint: /Explorer/V3/ReleaseNotes [GET]
// Operation ID: Explorer/V3/ReleaseNotes

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Explorer/V3/ReleaseNotes');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
