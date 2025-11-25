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
$allCustomers = isset($_GET['allCustomers']) && $_GET['allCustomers'] === 'true';

if ($pageNumber < 1) {
    $pageNumber = 1;
}

if ($pageRows < 1) {
    $pageRows = 1;
} elseif ($pageRows > 5000) {
    // Allow up to 5000 devices for global search performance
    // Single large request is faster than 34 sequential paginated requests
    $pageRows = 5000;
}

$sortOrder = strtoupper($sortOrder) === 'DESC' ? 'Desc' : 'Asc';

try {
    // Build params - omit customer filter if allCustomers=true for global search
    $params = [
        'FilterDealerId' => $dealerId,
        'FilterDealerCodes' => [$dealerCode],
        'PageNumber' => $pageNumber,
        'PageRows' => $pageRows,
        'SortColumn' => $sortColumn,
        'SortOrder' => $sortOrder
    ];

    // Only filter by customer if not searching all customers
    if (!$allCustomers) {
        $params['FilterCustomerCodes'] = [$customerCode];
    }

    // Call mps-api backend via /query endpoint
    $payload = json_encode([
        'action' => 'Device/List',
        'params' => $params
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
                $retryAfter = null;
                $upstreamError = $response;
                $decodedError = json_decode($response, true);

                if (is_array($decodedError)) {
                    $upstreamError = $decodedError['error'] ?? $upstreamError;
                    if (isset($decodedError['retry_after'])) {
                        $retryAfter = (int)$decodedError['retry_after'];
                    }
                }

                if ($http_code === 429) {
                    $retryAfter = $retryAfter ?? 60;
                    jsonResponse([
                        'success' => false,
                        'error' => 'Rate limit exceeded',
                        'retry_after' => $retryAfter,
                        'upstream_error' => $upstreamError
                    ], 429);
                }

                throw new Exception("mps-api returned HTTP {$http_code}: {$upstreamError}");
            }
        }
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

        // FIX BUG #7: Improve total count extraction with logging
        $total = $meta['total_rows']
            ?? $meta['total_count']
            ?? $meta['total']
            ?? (isset($raw['TotalCount']) ? (int) $raw['TotalCount'] : null)
            ?? (isset($raw['TotalRows']) ? (int) $raw['TotalRows'] : null);

        if ($total === null || $total === 0) {
            if (count($devices) > 0) {
                error_log("get-devices.php: Total count missing or zero, but " . count($devices) . " devices returned. Using device count.");
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
    jsonError("Failed to fetch devices: " . $e->getMessage());
}

/*
CHANGELOG
2025-11-25 Codex
- Propagate upstream 429 with retry_after metadata and cleaner error messages to enable rate-limit handling on the dashboard.
*/
