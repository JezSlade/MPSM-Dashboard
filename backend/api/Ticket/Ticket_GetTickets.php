<?php
// Auto-generated endpoint: /Ticket/GetTickets [GET]
// Operation ID: Ticket/GetTickets

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/Ticket/GetTickets');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
