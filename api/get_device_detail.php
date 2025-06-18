<?php
// api/get_device_detail.php
// Fetch full device detail for one device by deviceId, externalIdentifier, or serialNumber

require_once __DIR__ . '/../includes/api_functions.php';

// 1. Read & normalize input
$raw = file_get_contents('php://input');
$in  = json_decode($raw, true) ?: $_GET;
$normalized = [];
foreach ($in as $k => $v) {
    $normalized[strtolower($k)] = is_string($v) ? trim($v) : $v;
}
$in = $normalized;

// 2. Prepare identifiers
$deviceId  = $in['deviceid']            ?? '';
$extId      = strtoupper($in['externalidentifier'] ?? '');
$serialNum  = strtoupper($in['serialnumber']      ?? '');

// 3. Cache key & check
$cacheKey = 'mpsm:api:get_device_detail:' . md5(json_encode($in));
if ($cached = get_cache($cacheKey)) {
    echo $cached;
    exit;
}

// 4. Resolve the DeviceDto
$device = null;

if ($deviceId !== '') {
    // Direct lookup by ID
    $resp = call_api('POST', 'Device/Get', [
        'DealerCode' => env()['DEALER_CODE'] ?? '',
        'Id'         => $deviceId
    ]);
    $device = $resp['Result'] ?? null;

} elseif ($extId !== '') {
    // Two-step lookup by externalIdentifier
    $device = get_device_by_external($extId);

} elseif ($serialNum !== '') {
    // Direct lookup by serialNumber
    $resp = call_api('POST', 'Device/Get', [
        'DealerCode'   => env()['DEALER_CODE'] ?? '',
        'SerialNumber' => $serialNum
    ]);
    $device = $resp['Result'] ?? null;

} else {
    http_response_code(400);
    echo json_encode(
      ['error' => 'Missing deviceId, externalIdentifier, or serialNumber'],
      JSON_PRETTY_PRINT
    );
    exit;
}

// 5. Validate lookup result
if (!is_array($device) || empty($device['Id'])) {
    http_response_code(404);
    echo json_encode(
      ['error' => 'Device not found', 'lookup' => $device],
      JSON_PRETTY_PRINT
    );
    exit;
}

// 6. Extract codes from DeviceDto
$deviceId     = $device['Id'];
$serialNumber = $device['SerialNumber'] ?? $serialNum;
$assetNumber  = $device['AssetNumber']  ?? '';
$dealerCode   = $device['Dealer']['Code']   ?? env()['DEALER_CODE']   ?? '';
$customerCode = $device['Customer']['Code'] ?? env()['CUSTOMER_CODE'] ?? '';
$dealerId     = $device['Dealer']['Id']     ?? env()['DEALER_ID']     ?? null;
$customerId   = $device['Customer']['Id']   ?? null;

// 7. Fan-out all related endpoints
$output = ['device' => $device];

// GET /Device/GetDeviceDashboard
$output['GetDeviceDashboard'] = call_api(
    'GET',
    "Device/GetDeviceDashboard?dealerId={$dealerId}&customerId={$customerId}&deviceId={$deviceId}"
);

// POST /Device/GetDevices
$output['GetDevices'] = call_api('POST', 'Device/GetDevices', [
    'PageNumber'   => 1,
    'PageRows'     => 100,
    'SortColumn'   => 'Id',
    'SortOrder'    => 'Asc',
    'DealerCode'   => $dealerCode,
    'CustomerCode' => $customerCode,
    'Search'       => $serialNumber
]);

// POST /Device/GetDeviceAlerts
$output['GetDeviceAlerts'] = call_api('POST', 'Device/GetDeviceAlerts', [
    'PageNumber'   => 1,
    'PageRows'     => 100,
    'SortColumn'   => 'InitialDate',
    'SortOrder'    => 'Desc',
    'DealerCode'   => $dealerCode,
    'CustomerCode' => $customerCode,
    'SerialNumber' => $serialNumber,
    'AssetNumber'  => $assetNumber
]);

// POST /Device/GetDevicesCount
$output['GetDevicesCount'] = call_api('POST', 'Device/GetDevicesCount', [
    'DealerCode'   => $dealerCode,
    'CustomerCode' => $customerCode
]);

// POST /Device/GetAvailableSupplies
$output['GetAvailableSupplies'] = call_api('POST', 'Device/GetAvailableSupplies', [
    'DealerCode'   => $dealerCode,
    'CustomerCode' => $customerCode,
    'SerialNumber' => $serialNumber,
    'AssetNumber'  => $assetNumber
]);

// POST /Device/GetSupplyAlerts
$output['GetSupplyAlerts'] = call_api('POST', 'Device/GetSupplyAlerts', [
    'PageNumber'   => 1,
    'PageRows'     => 100,
    'SortColumn'   => 'InitialDate',
    'SortOrder'    => 'Desc',
    'DealerCode'   => $dealerCode,
    'CustomerCode' => $customerCode,
    'SerialNumber' => $serialNumber,
    'AssetNumber'  => $assetNumber
]);

// POST /Device/GetMaintenanceAlerts
$output['GetMaintenanceAlerts'] = call_api('POST', 'Device/GetMaintenanceAlerts', [
    'PageNumber'   => 1,
    'PageRows'     => 100,
    'SortColumn'   => 'InitialDate',
    'SortOrder'    => 'Desc',
    'DealerCode'   => $dealerCode,
    'CustomerCode' => $customerCode,
    'SerialNumber' => $serialNumber,
    'AssetNumber'  => $assetNumber
]);

// POST /Device/GetDeviceDataHistory
$output['GetDeviceDataHistory'] = call_api('POST', 'Device/GetDeviceDataHistory', [
    'DealerCode'   => $dealerCode,
    'CustomerCode' => $customerCode,
    'SerialNumber' => $serialNumber,
    'AssetNumber'  => $assetNumber,
    'DeviceId'     => $deviceId
]);

// POST /Device/GetDeviceChart
$output['GetDeviceChart'] = call_api('POST', 'Device/GetDeviceChart', [
    'DealerCode'   => $dealerCode,
    'CustomerCode' => $customerCode,
    'SerialNumber' => $serialNumber,
    'AssetNumber'  => $assetNumber,
    'DeviceId'     => $deviceId
]);

// POST /Device/GetErrorsMessagesDataHistory
$output['GetErrorsMessagesDataHistory'] = call_api('POST', 'Device/GetErrorsMessagesDataHistory', [
    'PageNumber'   => 1,
    'PageRows'     => 100,
    'SortColumn'   => 'InitialDate',
    'SortOrder'    => 'Desc',
    'DealerCode'   => $dealerCode,
    'CustomerCode' => $customerCode,
    'SerialNumber' => $serialNumber,
    'AssetNumber'  => $assetNumber
]);

// POST /Device/GetAttributesDataHistory
$output['GetAttributesDataHistory'] = call_api('POST', 'Device/GetAttributesDataHistory', [
    'DealerCode'   => $dealerCode,
    'CustomerCode' => $customerCode,
    'SerialNumber' => $serialNumber,
    'AssetNumber'  => $assetNumber,
    'DeviceId'     => $deviceId
]);

// GET /SdsAction/GetDeviceActionsDashboard
$output['GetDeviceActionsDashboard'] = call_api(
    'GET',
    "SdsAction/GetDeviceActionsDashboard?deviceId={$deviceId}&dealerId={$dealerId}"
);

// 8. Cache & return successful response
$response = json_encode($output, JSON_PRETTY_PRINT);
set_cache($cacheKey, $response, 60);
echo $response;
