<?php
/**
 * Aggregate device details from multiple MPS Monitor endpoints.
 */

require '../config.php';
require '../functions.php';

requireAuth();

$deviceId = isset($_GET['deviceId']) ? trim((string) $_GET['deviceId']) : '';
$dealerId = isset($_GET['dealerId']) && $_GET['dealerId'] !== '' ? trim((string) $_GET['dealerId']) : DEFAULT_DEALER_ID;
$customerCode = isset($_GET['customerCode']) && $_GET['customerCode'] !== '' ? trim((string) $_GET['customerCode']) : DEFAULT_CUSTOMER_CODE;

if ($deviceId === '') {
    jsonError('Device ID is required', 400);
}

/**
 * Call the mps-api proxy for a given action.
 *
 * @param string $action
 * @param array<string, mixed> $params
 * @return array<string, mixed>
 */
function callBackendAction(string $action, array $params = []): array
{
    $payload = json_encode([
        'action' => $action,
        'params' => $params
    ]);

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => $payload,
            'timeout' => 30
        ]
    ]);

    $response = file_get_contents('https://mpsm.resolutionsbydesign.us/mps-api/query', false, $context);

    if ($response === false) {
        throw new Exception("Backend request failed for {$action}");
    }

    $decoded = json_decode($response, true);
    if (!is_array($decoded)) {
        throw new Exception("Invalid response for {$action}");
    }

    if (isset($decoded['success']) && !$decoded['success']) {
        $message = isset($decoded['error']) ? (string) $decoded['error'] : 'Unknown error';
        throw new Exception($message);
    }

    return $decoded;
}

$actions = [
    'Device/GetDeviceAdditionalInfos',
    'Device/GetDeviceGapInfos',
    'Device/GetSuppliesDetails',
    'Device/GetSuppliesDetailsSummary',
    'Device/GetLfpCounters',
    'SdsDevice/GetCounters',
    'SdsDevice/GetSupplyDetails'
];

$results = [];

foreach ($actions as $action) {
    try {
        $response = callBackendAction($action, ['id' => $deviceId]);
        $results[] = [
            'action' => $action,
            'success' => true,
            'data' => $response['data'] ?? null,
            'meta' => $response['meta'] ?? null
        ];
    } catch (Exception $e) {
        $results[] = [
            'action' => $action,
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

jsonSuccess([
    'deviceId' => $deviceId,
    'dealerId' => $dealerId,
    'customerCode' => $customerCode,
    'results' => $results
]);
