<?php
// Auto-generated endpoint: /Communication/GetPortalReleaseNotes [GET]
// Operation ID: Communication/GetPortalReleaseNotes

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Communication/GetPortalReleaseNotes');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
