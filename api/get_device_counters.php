<?php
require_once __DIR__ . '/../includes/api_functions.php';

// Read input: ?deviceId=XXX or ?externalIdentifier=YYY
$in = json_decode(file_get_contents('php://input'), true) ?: $_GET;
$deviceId = trim($in['deviceid'] ?? '');

if ($deviceId === '' && !empty($in['externalidentifier'])) {
    // use two-step lookup
    $dev = get_device_by_external($in['externalidentifier']);
    if (!$dev) {
        http_response_code(404);
        echo json_encode(['error'=>'Device not found by externalIdentifier'], JSON_PRETTY_PRINT);
        exit;
    }
    $deviceId = $dev['Id'];
}

if ($deviceId === '') {
    http_response_code(400);
    echo json_encode(['error'=>'Missing deviceId'], JSON_PRETTY_PRINT);
    exit;
}

// finally, pull counters
$counters = get_device_counters($deviceId);
echo json_encode([
  'Result'      => $counters,
  'IsValid'     => true,
  'Errors'      => [],
  'ReturnValue' => ''
], JSON_PRETTY_PRINT);
