<?php
/**
 * Device Lifecycle - List
 * Provides paginated device listing via mps-api proxy.
 */

require '../config.php';
require '../functions.php';

requireAuth();
ensureDeviceCrudEnabled();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$pageRows = isset($_GET['pageRows']) ? (int)$_GET['pageRows'] : 50;
$sortColumn = $_GET['sortColumn'] ?? 'AssetNumber';
$sortOrder = $_GET['sortOrder'] ?? 'Asc';
$customerCode = trim($_GET['customerCode'] ?? '');
$dealerCode = $_GET['dealerCode'] ?? DEFAULT_DEALER_CODE;
$dealerId = $_GET['dealerId'] ?? DEFAULT_DEALER_ID;
$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? null;

if ($page < 1) {
    $page = 1;
}

if ($pageRows < 1) {
    $pageRows = 25;
} elseif ($pageRows > 200) {
    $pageRows = 200;
}

$sortOrder = strtoupper($sortOrder) === 'DESC' ? 'Desc' : 'Asc';

$params = [
    'FilterDealerId' => $dealerId,
    'FilterDealerCodes' => [$dealerCode],
    'PageNumber' => $page,
    'PageRows' => $pageRows,
    'SortColumn' => $sortColumn,
    'SortOrder' => $sortOrder,
];

if ($customerCode !== '') {
    $params['FilterCustomerCodes'] = [$customerCode];
}

if ($search !== '') {
    $params['FilterText'] = $search;
}

if ($status !== null && $status !== '') {
    $params['Status'] = (int)$status;
}

try {
    $apiResponse = callMPSQuery('Device/List', $params);

    if ($apiResponse === null) {
        logDeviceCrudAction('list', $params, null, 'error', 'Device/List returned null');
        jsonError('Failed to retrieve devices from upstream API', 502);
    }

    $devices = extractDevicesFromResponse($apiResponse);
    $total = $apiResponse['TotalCount']
        ?? $apiResponse['TotalRows']
        ?? $apiResponse['Total']
        ?? ($apiResponse['Meta']['TotalCount'] ?? null)
        ?? count($devices);

    $meta = [
        'page' => $page,
        'pageRows' => $pageRows,
        'sortColumn' => $sortColumn,
        'sortOrder' => $sortOrder,
        'total' => (int)$total,
        'rawMeta' => array_diff_key($apiResponse, array_flip(['Items', 'Result'])),
    ];

    logDeviceCrudAction('list', $params, ['total' => (int)$total, 'returned' => count($devices)]);

    jsonSuccess([
        'devices' => $devices,
        'total' => (int)$total,
        'meta' => $meta,
    ]);
} catch (Throwable $exception) {
    logDeviceCrudAction('list', $params, null, 'error', $exception->getMessage());
    jsonError('Failed to fetch device list: ' . $exception->getMessage());
}
