<?php
// Auto-generated endpoint: /PanelMessageAlert/Update [PUT]
// Operation ID: PanelMessageAlert/Update

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('PUT', '/PanelMessageAlert/Update');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
