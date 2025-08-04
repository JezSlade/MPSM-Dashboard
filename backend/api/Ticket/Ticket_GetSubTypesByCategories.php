<?php
// Auto-generated endpoint: /Ticket/GetSubTypesByCategories [POST]
// Operation ID: Ticket/GetSubTypesByCategories

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/Ticket/GetSubTypesByCategories');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
