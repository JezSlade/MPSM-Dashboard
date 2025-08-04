<?php
// Auto-generated endpoint: /PanelMessageAlert/GetErrorCodes [POST]
// Operation ID: PanelMessageAlert/GetErrorCodes

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/PanelMessageAlert/GetErrorCodes');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
