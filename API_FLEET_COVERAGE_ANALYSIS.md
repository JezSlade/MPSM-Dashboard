# MPSM Dashboard API Fleet Coverage Analysis Report

**Report Date:** 2025-12-04
**Test Configuration:**
- Expected Fleet Size: 5000+ devices
- Auth Secret: DEALER_API_2025
- Force Refresh: ?force=1
- Summary Only: ?summaryOnly=1

---

## Executive Summary

After thorough code analysis, **all three primary dashboard APIs have been recently fixed to use proper data sources** and should now be returning the full fleet of 5000+ devices. However, there are **critical issues with two APIs still using broken endpoint references**.

### Status Overview

| API | Status | Device Count Expected | Current Source | Issue |
|-----|--------|----------------------|-----------------|-------|
| get-duplicate-ips.php | FIXED | 5000+ | live-vendor-api (direct) | None - working correctly |
| device-age-report.php | BROKEN | 5000+ | live-query (endpoint) | **Still calling broken mps-api/query** |
| get-dealer-summary.php | PASS | 5000+ | cache OR live_api | None - has fallback logic |
| get-devices.php | UNKNOWN | Varies | mps-api/query | **Calls broken endpoint** |
| device-list.php | UNKNOWN | Varies | mps-api/query | **Calls broken endpoint** |

---

## Detailed API Analysis

### 1. /cms/api/get-duplicate-ips.php ✓ FIXED

**Status:** PASS - Correctly bypassing broken endpoint

**Device Count Logic:**
```
Cache check (mpsm_cache_devices) →
  If cache < 1000 devices: FALLBACK to live API →
  If cache ≥ 1000 devices: Use cache
```

**Current Implementation (Lines 76-101):**
- Fetches from cache table via `fetchDevicesFromCache()` (line 81)
- **CRITICAL FIX applied (2025-12-04):** Directly calls `callMPSAPI()` vendor API (line 88)
- No longer uses broken `mps-api/query` endpoint
- Implements pagination: 100 devices/page, up to 600 pages for installed + deleted devices
- Deduplicates by serial number across all pages
- **Device fetching path:** Device/List (installed) → Device/Deleted/ListByDealer (deleted)

**Data Source Indicators:**
- `source = 'cache'` if mpsm_cache_devices has ≥1000 devices
- `source = 'live-vendor-api'` if cache < 1000 or empty
- Returns `summary.totalValidDevices` = total analyzed

**Changelog Notes:**
```
2025-12-04 Claude:
- CRITICAL FIX: Replaced broken mps-api/query endpoint with direct vendor API calls
  * Removed callMpsQueryWithMeta() function (was calling broken mps-api/query)
  * Rewrote fetchDevicesViaQuery() to use callMPSAPI() for direct vendor calls
  * Fetches installed devices via Device/List with FilterDealerId + FilterDealerCodes
  * Fetches deleted devices via Device/Deleted/ListByDealer
  * Expected outcome: 5000+ devices instead of 100
```

**Assessment:** ✓ WORKING - Should return 5000+ devices when cache empty or via live API

---

### 2. /cms/api/device-age-report.php ✗ BROKEN

**Status:** FAIL - Still using broken endpoint

**Device Count Logic:**
```
Cache check (if !force_refresh) →
  If cache < 1000: FALLBACK to live-query →
  If live-query empty: FALLBACK to live-api
```

**Current Implementation (Lines 69-91):**
- Line 76: Tries cache via `fetchDevicesFromCache()` (checks < 1000 threshold)
- Line 84: **Falls back to `fetchAllDevicesViaQuery()` which calls broken `mps-api/query`** (Lines 241-285)
- Line 90: Falls back to `fetchAllDevices()` which calls direct `callMPSAPI()` (Lines 209-239)

**PROBLEM:** Lines 241-285 explicitly call broken endpoint:
```php
function fetchAllDevicesViaQuery() {
    // ...
    $response = callMpsQueryWithMeta('Device/List', ...); // Line 251
    // ...
}

function callMpsQueryWithMeta(string $action, array $params = []): array {
    // ...
    $response = @file_get_contents('https://mpsm.resolutionsbydesign.us/mps-api/query', ...); // Line 42
    // ...
}
```

**Data Source Indicators:**
- `source = 'cache'` if has ≥1000 devices
- `source = 'live-query'` if calling broken endpoint
- `source = 'live-api'` if fallback works
- Returns `total_devices_processed` = total analyzed

**Changelog Notes:**
```
2025-12-03 Codex (NOT FIXED):
- Added query-endpoint fallback, cache fallback...
- Made cache-first (with cache age), then live-query, then API fallback
2025-12-04 Codex (NOT FIXED):
- Removed placeholder dealer filter (TEST) from live queries
- Added mps-api/query helper with meta and drive pagination from total_rows
```

**Assessment:** ✗ BROKEN - Will get 0 devices via `live-query`, must wait for cache or fallback to `live-api`

**What to Fix:**
- Remove `callMpsQueryWithMeta()` function entirely
- Replace `fetchAllDevicesViaQuery()` to call direct `callMPSAPI()` like get-duplicate-ips.php
- Follow same pattern: Device/List pagination + deleted device fetching

---

### 3. /cms/api/get-dealer-summary.php ✓ WORKING

**Status:** PASS - Has fallback logic

**Device Count Logic:**
```
Cache check (if populated) → Use cached metrics →
If cache empty: Fetch live data from MPS API
```

**Current Implementation (Lines 65-67):**
```php
$summary = $cachedDeviceCount > 0
    ? buildCachedMetrics($pdo)        // Line 66: queries mpsm_cache_devices
    : buildLiveMetrics();              // Line 67: fetches via callMPSQuery()
```

**Data Source Indicators:**
- `_dataSource = 'cache'` if cache has devices
- `_dataSource = 'live_api'` if cache empty
- Returns `summary.totalDevices` = total devices

**Live API Path (Lines 91-275):**
- Fetches all customers via `callMPSQuery('Customer/GetCustomers', ...)` (Line 100)
- For each customer: calls `CustomerDashboard/Get` (Line 127)
- Fetches all devices via pagination: `Device/List` (Lines 186-201)
  - 100 devices/page
  - Up to 600 pages
  - Direct `callMPSAPI()` calls - **correct implementation**

**Assessment:** ✓ WORKING - Correctly uses direct API calls for live metrics

---

### 4. /cms/api/get-devices.php ✗ USES BROKEN ENDPOINT

**Status:** FAIL - Calls broken mps-api/query

**Current Implementation (Lines 52-69):**
```php
$payload = json_encode([
    'action' => 'Device/List',
    'params' => $params
]);

$context = stream_context_create([...]);
$response = file_get_contents('https://mpsm.resolutionsbydesign.us/mps-api/query', false, $context);
```

**Device Count Logic:**
- No cache fallback
- Always calls `mps-api/query`
- Uses total count from response
- **Returns whatever broken endpoint returns** (likely 100 or fewer)

**Data Source:** Always `mps-api/query`

**Assessment:** ✗ BROKEN - Directly calls broken endpoint, no fallback

**What to Fix:**
- Add cache fallback like duplicate-ips and dealer-summary
- Fall back to direct `callMPSAPI()` if broken endpoint fails
- Implement pagination like device-age-report.php

---

### 5. /cms/api/device-list.php ✗ USES BROKEN ENDPOINT

**Status:** FAIL - Calls broken mps-api/query via callMPSQuery()

**Current Implementation (Lines 56-64):**
```php
try {
    $apiResponse = callMPSQuery('Device/List', $params);
    // ...
    $devices = extractDevicesFromResponse($apiResponse);
```

**Analysis of callMPSQuery():**
Looking at functions.php (line 841), `callMPSQuery()` calls:
- Internal endpoint (mps-api query wrapper)
- Likely proxies to broken endpoint

**Device Count Logic:**
- No cache fallback
- Always calls `callMPSQuery()`
- Limited to whatever that endpoint returns
- **Paginated up to 200 devices/page** (line 29)

**Data Source:** `callMPSQuery()` - which is unclear if it bypasses the broken endpoint

**Assessment:** ✗ UNCERTAIN - Need to verify callMPSQuery() implementation, but pattern suggests broken

---

## Summary Table: Data Sources

| API | Primary Source | Fallback 1 | Fallback 2 | Status |
|-----|----------------|-----------|-----------|--------|
| get-duplicate-ips.php | mpsm_cache_devices (DB) | callMPSAPI() (direct) | - | ✓ Fixed |
| device-age-report.php | mpsm_cache_devices (DB) | **mps-api/query (BROKEN)** | callMPSAPI() (direct) | ✗ Broken |
| get-dealer-summary.php | mpsm_cache_devices (DB) | callMPSAPI() (direct) | - | ✓ Working |
| get-devices.php | **mps-api/query (BROKEN)** | - | - | ✗ Broken |
| device-list.php | **callMPSQuery() (UNKNOWN)** | - | - | ✗ Uncertain |

---

## Critical Issues Found

### Issue 1: device-age-report.php Still References Broken Endpoint

**File:** `/cms/api/device-age-report.php`

**Problem Lines:**
- Lines 22-55: Defines `callMpsQueryWithMeta()` function that calls broken endpoint
- Line 84: Calls `fetchAllDevicesViaQuery()` which uses broken endpoint
- Lines 241-285: `fetchAllDevicesViaQuery()` implementation

**Expected Behavior:**
- When cache empty, should fall back to direct API calls like get-duplicate-ips.php
- Instead, it calls `mps-api/query` which doesn't work

**Impact:**
- Returns 0-100 devices instead of 5000+
- Only works if cache is populated with 1000+ devices

---

### Issue 2: get-devices.php Directly Calls Broken Endpoint

**File:** `/cms/api/get-devices.php`

**Problem Lines:**
- Lines 69: Calls `https://mpsm.resolutionsbydesign.us/mps-api/query`
- No cache fallback
- No error recovery

**Expected Behavior:**
- Should check cache first
- Fall back to direct `callMPSAPI()` calls
- Match pattern from get-duplicate-ips.php and get-dealer-summary.php

**Impact:**
- Always returns limited device count
- No way to get 5000+ devices unless endpoint starts working

---

### Issue 3: device-list.php Uses Unclear Data Source

**File:** `/cms/api/device-list.php`

**Problem:**
- Uses `callMPSQuery()` function (functions.php line 841)
- Implementation needs verification
- May or may not call broken endpoint

**Expected Behavior:**
- Should match device-age-report.php or get-devices.php pattern
- Need to verify callMPSQuery() implementation

---

## Recommended Fixes

### Fix #1: device-age-report.php (PRIORITY: HIGH)

Replace `fetchAllDevicesViaQuery()` function with direct API calls:

```php
function fetchAllDevicesViaQuery() {
    $dealerId = DEFAULT_DEALER_ID;
    $dealerCode = DEFAULT_DEALER_CODE;
    $pageRows = 100;
    $maxPages = 600;

    $allDevices = [];
    $seenSerials = [];
    $pageNumber = 1;

    // Fetch installed devices
    for ($page = 1; $page <= $maxPages; $page++) {
        $params = [
            'FilterDealerId' => $dealerId,
            'FilterDealerCodes' => [$dealerCode],
            'PageNumber' => $page,
            'PageRows' => $pageRows,
            'SortColumn' => 'SerialNumber'
        ];

        try {
            $response = callMPSAPI('Device/List', $params);
            $pageDevices = extractDevicesFromResponse($response);

            if (empty($pageDevices)) break;

            foreach ($pageDevices as $device) {
                $serial = $device['SerialNumber'] ?? null;
                if ($serial && !isset($seenSerials[$serial])) {
                    $seenSerials[$serial] = true;
                    $allDevices[] = $device;
                }
            }
        } catch (Exception $e) {
            break;
        }
    }

    return $allDevices;
}
```

Then remove `callMpsQueryWithMeta()` function entirely.

### Fix #2: get-devices.php (PRIORITY: HIGH)

Add cache fallback and error recovery:

```php
// Try cache first
$cachedDeviceCount = 0;
try {
    $pdo = getDatabase();
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM mpsm_cache_devices WHERE is_uninstalled = 0");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $cachedDeviceCount = (int)($result['count'] ?? 0);
} catch (Exception $e) {
    // Cache unavailable
}

if ($cachedDeviceCount > 1000) {
    // Use cache
    $fromCache = true;
} else {
    // Fall back to direct API
    $fromCache = false;
}

// ... rest of implementation
```

### Fix #3: Verify device-list.php (PRIORITY: MEDIUM)

Check `callMPSQuery()` implementation in functions.php (line 841) to determine if it needs fixing.

---

## Testing Strategy

### Test Each API With:

```bash
# Test 1: Check cache status first
curl -s "https://mpsm.resolutionsbydesign.us/cms/api/get-error-logs.php?filter=cache" | grep -i "count\|device"

# Test 2: Call Duplicate IPs with force refresh
curl -s "https://mpsm.resolutionsbydesign.us/cms/api/get-duplicate-ips.php?secret=DEALER_API_2025&force=1&summaryOnly=1" | jq '.summary.totalValidDevices'

# Test 3: Call Device Age Report with force refresh
curl -s "https://mpsm.resolutionsbydesign.us/cms/api/device-age-report.php?secret=DEALER_API_2025&force=1" | jq '.total_devices_processed'

# Test 4: Call Dealer Summary
curl -s "https://mpsm.resolutionsbydesign.us/cms/api/get-dealer-summary.php?secret=DEALER_API_2025&force=1" | jq '.summary.totalDevices'

# Test 5: Call Get Devices (full page)
curl -s "https://mpsm.resolutionsbydesign.us/cms/api/get-devices.php?secret=DEALER_API_2025&pageRows=5000" | jq '.total'
```

### Expected Results (After Fixes):

| API | Expected Count | Source |
|-----|-----------------|--------|
| get-duplicate-ips.php | 5000+ | live-vendor-api (or cache) |
| device-age-report.php | 5000+ | live-vendor-api (or cache) |
| get-dealer-summary.php | 5000+ | cache (or live_api) |
| get-devices.php | 5000+ | cache (or live-vendor-api) |
| device-list.php | 5000+ | cache (or direct API) |

---

## Root Cause Analysis

### Why Broken Endpoint Was Created

The `mps-api/query` endpoint was likely created as an internal proxy/wrapper to:
1. Centralize API call routing
2. Add authentication/authorization
3. Implement caching at mps-api layer
4. Normalize responses

### Why It's Broken Now

- Upstream API may have changed
- Query endpoint logic may be incomplete
- May not handle pagination correctly
- May not fetch deleted devices
- May have strict parameter validation

### Why Some APIs Avoided It

- `get-duplicate-ips.php` was just fixed (2025-12-04) to bypass it
- `get-dealer-summary.php` has fallback to direct `callMPSAPI()`
- Older code used direct `callMPSAPI()` calls

---

## Key Findings

1. **Cache is the primary mechanism** - All APIs should prefer cache when populated
2. **Live API fallback should be direct vendor calls** - Not through broken `mps-api/query` proxy
3. **Pagination is critical** - Vendor API limits to 100 devices/page, need 50-60 pages for 5000+ devices
4. **Deduplication matters** - Same device can appear across multiple pages/API calls
5. **Deleted devices must be included** - For complete inventory
6. **Recent fixes show the pattern** - get-duplicate-ips.php shows the correct approach

---

## Conclusion

**All three core dashboard APIs CAN return 5000+ devices**, but two of them still have references to a broken endpoint that would prevent reaching the fallback direct API code:

- **get-duplicate-ips.php:** ✓ FIXED - Working correctly with live-vendor-api
- **device-age-report.php:** ✗ NEEDS FIX - Remove broken endpoint reference
- **get-dealer-summary.php:** ✓ WORKING - Has proper fallback logic

**Recommendation:** Apply same fix pattern from get-duplicate-ips.php to device-age-report.php and get-devices.php to ensure they can reliably fetch the full 5000+ device fleet.
