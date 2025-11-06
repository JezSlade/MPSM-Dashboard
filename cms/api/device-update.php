<?php
/**
 * Device Lifecycle - Update
 * Updates an existing device via mps-api proxy.
 */

require '../config.php';
require '../functions.php';

requireAuth();
ensureDeviceCrudEnabled();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    jsonError('Invalid JSON payload', 400);
}

$deviceId = trim($data['deviceId'] ?? '');
if ($deviceId === '') {
    jsonError('deviceId is required', 422);
}

$payload = ['DeviceId' => $deviceId];

$booleanKeys = [
    'isManaged' => 'IsManaged',
    'isGenerateAlert' => 'IsGenerateAlert',
    'manageRepeatedAlerts' => 'ManageRepeatedAlerts',
];

foreach ($booleanKeys as $inputKey => $apiKey) {
    if (array_key_exists($inputKey, $data)) {
        $payload[$apiKey] = (bool)$data[$inputKey];
    }
}

$stringMap = [
    'newProductBrand' => 'NewProductBrand',
    'newProductModel' => 'NewProductModel',
    'newStandardModelId' => 'NewStandardModelId',
    'newStandardModelClean' => 'NewStandardModelClean',
    'projectId' => 'ProjectId',
    'officeId' => 'OfficeId',
];

foreach ($stringMap as $inputKey => $apiKey) {
    if (array_key_exists($inputKey, $data) && $data[$inputKey] !== null && $data[$inputKey] !== '') {
        $payload[$apiKey] = $data[$inputKey];
    }
}

try {
    $response = callMPSQuery('Device/Update', $payload);

    if ($response === null) {
        logDeviceCrudAction('update', $payload, null, 'error', 'Device/Update returned null');
        jsonError('Device update failed via upstream API', 502);
    }

    cacheClear('all-devices-dealer-' . DEFAULT_DEALER_CODE);
    logDeviceCrudAction('update', $payload, ['deviceId' => $deviceId]);

    jsonSuccess([
        'device' => $response,
        'message' => 'Device updated successfully',
    ]);
} catch (Throwable $exception) {
    logDeviceCrudAction('update', $payload, null, 'error', $exception->getMessage());
    jsonError('Failed to update device: ' . $exception->getMessage());
}
