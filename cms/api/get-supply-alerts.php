<?php
/**
 * Get Supply Alerts API
 * Proxies SupplyAlert/List for a given dealer/customer/supply type. Uses the
 * current user's personal MPS Monitor login when saved, so switched dealers
 * resolve (see cms/toner-alerts.php).
 */
require '../config.php';
require '../functions.php';

requireAuth();

$dealerCode = $_GET['dealerCode'] ?? DEFAULT_DEALER_CODE;
$customerCode = $_GET['customerCode'] ?? null; // Optional - null returns all customers
$supplyType = $_GET['supplyType'] ?? null; // Optional - null returns all types (ManteinanceKit, PhotoConductor, Toner)
$pageNumber = isset($_GET['pageNumber']) ? (int)$_GET['pageNumber'] : 1;
$pageRows = isset($_GET['pageRows']) ? (int)$_GET['pageRows'] : 50;
$sortColumn = $_GET['sortColumn'] ?? 'InitialDate';
$sortOrder = $_GET['sortOrder'] ?? 'Desc';

if ($pageNumber < 1) {
    $pageNumber = 1;
}

if ($pageRows < 1) {
    $pageRows = 1;
} elseif ($pageRows > 200) {
    $pageRows = 200;
}

$sortOrder = strtoupper($sortOrder) === 'ASC' ? 'Asc' : 'Desc';

try {
    $data = queryMpsApi('SupplyAlert/List', [
        'DealerCode' => $dealerCode,
        'CustomerCode' => $customerCode,
        'SupplyType' => $supplyType,
        'ManageOption' => null,
        'PageNumber' => $pageNumber,
        'PageRows' => $pageRows,
        'SortColumn' => $sortColumn,
        'SortOrder' => $sortOrder
    ], $_SESSION['user_id']);

    $raw = $data['data'];
    $meta = isset($data['meta']) && is_array($data['meta']) ? $data['meta'] : [];
    $alerts = [];

    if (isset($raw['Items']) && is_array($raw['Items'])) {
        $alerts = $raw['Items'];
        $meta['total_rows'] = $meta['total_rows'] ?? (int)($raw['TotalRows'] ?? 0);
    } elseif (is_array($raw)) {
        $alerts = $raw;
    }

    $total = $meta['total_rows'] ?? $meta['total_count'] ?? count($alerts);

    jsonSuccess([
        'alerts' => $alerts,
        'total' => (int) $total,
        'page' => [
            'number' => $pageNumber,
            'size' => $pageRows,
        ],
    ]);
} catch (Exception $e) {
    jsonError('Failed to fetch supply alerts: ' . $e->getMessage());
}
