<?php
// Auto-generated endpoint: /DealerProduct/Edit [PUT]
// Operation ID: DealerProduct/Edit

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('PUT', '/DealerProduct/Edit');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
