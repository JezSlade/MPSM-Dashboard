<?php
// Auto-generated endpoint: /Dealer/SaveDealerTagsHierarchy [POST]
// Operation ID: Dealer/SaveDealerTagsHierarchy

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/Dealer/SaveDealerTagsHierarchy');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
