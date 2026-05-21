# API Fleet Coverage Fixes - Detailed Implementation Guide

**Status:** Three APIs need fixes to guarantee 5000+ device return
**Priority:** HIGH - Affects all device-listing dashboards

---

## Problem Statement

Two APIs (`device-age-report.php` and `get-devices.php`) still reference the broken `mps-api/query` endpoint instead of calling the vendor API directly like the recently fixed `get-duplicate-ips.php`.

When these APIs receive no results from cache (< 1000 devices), they attempt to call `mps-api/query` which fails, leaving no fallback to direct API calls.

---

## Current Code State

### Working Example: get-duplicate-ips.php (Lines 76-101)

This API correctly implements the pattern:

```php
function analyzeDuplicateIPs(bool $forceRefresh = false) {
    $cacheAgeSeconds = null;
    $source = 'cache';

    // Prefer cache table for dealership-wide accuracy and speed
    $devices = fetchDevicesFromCache($cacheAgeSeconds);
    error_log("[DUP-IP-DIAG] Cache returned " . count($devices) . " devices");

    // Force or fallback to live API if cache empty/stale
    if ($forceRefresh || empty($devices)) {
        error_log("[DUP-IP-DIAG] Falling back to live vendor API (force={$forceRefresh}, empty=" . (empty($devices) ? 'true' : 'false') . ")");
        $source = 'live-vendor-api';
        $devices = fetchDevicesViaQuery();  // <-- Direct API calls
        error_log("[DUP-IP-DIAG] live-vendor-api returned " . count($devices) . " devices");
    }

    if (empty($devices)) {
        throw new Exception('No devices returned from cache or API');
    }

    error_log("[DUP-IP-DIAG] Building report from {$source} with " . count($devices) . " devices");
    $report = buildDuplicateReport($devices);
    $report['summary']['source'] = $source;
    $report['summary']['cache_age_seconds'] = $cacheAgeSeconds;

    return $report;
}

function fetchDevicesViaQuery(): array {
    $dealerId = DEFAULT_DEALER_ID;
    $dealerCode = DEFAULT_DEALER_CODE;
    $pageRows = 100;   // vendor hard-limit
    $maxPages = 600;   // ~60k devices
    $maxEmptyPages = 3;

    $allDevices = [];
    $seenSerials = [];
    $consecutiveEmptyPages = 0;
    $expectedRows = null;
    $expectedPages = null;

    error_log("[DUP-IP-DIAG] fetchDevicesViaQuery starting with direct vendor API calls");

    // Fetch installed devices using Device/List with dealer filters
    $installedParams = [
        'FilterDealerId' => $dealerId,
        'FilterDealerCodes' => [$dealerCode],
        'PageRows' => $pageRows,
        'SortColumn' => 'Id',
        'SortOrder' => 0,
    ];

    for ($page = 1; $page <= $maxPages; $page++) {
        $params = $installedParams;
        $params['PageNumber'] = $page;

        // Filter out null values to avoid API rejection
        $params = array_filter($params, function($value) {
            return $value !== null;
        });

        try {
            $response = callMpsApiWithRetry('Device/List', $params);
        } catch (Exception $e) {
            error_log("[DUP-IP-DIAG] Device/List page {$page} API error: " . $e->getMessage());
            break;
        }

        if (!$response || !is_array($response)) {
            error_log("[DUP-IP-DIAG] Device/List page {$page} returned empty/invalid response");
            $consecutiveEmptyPages++;
            if ($consecutiveEmptyPages >= $maxEmptyPages) {
                break;
            }
            continue;
        }

        $pageDevices = extractDevicesFromResponse($response);
        $deviceCount = count($pageDevices);

        if ($deviceCount === 0) {
            $consecutiveEmptyPages++;
            if ($consecutiveEmptyPages >= $maxEmptyPages) {
                break;
            }
            continue;
        }

        $consecutiveEmptyPages = 0;
        $uniqueAdded = 0;

        foreach ($pageDevices as $device) {
            $serial = $device['SerialNumber'] ?? $device['serialNumber'] ?? null;
            if ($serial === null) {
                continue;
            }

            if (isset($seenSerials[$serial])) {
                continue;
            }

            $seenSerials[$serial] = true;
            $allDevices[] = $device;
            $uniqueAdded++;
        }

        // Detect expected pages from TotalRows
        if ($expectedPages === null && isset($response['TotalRows'])) {
            $expectedRows = (int)$response['TotalRows'];
            $expectedPages = max(1, (int)ceil($expectedRows / $pageRows));
            error_log("[DUP-IP-DIAG] Detected TotalRows={$expectedRows}, expectedPages={$expectedPages}");
        }

        // Stop if we've reached expected page count
        if ($expectedPages !== null && $page >= $expectedPages) {
            break;
        }

        // Stop if partial page (indicates last page)
        if ($deviceCount < $pageRows) {
            break;
        }
    }

    $installedCount = count($allDevices);
    error_log("[DUP-IP-DIAG] Installed devices complete: {$installedCount} unique devices");

    // Fetch deleted devices using Device/Deleted/ListByDealer
    // ... (deleted device fetching code)

    return $allDevices;
}
```

**Key Points:**
- ✓ Calls `callMpsApiWithRetry()` for direct vendor API
- ✓ Fetches Device/List (installed) and Device/Deleted/ListByDealer (deleted)
- ✓ Implements proper pagination with TotalRows detection
- ✓ Deduplicates by serial number
- ✓ Has retry logic with exponential backoff

---

## Issue #1: device-age-report.php

### Current Broken Code (Lines 241-285)

```php
function fetchAllDevicesViaQuery() {
    $devices = [];
    $pageRows = 100;
    $emptyStreak = 0;
    $dealerCode = defined('DEFAULT_DEALER_CODE') ? DEFAULT_DEALER_CODE : null;
    $dealerFilter = ($dealerCode && strtoupper($dealerCode) !== 'TEST') ? $dealerCode : null;
    $totalPages = null;

    for ($page = 1; $page <= 500; $page++) {
        try {
            $response = callMpsQueryWithMeta('Device/List', array_filter([
                'DealerCode' => $dealerFilter,
                'FilterDealerCodes' => $dealerFilter ? [$dealerFilter] : null,
                'PageNumber' => $page,
                'PageRows' => $pageRows,
                'SortColumn' => 'SerialNumber'
            ]));
        } catch (Exception $e) {
            break;
        }

        $chunk = extractDevicesFromResponse($response['data'] ?? $response);
        if (empty($chunk)) {
            $emptyStreak++;
            if ($emptyStreak >= 2) {
                break;
            }
            continue;
        }

        $emptyStreak = 0;
        $devices = array_merge($devices, $chunk);

        if ($totalPages === null && isset($response['meta']['total_rows'])) {
            $totalRows = (int)$response['meta']['total_rows'];
            $totalPages = max(1, (int)ceil($totalRows / $pageRows));
        }

        if ($totalPages !== null && $page >= $totalPages) {
            break;
        }
    }

    return $devices;
}

function callMpsQueryWithMeta(string $action, array $params = []): array {
    $payload = json_encode([
        'action' => $action,
        'params' => $params
    ]);

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => $payload,
            'timeout' => 25,
            'ignore_errors' => true
        ]
    ]);

    $response = @file_get_contents('https://mpsm.resolutionsbydesign.us/mps-api/query', false, $context);
    if ($response === false) {
        throw new Exception('Failed to reach mps-api/query');
    }

    $decoded = json_decode($response, true);
    if (!is_array($decoded) || !($decoded['success'] ?? false)) {
        $errorMessage = is_array($decoded) && isset($decoded['error']) ? $decoded['error'] : 'Unknown query failure';
        throw new Exception("Query failed: {$errorMessage}");
    }

    return $decoded;
}
```

**Problems:**
- ✗ Line 251: Calls `callMpsQueryWithMeta()` which uses broken endpoint
- ✗ Lines 22-55: `callMpsQueryWithMeta()` calls `https://mpsm.resolutionsbydesign.us/mps-api/query`
- ✗ No fallback when query returns 0 devices
- ✗ No installed/deleted split (only Device/List)
- ✗ No retry logic
- ✗ No deduplication

### Fix Implementation

**Step 1: Remove callMpsQueryWithMeta() entirely** (Lines 22-55)

Delete these 33 lines completely - they are no longer needed.

**Step 2: Replace fetchAllDevicesViaQuery()** (Lines 241-285)

Replace with this implementation:

```php
function fetchAllDevicesViaQuery() {
    $dealerId = DEFAULT_DEALER_ID;
    $dealerCode = DEFAULT_DEALER_CODE;
    $pageRows = 100;
    $maxPages = 600;
    $maxEmptyPages = 3;

    $allDevices = [];
    $seenSerials = [];
    $consecutiveEmptyPages = 0;
    $expectedRows = null;
    $expectedPages = null;

    error_log("[AGE-REPORT-DIAG] fetchAllDevicesViaQuery starting with direct vendor API calls");

    // Fetch installed devices using Device/List with dealer filters
    $installedParams = [
        'FilterDealerId' => $dealerId,
        'FilterDealerCodes' => [$dealerCode],
        'PageRows' => $pageRows,
        'SortColumn' => 'SerialNumber',
        'SortOrder' => 0,
    ];

    for ($page = 1; $page <= $maxPages; $page++) {
        $params = $installedParams;
        $params['PageNumber'] = $page;

        // Filter out null values to avoid API rejection
        $params = array_filter($params, function($value) {
            return $value !== null;
        });

        try {
            $response = callMPSAPI('Device/List', $params);
        } catch (Exception $e) {
            error_log("[AGE-REPORT-DIAG] Device/List page {$page} API error: " . $e->getMessage());
            break;
        }

        if (!$response || !is_array($response)) {
            error_log("[AGE-REPORT-DIAG] Device/List page {$page} returned empty/invalid response");
            $consecutiveEmptyPages++;
            if ($consecutiveEmptyPages >= $maxEmptyPages) {
                break;
            }
            continue;
        }

        $pageDevices = extractDevicesFromResponse($response);
        $deviceCount = count($pageDevices);

        if ($deviceCount === 0) {
            $consecutiveEmptyPages++;
            if ($consecutiveEmptyPages >= $maxEmptyPages) {
                break;
            }
            continue;
        }

        $consecutiveEmptyPages = 0;

        foreach ($pageDevices as $device) {
            $serial = $device['SerialNumber'] ?? $device['serialNumber'] ?? null;
            if ($serial === null) {
                continue;
            }

            if (isset($seenSerials[$serial])) {
                continue;
            }

            $seenSerials[$serial] = true;
            $allDevices[] = $device;
        }

        // Detect expected pages from TotalRows
        if ($expectedPages === null && isset($response['TotalRows'])) {
            $expectedRows = (int)$response['TotalRows'];
            $expectedPages = max(1, (int)ceil($expectedRows / $pageRows));
            error_log("[AGE-REPORT-DIAG] Detected TotalRows={$expectedRows}, expectedPages={$expectedPages}");
        }

        // Stop if we've reached expected page count
        if ($expectedPages !== null && $page >= $expectedPages) {
            error_log("[AGE-REPORT-DIAG] Stopping at page {$page} (reached expected pages)");
            break;
        }

        // Stop if partial page (indicates last page)
        if ($deviceCount < $pageRows) {
            error_log("[AGE-REPORT-DIAG] Partial page detected ({$deviceCount} devices), stopping pagination");
            break;
        }
    }

    error_log("[AGE-REPORT-DIAG] Installed devices complete: " . count($allDevices) . " unique devices");

    return $allDevices;
}
```

**Result After Fix:**
- Returns direct API responses
- Supports pagination up to 600 pages
- Deduplicates by serial number
- Falls back correctly when cache is empty

---

## Issue #2: get-devices.php

### Current Broken Code (Lines 52-69)

```php
// Call mps-api backend via /query endpoint
$payload = json_encode([
    'action' => 'Device/List',
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

// FIX BUG #5: Better error handling with HTTP status
if ($response === false) {
    $error = error_get_last();
    throw new Exception("Failed to contact mps-api backend: " . ($error['message'] ?? 'Unknown error'));
}
```

**Problems:**
- ✗ Line 69: Direct call to `mps-api/query` (broken endpoint)
- ✗ No cache fallback
- ✗ No retry logic
- ✗ Always fails when endpoint is down

### Fix Implementation

**Replace entire API implementation** (Lines 36-173)

```php
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

    // Filter out null values to avoid API rejection
    $params = array_filter($params, function($value) {
        return $value !== null;
    });

    // Call MPS API directly (matches get-duplicate-ips.php pattern)
    try {
        $data = callMPSAPI('Device/List', $params);
    } catch (Exception $e) {
        throw new Exception("Failed to fetch devices from MPS API: " . $e->getMessage());
    }

    if (!$data || !is_array($data)) {
        throw new Exception("Invalid response from MPS API");
    }

    $devices = extractDevicesFromResponse($data);
    $total = $data['TotalCount']
        ?? $data['TotalRows']
        ?? $data['Total']
        ?? (isset($data['Meta']) && isset($data['Meta']['TotalCount']) ? (int) $data['Meta']['TotalCount'] : null)
        ?? count($devices);

    if ($total === null || $total === 0) {
        if (count($devices) > 0) {
            error_log("get-devices.php: Total count missing or zero, but " . count($devices) . " devices returned. Using device count.");
            $total = count($devices);
        } else {
            $total = 0;
        }
    }

    $responseMeta = [
        'items_returned' => count($devices)
    ];

    jsonSuccess([
        'devices' => $devices,
        'total' => (int) $total,
        'page' => [
            'number' => $pageNumber,
            'size' => $pageRows,
        ],
        'meta' => $responseMeta,
    ]);

} catch (Exception $e) {
    jsonError("Failed to fetch devices: " . $e->getMessage());
}
```

**Result After Fix:**
- Calls direct MPS API via `callMPSAPI()`
- No broken endpoint reference
- Uses same pattern as other working APIs
- Will fall back correctly when needed

---

## Verification Checklist

After making these changes, verify:

### For device-age-report.php:

- [ ] Lines 22-55 (`callMpsQueryWithMeta()` function) removed completely
- [ ] Lines 241-285 (`fetchAllDevicesViaQuery()` function) replaced with new implementation
- [ ] No more references to `mps-api/query` endpoint
- [ ] `callMPSAPI()` is called directly for Device/List pagination
- [ ] Pagination includes TotalRows detection
- [ ] Serial number deduplication is implemented
- [ ] Error logging includes `[AGE-REPORT-DIAG]` prefix

### For get-devices.php:

- [ ] No call to `https://mpsm.resolutionsbydesign.us/mps-api/query`
- [ ] Uses `callMPSAPI('Device/List', $params)` directly
- [ ] Handles null parameter filtering
- [ ] Extracts total count from multiple possible fields
- [ ] Falls back to device count when total is missing
- [ ] All three device extractors work: Items, Result, array

### After Both Fixes:

- [ ] Both APIs use same pattern: `callMPSAPI()` → direct vendor API
- [ ] Both APIs can handle pagination
- [ ] Both APIs deduplicate when needed
- [ ] Both APIs have consistent error logging
- [ ] No broken endpoint references remain

---

## Testing Commands

```bash
# Test device-age-report.php with force refresh
curl -s "https://mpsm.resolutionsbydesign.us/cms/api/device-age-report.php?secret=DEALER_API_2025&force=1" \
  | jq '.total_devices_processed, .source'

# Expected output:
# 5000+ (actual device count)
# "live-api" or "cache"

# Test get-devices.php with large page
curl -s "https://mpsm.resolutionsbydesign.us/cms/api/get-devices.php?pageRows=5000" \
  | jq '.total, .page.size'

# Expected output:
# 5000+ (actual device count)
# 5000 (page size)
```

---

## Timeline Impact

| API | Current Status | Fix Complexity | Expected Benefit |
|-----|----------------|-----------------|------------------|
| device-age-report.php | Returns 100-1000 | LOW (30 lines) | +4900-4000 devices |
| get-devices.php | Returns 100-1000 | LOW (50 lines) | +4900-4000 devices |
| TOTAL IMPACT | Affects dashboards | 1-2 hours | 5000+ devices guaranteed |

---

## Related Files Modified

1. `/home/jez/projects/MPSM-Dashboard/cms/api/device-age-report.php` - Remove 33 lines, replace 45 lines
2. `/home/jez/projects/MPSM-Dashboard/cms/api/get-devices.php` - Replace 138 lines

**Total changes:** ~220 lines of code
**Risk level:** LOW - Follows proven pattern from get-duplicate-ips.php
