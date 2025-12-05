<?php
/**
 * Test API credentials and token retrieval
 * Access via: http://localhost/path/to/cms/api/test-api-credentials.php?secret=DEALER_API_2025
 */

require '../config.php';
require '../functions.php';

// Auth bypass
$bypassSecret = 'DEALER_API_2025';
$providedSecret = $_GET['secret'] ?? '';

if ($providedSecret !== $bypassSecret) {
    http_response_code(401);
    die('Unauthorized - provide secret parameter');
}

header('Content-Type: application/json');

$result = [
    'test' => 'API Credentials Test',
    'timestamp' => date('Y-m-d H:i:s'),
    'steps' => []
];

// Step 1: Check constants
$result['steps'][] = [
    'step' => 1,
    'name' => 'Check constants',
    'MPS_CLIENT_ID' => MPS_CLIENT_ID !== '' ? 'SET (' . strlen(MPS_CLIENT_ID) . ' chars)' : 'EMPTY',
    'MPS_CLIENT_SECRET' => MPS_CLIENT_SECRET !== '' ? 'SET (' . strlen(MPS_CLIENT_SECRET) . ' chars)' : 'EMPTY',
    'MPS_USERNAME' => MPS_USERNAME !== '' ? 'SET (' . strlen(MPS_USERNAME) . ' chars)' : 'EMPTY',
    'MPS_PASSWORD' => MPS_PASSWORD !== '' ? 'SET (' . strlen(MPS_PASSWORD) . ' chars)' : 'EMPTY',
    'DEFAULT_DEALER_CODE' => DEFAULT_DEALER_CODE,
    'DEFAULT_DEALER_ID' => DEFAULT_DEALER_ID,
    'status' => (MPS_CLIENT_ID !== '' && MPS_CLIENT_SECRET !== '') ? 'PASS' : 'FAIL'
];

// Step 2: Check curl extension
$result['steps'][] = [
    'step' => 2,
    'name' => 'Check curl extension',
    'curl_available' => function_exists('curl_init'),
    'status' => function_exists('curl_init') ? 'PASS' : 'FAIL'
];

if (!function_exists('curl_init')) {
    $result['error'] = 'curl extension not available in PHP';
    $result['overall_status'] = 'FAIL';
    echo json_encode($result, JSON_PRETTY_PRINT);
    exit;
}

// Step 3: Test OAuth token retrieval
try {
    $token = getMPSToken();
    $result['steps'][] = [
        'step' => 3,
        'name' => 'Get OAuth token',
        'token_obtained' => !empty($token),
        'token_length' => strlen($token ?? ''),
        'token_preview' => substr($token ?? '', 0, 20) . '...',
        'status' => !empty($token) ? 'PASS' : 'FAIL'
    ];
} catch (Exception $e) {
    $result['steps'][] = [
        'step' => 3,
        'name' => 'Get OAuth token',
        'status' => 'FAIL',
        'error' => $e->getMessage()
    ];
    $result['overall_status'] = 'FAIL';
    echo json_encode($result, JSON_PRETTY_PRINT);
    exit;
}

// Step 4: Test Device/List API call
try {
    $testParams = [
        'FilterDealerId' => DEFAULT_DEALER_ID,
        'FilterDealerCodes' => [DEFAULT_DEALER_CODE],
        'PageRows' => 5,
        'PageNumber' => 1,
        'SortColumn' => 'Id',
        'SortOrder' => 0,
    ];

    $testParams = array_filter($testParams, function($value) {
        return $value !== null;
    });

    $response = callMPSAPI('Device/List', $testParams);

    $devices = extractDevicesFromResponse($response);

    $result['steps'][] = [
        'step' => 4,
        'name' => 'Test Device/List API call',
        'api_response_received' => !empty($response),
        'response_is_array' => is_array($response),
        'response_keys' => is_array($response) ? array_keys($response) : null,
        'total_rows' => $response['TotalRows'] ?? null,
        'devices_extracted' => count($devices),
        'status' => (count($devices) > 0) ? 'PASS' : 'FAIL'
    ];

    if (count($devices) > 0) {
        $result['steps'][] = [
            'step' => 5,
            'name' => 'Sample device data',
            'first_device' => [
                'SerialNumber' => $devices[0]['SerialNumber'] ?? 'N/A',
                'CustomerCode' => $devices[0]['CustomerCode'] ?? 'N/A',
                'Model' => $devices[0]['Model'] ?? 'N/A',
                'IpAddress' => $devices[0]['IpAddress'] ?? 'N/A',
            ],
            'status' => 'INFO'
        ];
    }

} catch (Exception $e) {
    $result['steps'][] = [
        'step' => 4,
        'name' => 'Test Device/List API call',
        'status' => 'FAIL',
        'error' => $e->getMessage()
    ];
    $result['overall_status'] = 'FAIL';
    echo json_encode($result, JSON_PRETTY_PRINT);
    exit;
}

// Overall status
$allPass = true;
foreach ($result['steps'] as $step) {
    if (isset($step['status']) && $step['status'] === 'FAIL') {
        $allPass = false;
        break;
    }
}

$result['overall_status'] = $allPass ? 'PASS - API is working correctly' : 'FAIL - See steps above';

echo json_encode($result, JSON_PRETTY_PRINT);
