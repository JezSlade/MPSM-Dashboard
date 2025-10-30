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

    // FIX BUG #5: Reduce timeout and add better error messages
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => $payload,
            'timeout' => 15,  // Reduced from 30 to 15 seconds
            'ignore_errors' => true  // Get response even on HTTP errors
        ]
    ]);

    $response = file_get_contents('https://mpsm.resolutionsbydesign.us/mps-api/query', false, $context);

    // FIX BUG #5: Better error handling with HTTP status
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

        // FIX BUG #7: Improve total count extraction with logging
        $total = $meta['total_rows']
            ?? $meta['total_count']
            ?? $meta['total']
            ?? null;

        if ($total === null || $total === 0) {
            if (count($alerts) > 0) {
                error_log("get-supply-alerts.php: Total count missing or zero, but " . count($alerts) . " alerts returned. Using alert count.");
                $total = count($alerts);
            } else {
                $total = 0;
            }
        }

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
