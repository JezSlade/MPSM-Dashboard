<?php
/**
 * Get Devices API
 * Proxies request to mps-api backend
 * Following Engineering Standards: CMS = presentation, mps-api = API proxy
 */

require '../config.php';
require '../functions.php';

requireAuth();

$customerCode = $_GET['customerCode'] ?? DEFAULT_CUSTOMER_CODE;
$dealerCode = $_GET['dealerCode'] ?? DEFAULT_DEALER_CODE;
$dealerId = $_GET['dealerId'] ?? DEFAULT_DEALER_ID;
$pageNumber = isset($_GET['pageNumber']) ? (int) $_GET['pageNumber'] : 1;
$pageRows = isset($_GET['pageRows']) ? (int) $_GET['pageRows'] : 50;
$sortColumn = $_GET['sortColumn'] ?? 'AssetNumber';
$sortOrder = $_GET['sortOrder'] ?? 'Asc';

if ($pageNumber < 1) {
    $pageNumber = 1;
}

if ($pageRows < 1) {
    $pageRows = 1;
} elseif ($pageRows > 200) {
    $pageRows = 200;
}

$sortOrder = strtoupper($sortOrder) === 'DESC' ? 'Desc' : 'Asc';

try {
    // Call mps-api backend via /query endpoint
    $payload = json_encode([
        'action' => 'Device/List',
        'params' => [
            'FilterDealerId' => $dealerId,
            'FilterDealerCodes' => [$dealerCode],
            'FilterCustomerCodes' => [$customerCode],
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

    // mps-api returns {success, data, action}
    if (isset($data['success']) && $data['success'] && isset($data['data'])) {
        $raw = $data['data'];
        $meta = isset($data['meta']) && is_array($data['meta']) ? $data['meta'] : [];
        $devices = [];

        if (is_array($raw)) {
            if (isset($raw['Items']) && is_array($raw['Items'])) {
                $devices = $raw['Items'];
                if (!isset($meta['total_count']) && isset($raw['TotalCount'])) {
                    $meta['total_count'] = (int) $raw['TotalCount'];
                }
            } elseif (isset($raw['Result']) && is_array($raw['Result'])) {
                $devices = $raw['Result'];
                if (!isset($meta['total_rows']) && isset($raw['TotalRows'])) {
                    $meta['total_rows'] = (int) $raw['TotalRows'];
                }
            } else {
                $isList = array_keys($raw) === range(0, count($raw) - 1);
                if ($isList) {
                    $devices = $raw;
                }
            }
        }

        $total = $meta['total_rows']
            ?? $meta['total_count']
            ?? $meta['total']
            ?? (isset($raw['TotalCount']) ? (int) $raw['TotalCount'] : null)
            ?? (isset($raw['TotalRows']) ? (int) $raw['TotalRows'] : null);

        if ($total === null) {
            $total = count($devices);
        }

        $responseMeta = $meta;
        $responseMeta['items_returned'] = count($devices);

        jsonSuccess([
            'devices' => $devices,
            'total' => (int) $total,
            'page' => [
                'number' => $meta['page_number'] ?? $pageNumber,
                'size' => $meta['page_size'] ?? $pageRows,
            ],
            'meta' => $responseMeta,
        ]);
    } else {
        throw new Exception($data['error'] ?? 'Unknown error from mps-api');
    }

} catch (Exception $e) {
    jsonError("Failed to fetch devices: " . $e->getMessage());
}
