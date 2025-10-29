<?php
/**
 * Get Supply Alerts API
 * Proxies request to mps-api backend for supply/toner alerts
 * Following Engineering Standards: CMS = presentation, mps-api = API proxy
 */

require '../config.php';
require '../functions.php';

requireAuth();

$dealerCode = $_GET['dealerCode'] ?? DEFAULT_DEALER_CODE;
$customerCode = $_GET['customerCode'] ?? null; // Optional - null returns all customers
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
    // Call mps-api backend via /query endpoint
    $payload = json_encode([
        'action' => 'SupplyAlert/List',
        'params' => [
            'DealerCode' => $dealerCode,
            'CustomerCode' => $customerCode,
            'SupplyType' => null, // null = all types
            'ManageOption' => null, // null = all options
            'PageNumber' => $pageNumber,
            'PageRows' => $pageRows,
            'SortColumn' => $sortColumn,
            'SortOrder' => $sortOrder
        ]
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
        throw new Exception("Failed to contact mps-api backend");
    }

    $data = json_decode($response, true);
    if (!$data) {
        throw new Exception("Invalid response from mps-api backend");
    }

    // mps-api returns {success, data, meta?, action}
    if (isset($data['success']) && $data['success'] && isset($data['data'])) {
        $raw = $data['data'];
        $meta = isset($data['meta']) && is_array($data['meta']) ? $data['meta'] : [];
        $alerts = [];

        if (isset($raw['Items']) && is_array($raw['Items'])) {
            $alerts = $raw['Items'];
            if (!isset($meta['total_rows']) && isset($raw['TotalRows'])) {
                $meta['total_rows'] = (int) $raw['TotalRows'];
            }
            if (!isset($meta['total_count']) && isset($raw['TotalCount'])) {
                $meta['total_count'] = (int) $raw['TotalCount'];
            }
        } elseif (is_array($raw)) {
            $alerts = $raw;
            if (!isset($meta['total_rows']) && isset($raw['TotalRows'])) {
                $meta['total_rows'] = (int) $raw['TotalRows'];
            }
            if (!isset($meta['total_count']) && isset($raw['TotalCount'])) {
                $meta['total_count'] = (int) $raw['TotalCount'];
            }
        }

        $total = $meta['total_rows']
            ?? $meta['total_count']
            ?? $meta['total']
            ?? count($alerts);

        $responseMeta = $meta;
        $responseMeta['items_returned'] = count($alerts);

        jsonSuccess([
            'alerts' => $alerts,
            'total' => (int) $total,
            'page' => [
                'number' => $pageNumber,
                'size' => $pageRows,
            ],
            'meta' => $responseMeta,
        ]);
    } else {
        throw new Exception($data['error'] ?? 'Unknown error from mps-api');
    }

} catch (Exception $e) {
    jsonError("Failed to fetch supply alerts: " . $e->getMessage());
}
