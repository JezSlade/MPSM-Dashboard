<?php
// Auto-generated endpoint: /SdsDevice/HideEarlyReplacement [PUT]
// Operation ID: SdsDevice/HideEarlyReplacement

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('PUT', '/SdsDevice/HideEarlyReplacement');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
