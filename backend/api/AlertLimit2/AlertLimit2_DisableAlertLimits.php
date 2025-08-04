<?php
// Auto-generated endpoint: /AlertLimit2/DisableAlertLimits [POST]
// Operation ID: AlertLimit2/DisableAlertLimits

require_once __DIR__ . '/../../core/ApiCaller.php';

try {
    $result = ApiCaller::request('POST', '/AlertLimit2/DisableAlertLimits');
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
