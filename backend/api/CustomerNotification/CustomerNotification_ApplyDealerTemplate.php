<?php
// Auto-generated endpoint: /CustomerNotification/ApplyDealerTemplate [PUT]
// Operation ID: CustomerNotification/ApplyDealerTemplate

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('PUT', '/CustomerNotification/ApplyDealerTemplate');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
