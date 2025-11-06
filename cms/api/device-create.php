<?php
/**
 * Device Lifecycle - Create
 * Creates a new offline device via mps-api proxy.
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

$customerCode = trim($data['customerCode'] ?? DEFAULT_CUSTOMER_CODE);
$serialNumber = trim($data['serialNumber'] ?? '');
$model = trim($data['model'] ?? '');
$brand = trim($data['brand'] ?? '');

if ($serialNumber === '' || $model === '' || $brand === '') {
    jsonError('serialNumber, model, and brand are required', 422);
}

$payload = [
    'CustomerCode' => $customerCode,
    'SerialNumber' => $serialNumber,
    'Model' => $model,
    'Brand' => $brand,
];

$optionalMap = [
    'assetNumber' => 'AssetNumber',
    'contact' => 'Contact',
    'department' => 'Department',
    'ipAddress' => 'AddressIP',
    'note' => 'Note',
    'productDescription' => 'ProductDescription',
    'isManaged' => 'IsManaged',
];

foreach ($optionalMap as $inputKey => $apiKey) {
    if (array_key_exists($inputKey, $data) && $data[$inputKey] !== null && $data[$inputKey] !== '') {
        $payload[$apiKey] = $data[$inputKey];
    }
}

try {
    $response = callMPSQuery('Device/Offline/Create', $payload);

    if ($response === null) {
        logDeviceCrudAction('create', $payload, null, 'error', 'Device/Offline/Create returned null');
        jsonError('Device creation failed via upstream API', 502);
    }

    cacheClear('all-devices-dealer-' . DEFAULT_DEALER_CODE);
    logDeviceCrudAction('create', $payload, ['deviceId' => $response['DeviceId'] ?? null]);

    jsonSuccess([
        'device' => $response,
        'message' => 'Device created successfully',
    ]);
} catch (Throwable $exception) {
    logDeviceCrudAction('create', $payload, null, 'error', $exception->getMessage());
    jsonError('Failed to create device: ' . $exception->getMessage());
}
