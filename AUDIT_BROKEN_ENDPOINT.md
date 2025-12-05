# COMPREHENSIVE AUDIT: Broken mps-api/query Endpoint Usage

**Date:** 2025-12-04
**Auditor:** Claude Code
**Status:** Complete - All Instances Found and Catalogued
**Broken Endpoint:** `https://mpsm.resolutionsbydesign.us/mps-api/query`

---

## Executive Summary

This comprehensive audit identified **ALL remaining usages** of the broken `mps-api/query` endpoint in the MPSM-Dashboard codebase.

### Key Findings:
- **4 broken helper functions** across 5 files
- **22 direct function calls** using these broken helpers
- **10+ dashboard APIs** affected by these broken dependencies
- **4 unique function names** implementing the broken endpoint
- **3 critical functions** that require immediate attention

### Risk Level: HIGH
Multiple critical dashboard APIs and cache infrastructure components depend on the broken endpoint.

---

## Broken Functions Inventory

### 1. callMPSQuery() - CRITICAL
**File:** `/cms/functions.php`
**Lines:** 841-870
**Impact:** HIGH - Used by 14 calls across 8+ APIs
**Status:** BROKEN

```php
// Line 857: Direct call to broken endpoint
$response = @file_get_contents('https://mpsm.resolutionsbydesign.us/mps-api/query', false, $context);
```

**Dependent Files (14 calls):**
- device-update.php (line 73)
- device-list.php (line 57) - **DASHBOARD**
- device-create.php (line 56)
- device-delete.php (line 31)
- test-dealer-api.php (line 48)
- test-dealer-live.php (lines 93, 108) - **DASHBOARD**
- test-dealer-summary.php (line 77)
- get-dealer-summary.php (lines 100, 127, 186) - **DASHBOARD CRITICAL**
- get-dealer-summary-hybrid.php (lines 93, 117, 157) - **DASHBOARD CRITICAL**
- get-customer-portfolio.php (lines 278, 297) - **DASHBOARD CRITICAL**
- refresh-cache-chunked.php (line 451)

### 2. callMpsQueryWithMeta() - HIGH (Device Age Report)
**File:** `/cms/api/device-age-report.php`
**Lines:** 22-54 (definition), 251 (usage)
**Impact:** HIGH - Dashboard metric endpoint
**Status:** BROKEN

```php
// Line 42: Direct call to broken endpoint
$response = @file_get_contents('https://mpsm.resolutionsbydesign.us/mps-api/query', false, $context);

// Line 251: Function usage
$response = callMpsQueryWithMeta('Device/List', array_filter([...]));
```

**Scope:** Function only used in this file
**Note:** Has fallback to `fetchAllDevices()` on line 90, but middle path is still broken

### 3. callMpsQueryWithMeta() - CRITICAL (Cache Infrastructure)
**File:** `/cms/api/refresh-cache-chunked.php`
**Lines:** 110-138 (definition), 309 (usage)
**Impact:** CRITICAL - Cache refresh infrastructure
**Status:** BROKEN

```php
// Line 126: Direct call to broken endpoint
$response = @file_get_contents('https://mpsm.resolutionsbydesign.us/mps-api/query', false, $context);

// Line 309: Function usage in PHASE 1 device fetching
$response = callMpsQueryWithMeta('Device/List', $params);
```

**Critical Context:** Line 614 changelog admits:
> "Swapped Device/List and Device/Get fetches to use the mps-api engine via callMPSQuery to avoid direct OAuth token timeouts"

This was attempting a workaround that made things worse.

### 4. callMPSMAPI() - MEDIUM
**File:** `/cms/api/refresh-cache-enhanced.php`
**Lines:** 576-674 (definition), 825 & 856 (usages)
**Impact:** MEDIUM - Dashboard metadata only
**Status:** BROKEN

```php
// Line 599: Direct call to broken endpoint
$response = @file_get_contents('https://mpsm.resolutionsbydesign.us/mps-api/query', false, $context);

// Line 825: Usage for connectors
$connectorsData = callMPSMAPI('CustomerDashboard/Connectors', ['Code' => $customerCode]);

// Line 856: Usage for pages
$pagesData = callMPSMAPI('CustomerDashboard/Pages', ['code' => $customerCode]);
```

**Scope Issue:** Function defined ONLY in this file, NOT in functions.php

### 5. Undefined Function Call - ERROR
**File:** `/cms/api/populate-chunked.php`
**Line:** 59
**Impact:** LOW - Appears to be utility script
**Status:** RUNTIME ERROR

```php
// Line 59: Calls undefined function
$response = callMPSMAPI('Device/List', $params);
```

**Problem:** `callMPSMAPI()` is not defined in functions.php or bootstrap.php
**Will fail with:** `Call to undefined function callMPSMAPI()`

---

## Affected Dashboard APIs

### CRITICAL (Must Fix First)
1. **Dealer Summary Dashboard** (`get-dealer-summary.php`)
   - 3 callMPSQuery() calls (lines 100, 127, 186)
   - Fetches: Customers, Dashboard data, Device list
   - Impact: Primary business dashboard broken

2. **Customer Portfolio Dashboard** (`get-customer-portfolio.php`)
   - 2 callMPSQuery() calls (lines 278, 297)
   - Fetches: Customers, Dashboard data
   - Impact: Customer overview broken

### HIGH (Dependent on Critical Fix)
3. **Device List** (`device-list.php`)
   - 1 callMPSQuery() call (line 57)
   - Fetches: Device list
   - Impact: Device management broken

4. **Device Age Report** (`device-age-report.php`)
   - 1 callMpsQueryWithMeta() call (line 251)
   - Fetches: All devices for age analysis
   - Impact: Device aging metrics broken

### MEDIUM
5. **Dealer Summary Hybrid** (`get-dealer-summary-hybrid.php`)
   - 3 callMPSQuery() calls (lines 93, 117, 157)
   - Alternative implementation of dealer summary

6. **Test/Diagnostic APIs**
   - test-dealer-api.php, test-dealer-live.php, test-dealer-summary.php
   - Not critical production paths

---

## Infrastructure Affected

### Cache System (CRITICAL)
**Files:** refresh-cache-chunked.php, refresh-cache-enhanced.php
- Cache refresh pipeline depends on broken endpoint
- Device population cannot complete
- Cache data becomes stale
- All dashboards degrade when cache expires

---

## Comparison: Fixed vs Broken

### CORRECT PATTERN (Already Fixed)
**File:** `/cms/api/get-duplicate-ips.php`

```php
// Lines 189, 282: Direct callMPSAPI() with retry logic
$response = callMpsApiWithRetry('Device/List', $params);
$response = callMpsApiWithRetry('Device/Deleted/ListByDealer', $params);

// Includes proper error handling and pagination
// Follows vendor pagination limits (PageRows=100)
// Uses TotalRows metadata for pagination
```

**Changelog (lines 552-560) documents the fix:**
```
CRITICAL FIX: Replaced broken mps-api/query endpoint with direct vendor API calls
- Removed callMpsQueryWithMeta() function (was calling broken mps-api/query)
- Rewrote fetchDevicesViaQuery() to use callMPSAPI() for direct vendor calls
- Follows exact pagination pattern with proper retry logic
```

### INCORRECT PATTERN (Currently Broken)
All files listed above use this pattern:

```php
// Broken: calls mps-api/query instead of direct vendor API
$response = callMPSQuery('Device/List', $params);
$response = callMpsQueryWithMeta('Device/List', $params);
$response = callMPSMAPI('Device/List', $params);
```

---

## Root Cause Analysis

The mps-api/query endpoint was intended as an intermediate layer but:
1. Returns empty data for Device/List and other endpoints
2. Doesn't preserve necessary metadata (TotalRows for pagination)
3. Adds latency without benefit
4. Creates a point of failure between application and vendor API

**Solution:** Use `callMPSAPI()` directly from `/cms/functions.php` (lines 154-183)

---

## Fix Strategy

### Phase 1: CRITICAL (Hours 1-3)
1. Fix `/cms/functions.php` - Replace `callMPSQuery()` function
2. Fix `/cms/api/device-age-report.php` - Replace `callMpsQueryWithMeta()`
3. Fix `/cms/api/refresh-cache-chunked.php` - Replace `callMpsQueryWithMeta()`
4. Test all 8+ dependent APIs

### Phase 2: HIGH (Hour 4)
5. Fix `/cms/api/refresh-cache-enhanced.php` - Replace `callMPSMAPI()`
6. Fix `/cms/api/populate-chunked.php` - Fix undefined function call

### Phase 3: VERIFICATION (Hour 5-6)
7. Run full dashboard test suite
8. Verify cache refresh completes
9. Check log files for errors
10. Performance comparison

---

## Implementation Template

Replace all instances using this pattern:

```php
// BEFORE (BROKEN)
try {
    $response = callMPSQuery('Device/List', $params);
} catch (Exception $e) {
    // error handling
}

// AFTER (FIXED)
try {
    $response = callMPSAPI('Device/List', $params);
} catch (Exception $e) {
    // error handling
}
```

For pagination, follow the pattern in `/cms/api/get-duplicate-ips.php`:
- Use `callMpsApiWithRetry()` for automatic retry logic
- Check `$response['TotalRows']` for pagination metadata
- Implement 3-empty-page limit for termination
- Use PageRows=100 (vendor hard-limit)

---

## Testing Checklist

- [ ] functions.php fixed and tested
- [ ] device-age-report.php fixed and tested
- [ ] refresh-cache-chunked.php fixed and tested
- [ ] get-dealer-summary.php returns valid data
- [ ] get-customer-portfolio.php returns valid data
- [ ] device-list.php returns valid data
- [ ] Device Age Report displays metrics
- [ ] Cache refresh completes successfully
- [ ] No PHP errors in logs
- [ ] Rate limiting handled properly (429 responses)
- [ ] Pagination works correctly for multi-page requests

---

## Files Requiring Action

### MUST FIX (Absolute Priority)
1. `/home/jez/projects/MPSM-Dashboard/cms/functions.php` (Line 841)
2. `/home/jez/projects/MPSM-Dashboard/cms/api/device-age-report.php` (Lines 22-54, 251)
3. `/home/jez/projects/MPSM-Dashboard/cms/api/refresh-cache-chunked.php` (Lines 110-138, 309)

### SHOULD FIX (Secondary Priority)
4. `/home/jez/projects/MPSM-Dashboard/cms/api/refresh-cache-enhanced.php` (Lines 576-674)
5. `/home/jez/projects/MPSM-Dashboard/cms/api/populate-chunked.php` (Line 59)

### IMPACTED APIS (Will break without fixes)
- device-update.php, device-list.php, device-create.php, device-delete.php
- test-dealer-api.php, test-dealer-live.php, test-dealer-summary.php
- get-dealer-summary.php, get-dealer-summary-hybrid.php, get-customer-portfolio.php

---

## Reference Information

### Correct Implementation Location
**File:** `/cms/api/get-duplicate-ips.php`
- Shows proper use of callMPSAPI()
- Includes retry logic with exponential backoff
- Implements correct pagination with TotalRows
- Has comprehensive error handling and logging
- Follows vendor API pagination limits

### Core API Function to Use
**File:** `/cms/functions.php`
**Lines:** 154-183
**Function:** `callMPSAPI($action, $params = [])`
- Connects to vendor MPS API directly
- Handles OAuth token management
- Proper error handling
- Use this instead of any query endpoint wrapper

### Related Documentation
- `/context/systemic-data-coverage-issue.md` - Data coverage issues
- `/context/mps-api-layer.md` - API architecture
- `CRITICAL_FIX_DEVICE_PAGINATION.md` - Pagination fixes
- `/cms/api/get-duplicate-ips.php` - Reference implementation

---

## Conclusion

The comprehensive audit has identified all remaining usages of the broken mps-api/query endpoint. The root cause is an intermediate API layer that doesn't work properly. The solution is to replace all broken helper functions with direct calls to `callMPSAPI()`, following the pattern already implemented in `/cms/api/get-duplicate-ips.php`.

**Estimated fix time:** 5-6 hours
**Risk level:** High (affects multiple critical dashboards)
**Recommended action:** Begin with Phase 1 (functions.php fix) as it unblocks most dependent APIs

---

**Next Steps:** Execute Phase 1 fixes starting with `/cms/functions.php`
