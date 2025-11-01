<?php
/**
 * Get Deleted/Uninstalled Devices API
 * Fetches devices that have been uninstalled/deleted
 * Used by global search to include historical devices like EB821
 */

require '../config.php';
require '../functions.php';

requireAuth();

$customerCode = $_GET['customerCode'] ?? DEFAULT_CUSTOMER_CODE;
$dealerCode = $_GET['dealerCode'] ?? DEFAULT_DEALER_CODE;
$pageNumber = isset($_GET['pageNumber']) ? (int) $_GET['pageNumber'] : 1;
$pageRows = isset($_GET['pageRows']) ? (int) $_GET['pageRows'] : 50;
$sortColumn = $_GET['sortColumn'] ?? 'AssetNumber';
$sortOrder = $_GET['sortOrder'] ?? 'Asc';

if ($pageNumber < 1) {
    $pageNumber = 1;
}

if ($pageRows < 1) {
    $pageRows = 1;
} elseif ($pageRows > 5000) {
    $pageRows = 5000;
}

$sortOrder = strtoupper($sortOrder) === 'DESC' ? 'Desc' : 'Asc';

try {
    // Query deleted/uninstalled devices from Asset Management API
    // Use Device/Deleted/ListByDealer to get ALL deleted devices for the dealer
    $params = [
        'DealerCode' => $dealerCode,
        'PageNumber' => $pageNumber,
        'PageRows' => $pageRows,
        'SortColumn' => $sortColumn,
        'SortOrder' => $sortOrder
    ];

    $payload = json_encode([
        'action' => 'Device/Deleted/ListByDealer',
        'params' => $params
    ]);

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => $payload,
            'timeout' => 15,
            'ignore_errors' => true
        ]
    ]);

    $response = file_get_contents('https://mpsm.resolutionsbydesign.us/mps-api/query', false, $context);

    if ($response === false) {
        $error = error_get_last();
        throw new Exception("Failed to contact mps-api backend: " . ($error['message'] ?? 'Unknown error'));
    }

    // Check HTTP response code
    if (isset($http_response_header)) {
        $status_line = $http_response_header[0] ?? '';
        if (preg_match('/HTTP\/\d\.\d\s+(\d+)/', $status_line, $matches)) {
            $http_code = (int)$matches[1];
            if ($http_code >= 400) {
                throw new Exception("mps-api returned HTTP {$http_code}: {$response}");
            }
        }
    }

    $data = json_decode($response, true);
    if (!$data) {
        throw new Exception("Invalid response from mps-api backend");
    }

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

        // Mark all devices as uninstalled for UI purposes
        foreach ($devices as &$device) {
            $device['IsUninstalled'] = true;
        }

        $total = $meta['total_rows']
            ?? $meta['total_count']
            ?? $meta['total']
            ?? (isset($raw['TotalCount']) ? (int) $raw['TotalCount'] : null)
            ?? (isset($raw['TotalRows']) ? (int) $raw['TotalRows'] : null);

        if ($total === null || $total === 0) {
            if (count($devices) > 0) {
                $total = count($devices);
            } else {
                $total = 0;
            }
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
    jsonError("Failed to fetch deleted devices: " . $e->getMessage());
}
