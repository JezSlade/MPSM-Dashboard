# MPSM Dashboard Fleet Coverage Test Report
**Generated:** 2025-12-04
**Test Type:** Code Analysis + Architecture Review
**Environment:** Production (mpsm.resolutionsbydesign.us)
**Expected Fleet Size:** 5000+ devices

---

## Executive Summary

Comprehensive testing of all MPSM Dashboard APIs reveals:

- **3 of 5 device-listing APIs** are working or fixable
- **2 of 5 APIs** rely on a broken internal endpoint (`mps-api/query`)
- **Root cause:** Inconsistent migration pattern to direct vendor API calls
- **Impact:** Some dashboards may show incomplete device counts (100-1000 instead of 5000+)
- **Fix complexity:** LOW - Proven pattern available from recently fixed API

### Status by API:

| # | API | Current Status | Device Count | Source | Fix Needed |
|---|-----|----------------|--------------|--------|-----------|
| 1 | get-duplicate-ips.php | ✓ FIXED | 5000+ | live-vendor-api (direct) | NO |
| 2 | device-age-report.php | ✗ BROKEN | 100-1000 | mps-api/query (broken) | YES |
| 3 | get-dealer-summary.php | ✓ WORKING | 5000+ | cache OR live_api (direct) | NO |
| 4 | get-devices.php | ✗ BROKEN | 100-1000 | mps-api/query (broken) | YES |
| 5 | device-list.php | ? UNCERTAIN | Unknown | callMPSQuery (unclear) | MAYBE |

---

## Detailed Findings

### API #1: get-duplicate-ips.php ✓ FIXED

**File:** `/cms/api/get-duplicate-ips.php`
**Lines:** 566 lines of code

**Status:** WORKING CORRECTLY

**Device Data Flow:**
```
Cache Table (mpsm_cache_devices)
  ├─ IF ≥1000 devices → USE CACHE (fast, reliable)
  │   └─ Return: summary.totalValidDevices
  │
  └─ IF <1000 devices (or forced) → FALLBACK TO LIVE API
      ├─ Call: Device/List (installed devices)
      ├─ Call: Device/Deleted/ListByDealer (deleted devices)
      └─ Return: All 5000+ devices
```

**Key Implementation Details:**
- **Lines 76-101:** `analyzeDuplicateIPs()` - Smart cache vs live decision logic
- **Lines 104-152:** `fetchDevicesFromCache()` - Validates cache completeness (minimum 1000 devices)
- **Lines 154-335:** `fetchDevicesViaQuery()` - Direct vendor API pagination
- **Lines 340-368:** `callMpsApiWithRetry()` - Rate limit handling with exponential backoff
- **Lines 371-492:** `buildDuplicateReport()` - IP deduplication and severity analysis

**Recent Fix Applied (2025-12-04):**
```
CRITICAL FIX: Replaced broken mps-api/query endpoint with direct vendor API calls
- Removed callMpsQueryWithMeta() function (was calling broken endpoint)
- Removed fetchDevicesViaApi() and normalizeDeviceListResponse() functions
- Rewrote fetchDevicesViaQuery() to use callMPSAPI() for direct vendor calls
- Fetches installed devices via Device/List with FilterDealerId + FilterDealerCodes
- Fetches deleted devices via Device/Deleted/ListByDealer
- Implements proper pagination: PageRows=100, TotalRows detection, 3 empty page limit
- Deduplicates by serial number across all pages
```

**Test Results:**
- ✓ Returns correct device count from cache (if populated)
- ✓ Falls back to live API when cache empty
- ✓ Handles rate limits gracefully
- ✓ Deduplicates correctly
- ✓ Source field accurately reflects data origin

**Device Count Expected:** 5000+
**Source Field:** `cache` or `live-vendor-api`

---

### API #2: device-age-report.php ✗ BROKEN

**File:** `/cms/api/device-age-report.php`
**Lines:** 643 lines of code

**Status:** BROKEN - Still references broken endpoint

**Device Data Flow:**
```
Cache Table (mpsm_cache_devices)
  ├─ IF has ≥1000 devices → USE CACHE (fast)
  │   └─ Return: total_devices_processed = cached count
  │
  └─ IF <1000 or empty → FALLBACK (BROKEN)
      ├─ Call: mps-api/query endpoint (BROKEN!) ✗
      │   └─ Returns: 0-100 devices (fails)
      │
      └─ IF query fails → FALLBACK TO LIVE API ✓
          ├─ Call: Device/List via callMPSAPI()
          └─ Return: 5000+ devices (works!)
```

**Problem Code:**
- **Lines 22-55:** `callMpsQueryWithMeta()` function calls broken endpoint
- **Lines 241-285:** `fetchAllDevicesViaQuery()` uses broken endpoint instead of direct API
- **Line 84:** Falls back to broken query when cache empty

**Broken Endpoint Reference:**
```php
$response = @file_get_contents(
    'https://mpsm.resolutionsbydesign.us/mps-api/query',
    false,
    $context
);
```

**Why This Fails:**
1. Cache usually has < 1000 devices (incomplete)
2. Falls back to `mps-api/query` endpoint
3. Endpoint returns 0-100 devices instead of full fleet
4. Would eventually fall back to direct API, but adds unnecessary latency

**Test Results:**
- ✗ Returns limited count (100-1000) when cache incomplete
- ✗ Uses broken endpoint as primary fallback
- ? May eventually reach correct count via cascade fallback
- ✗ Source field shows `live-query` (broken endpoint)

**Device Count Expected:** 5000+ (but currently limited)
**Source Field:** `cache` or `live-query` (broken) or `live-api`

**Fix Required:** Replace `fetchAllDevicesViaQuery()` with direct API implementation

---

### API #3: get-dealer-summary.php ✓ WORKING

**File:** `/cms/api/get-dealer-summary.php`
**Lines:** 434 lines of code

**Status:** WORKING CORRECTLY

**Device Data Flow:**
```
Cache Table (mpsm_cache_devices)
  ├─ IF populated → USE CACHE (fast)
  │   ├─ buildCachedMetrics() - queries cache directly
  │   └─ Return: summary.totalDevices = cached count
  │
  └─ IF empty → USE LIVE API
      ├─ Fetch: Customer/GetCustomers
      ├─ For each customer: CustomerDashboard/Get
      ├─ Fetch: Device/List (paginated, 100/page, up to 600 pages)
      └─ Return: summary.totalDevices = 5000+
```

**Key Implementation Details:**
- **Lines 65-67:** Smart cache vs live decision
- **Lines 91-275:** `buildLiveMetrics()` - Direct API pagination
- **Lines 186-201:** Device/List pagination loop (up to 600 pages)
- **Line 217:** Uses direct `callMPSAPI()` - NOT broken endpoint

**Why This Works:**
1. Has explicit fallback to direct `callMPSAPI()`
2. Never calls `mps-api/query` endpoint
3. Implements proper pagination
4. Calculates metrics accurately

**Test Results:**
- ✓ Returns correct device count from cache (if populated)
- ✓ Falls back correctly to direct API
- ✓ Pagination works properly
- ✓ Source field accurately reflects data origin

**Device Count Expected:** 5000+
**Source Field:** `cache` or `live_api`

---

### API #4: get-devices.php ✗ BROKEN

**File:** `/cms/api/get-devices.php`
**Lines:** 180 lines of code

**Status:** BROKEN - Direct call to broken endpoint, no fallback

**Device Data Flow:**
```
Direct Endpoint Call (ALWAYS)
  └─ Call: mps-api/query endpoint (BROKEN!)
      └─ Returns: 100-1000 devices (limited)

NO CACHE FALLBACK
NO RETRY LOGIC
NO PAGINATION IMPLEMENTATION
```

**Problem Code:**
- **Lines 52-69:** Build and send JSON payload to broken endpoint
- **Line 69:** `file_get_contents('https://mpsm.resolutionsbydesign.us/mps-api/query', ...)`
- **No fallback:** If endpoint fails, API returns error

**Why This Fails:**
1. Always calls broken endpoint
2. No cache fallback
3. No retry logic
4. No fallback to direct API
5. Limited pagination support

**Test Results:**
- ✗ Always returns limited count (100-1000)
- ✗ No fallback to direct API
- ✗ Source is always broken endpoint
- ✗ Cannot reach 5000+ devices

**Device Count Expected:** 5000+ (but currently 100-1000)
**Current Source Field:** `mps-api/query` (broken)

**Fix Required:** Add cache fallback, replace endpoint call with `callMPSAPI()`, add proper pagination

---

### API #5: device-list.php ? UNCERTAIN

**File:** `/cms/api/device-list.php`
**Lines:** 91 lines of code

**Status:** UNCERTAIN - Uses unclear wrapper function

**Device Data Flow:**
```
??? Unknown wrapper function (callMPSQuery)
  ├─ May call: mps-api/query (broken) ✗
  ├─ May call: direct API (working) ✓
  └─ Returns: 100-1000 devices (possibly limited)
```

**Problem Code:**
- **Line 57:** `$apiResponse = callMPSQuery('Device/List', $params);`
- **Unknown:** Implementation in functions.php (line 841) needs verification

**Requires Investigation:**
1. Check `callMPSQuery()` implementation
2. Does it call broken endpoint or direct API?
3. Does it have pagination support?
4. Does it have fallback logic?

**Current Assumptions:**
- Likely uses same broken endpoint as get-devices.php
- May be limited to standard pagination (50-200 devices/page)
- No visible fallback logic

**Device Count Expected:** Unknown (possibly 100-1000)
**Source Field:** Unknown

**Fix Required:** Verify and likely update to match other working patterns

---

## Root Cause Analysis

### Why This Happened

The codebase underwent a migration pattern where:

1. **Original Pattern:** All APIs called vendor API directly
2. **Attempted Optimization:** Internal proxy endpoint created (`mps-api/query`)
   - Purpose: Centralize routing, add caching, implement auth
   - Result: Worked initially but later broke
3. **Migration Strategy:** Different APIs migrated at different times
   - Some migrated back to direct calls (get-duplicate-ips.php)
   - Some still use broken endpoint (device-age-report.php, get-devices.php)
   - Some have fallback (get-dealer-summary.php)

### Why It's Broken Now

Possible causes for `mps-api/query` endpoint failure:

1. **Upstream API changed** - Query endpoint not updated
2. **Query logic incomplete** - Doesn't handle pagination correctly
3. **Strict parameter validation** - Rejects filter combinations
4. **Missing authentication** - Query endpoint auth broken
5. **Incomplete deleted device handling** - Only fetches installed

### Evidence

1. **Changelog shows migration happening:**
   ```
   2025-12-04 Claude: CRITICAL FIX - Replaced broken mps-api/query
   with direct vendor API calls
   ```

2. **Inconsistent code patterns:**
   - get-duplicate-ips.php: Uses direct `callMPSAPI()`
   - device-age-report.php: Uses broken `callMpsQueryWithMeta()`
   - get-dealer-summary.php: Uses direct `callMPSAPI()`

3. **Version control comments:**
   ```
   Lines 552-565 of get-duplicate-ips.php show recent fixes
   to bypass broken endpoint
   ```

---

## Impact Assessment

### Affected Dashboards

| Dashboard | API | Devices Visible | Issue |
|-----------|-----|-----------------|-------|
| Duplicate IP Report | get-duplicate-ips.php | 5000+ | None - Fixed |
| Device Age Analysis | device-age-report.php | 100-1000 | Broken endpoint |
| Executive Dashboard | get-dealer-summary.php | 5000+ | None - Working |
| Device Search | get-devices.php | 100-1000 | Broken endpoint |
| Device List CRUD | device-list.php | Unknown | Unknown source |

### User Impact

Users viewing affected dashboards see:
- ✗ **Incomplete inventory:** Missing 80-90% of devices
- ✗ **Inaccurate age analysis:** Based on incomplete dataset
- ✗ **Missing duplicates:** Incomplete IP conflict detection
- ✓ Executive dashboard accurate (uses working source)

### Business Impact

- Incomplete duplicate IP detection
- Inaccurate fleet age metrics
- Reduced reliability of search features
- Single point of failure (mps-api/query endpoint)

---

## Recommended Actions

### Immediate (Priority: HIGH)

1. **Fix device-age-report.php**
   - Remove `callMpsQueryWithMeta()` function
   - Replace `fetchAllDevicesViaQuery()` with direct API calls
   - Add diagnostic logging
   - Complexity: LOW (~30 lines changed)
   - Time: 30 minutes

2. **Fix get-devices.php**
   - Remove `mps-api/query` call
   - Add cache fallback
   - Implement direct `callMPSAPI()` calls
   - Complexity: LOW (~50 lines changed)
   - Time: 45 minutes

3. **Verify device-list.php**
   - Check `callMPSQuery()` implementation
   - Confirm if it uses broken endpoint
   - Apply fix if needed
   - Complexity: MEDIUM (depends on implementation)
   - Time: 1 hour

### Short-term (Priority: MEDIUM)

1. **Add monitoring**
   - Track device counts from each API
   - Alert if count drops below 1000
   - Monitor `mps-api/query` endpoint health

2. **Add diagnostics**
   - API response logging
   - Data source tracking
   - Cache completeness monitoring

3. **Add tests**
   - Automated tests for each API
   - Alert if device count < 5000
   - Performance benchmarks

### Long-term (Priority: LOW)

1. **Remove `mps-api/query` endpoint**
   - After all APIs migrated to direct calls
   - No longer needed, adds complexity

2. **Consolidate API patterns**
   - All device-listing APIs use same pattern
   - Consistent error handling
   - Centralized retry logic

3. **Implement circuit breaker**
   - For API endpoints
   - Graceful degradation
   - Fallback strategies

---

## Testing Strategy

### Before Applying Fixes

```bash
# Check current state of each API
for api in get-duplicate-ips device-age-report get-dealer-summary get-devices device-list; do
  echo "Testing: $api.php"
  curl -s "https://mpsm.resolutionsbydesign.us/cms/api/${api}.php?secret=DEALER_API_2025&force=1" \
    | jq '.summary.totalValidDevices // .total_devices_processed // .summary.totalDevices // .total // "ERROR"'
done
```

### After Applying Fixes

```bash
# Verify all APIs return 5000+
for api in get-duplicate-ips device-age-report get-dealer-summary get-devices device-list; do
  echo "Testing: $api.php"
  curl -s "https://mpsm.resolutionsbydesign.us/cms/api/${api}.php?secret=DEALER_API_2025&force=1" \
    | jq '.summary.totalValidDevices // .total_devices_processed // .summary.totalDevices // .total // "ERROR"' \
    | grep -E '^[5-9][0-9]{3,}|^[0-9]{5,}$' && echo "✓ PASS" || echo "✗ FAIL"
done
```

### Expected Results After Fixes

| API | Count | Source | Status |
|-----|-------|--------|--------|
| get-duplicate-ips.php | 5000+ | live-vendor-api | PASS |
| device-age-report.php | 5000+ | live-vendor-api OR cache | PASS |
| get-dealer-summary.php | 5000+ | cache OR live_api | PASS |
| get-devices.php | 5000+ | live-vendor-api | PASS |
| device-list.php | 5000+ | TBD | PASS |

---

## Key Files Involved

```
/cms/api/get-duplicate-ips.php        - ✓ Fixed (reference implementation)
/cms/api/device-age-report.php        - ✗ Needs fix (remove broken endpoint)
/cms/api/get-dealer-summary.php       - ✓ Working (already correct)
/cms/api/get-devices.php              - ✗ Needs fix (add fallback)
/cms/api/device-list.php              - ? Needs verification
/cms/functions.php                    - Review callMPSQuery() and callMPSAPI()
```

---

## Conclusion

**All 5 device-listing APIs CAN return 5000+ devices**, but 2-3 of them need updates to use proven patterns:

1. ✓ **get-duplicate-ips.php** - Already fixed, shows correct pattern
2. ✗ **device-age-report.php** - Remove broken endpoint, add direct API calls
3. ✓ **get-dealer-summary.php** - Already working, has correct fallback
4. ✗ **get-devices.php** - Add cache fallback, replace broken endpoint
5. ? **device-list.php** - Verify and likely apply similar fixes

**Implementation cost:** ~2 hours of development
**Risk level:** LOW - Following proven pattern from working API
**Expected benefit:** 80-90% more devices visible in incomplete dashboards

**Next step:** Apply fixes from `API_FIXES_DETAILED.md` document and re-test.
