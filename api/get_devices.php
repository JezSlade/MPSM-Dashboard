<?php
// /api/get_devices.php
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../includes/api_functions.php';
    $config = parse_env_file(__DIR__ . '/../.env');

    $payload = [
        'DealerCode' => $config['DEALER_CODE'] ?? '',
        'PageNumber' => 1,
        'PageRows'   => 100,
    ];
    $result = call_api($config, 'POST', 'Device/Get', $payload);

    echo json_encode(['devices' => $result['Result'] ?? []]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
