<?php
// Auto-generated endpoint: /WhiteLabel/GetWhiteLabelCustomizationByUrl [GET]
// Operation ID: WhiteLabel/GetWhiteLabelCustomizationByUrl

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('GET', '/WhiteLabel/GetWhiteLabelCustomizationByUrl');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
