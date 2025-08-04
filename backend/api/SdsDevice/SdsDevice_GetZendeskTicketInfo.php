<?php
// Auto-generated endpoint: /SdsDevice/GetZendeskTicketInfo [GET]
// Operation ID: SdsDevice/GetZendeskTicketInfo

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/SdsDevice/GetZendeskTicketInfo');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
