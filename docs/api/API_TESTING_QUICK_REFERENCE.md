# API Fleet Coverage Testing - Quick Reference

## Test Command (One-Liner)

```bash
for api in get-duplicate-ips device-age-report get-dealer-summary get-devices; do
  echo "=== $api.php ===";
  curl -s "https://mpsm.resolutionsbydesign.us/cms/api/${api}.php?secret=DEALER_API_2025&force=1&summaryOnly=1" | jq '.summary.totalValidDevices // .total_devices_processed // .summary.totalDevices // .total // "N/A", .summary.source // .source // "unknown"' 2>/dev/null || echo "ERROR";
done
```

---

## Test Results Interpretation

### What to Look For

```
SUCCESS INDICATORS:
✓ Device count ≥ 5000
✓ Source = "cache", "live-vendor-api", "live_api", or "live-api"
✓ Response contains expected fields

WARNING SIGNS:
⚠ Device count = 100-1000
⚠ Source = "live-query" (broken endpoint!)
⚠ Source = "mps-api/query" (broken endpoint!)
⚠ Multiple API failures

ERROR INDICATORS:
✗ Device count = 0
✗ Connection refused
✗ Invalid JSON response
✗ All APIs return same error
```

---

## API Status Matrix

| API | URL | Expected Count | Working | Source |
|-----|-----|-----------------|---------|--------|
| Duplicate IPs | `/cms/api/get-duplicate-ips.php` | 5000+ | ✓ YES | live-vendor-api or cache |
| Device Age | `/cms/api/device-age-report.php` | 5000+ | ✗ NO | live-query (broken!) |
| Dealer Summary | `/cms/api/get-dealer-summary.php` | 5000+ | ✓ YES | cache or live_api |
| Get Devices | `/cms/api/get-devices.php` | 5000+ | ✗ NO | mps-api/query (broken!) |

---

## Test Parameters

```
secret=DEALER_API_2025        # Auth bypass for programmatic testing
force=1                       # Bypass cache, use live API
summaryOnly=1                 # Return summary only (faster)
pageRows=5000                 # Request large page (for get-devices.php)
```

---

## Response Field Mapping

```php
// Device count field by API:
get-duplicate-ips.php    → .summary.totalValidDevices
device-age-report.php    → .total_devices_processed
get-dealer-summary.php   → .summary.totalDevices
get-devices.php          → .total
device-list.php          → .meta.total

// Data source field by API:
get-duplicate-ips.php    → .summary.source
device-age-report.php    → .source
get-dealer-summary.php   → .summary._dataSource
get-devices.php          → n/a (not returned)
device-list.php          → n/a (not returned)
```

---

## Broken Endpoint Indicators

```
If you see any of these, the API is using a broken endpoint:

1. Source = "live-query"
   └─ API is calling: mps-api/query (BROKEN)
   └─ Fix: Replace with direct callMPSAPI()

2. Source = "mps-api/query"
   └─ Same as above

3. Device count 100-1000 with source "live-query"
   └─ Confirmed broken endpoint usage
   └─ Fix: Apply changes from API_FIXES_DETAILED.md

4. Multiple 429 (rate limit) errors in logs
   └─ Endpoint exists but is overloaded
   └─ Consider caching fixes
```

---

## Cache Inspection

```bash
# Check cache device count
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -e \
  "SELECT COUNT(*) as cache_device_count FROM mpsm_cache_devices WHERE is_uninstalled = 0;"

# Expected: 5000+
# If < 1000: APIs will use live fallback (or broken endpoint)
# If 0: All APIs will fail unless fallback works
```

---

## Quick Troubleshooting

### Problem: All APIs return 0 devices

```
Causes:
1. Cache is empty AND fallback is broken
2. API database connection failed
3. All devices marked as uninstalled

Solutions:
1. Check cache count: mysql query above
2. Check database connectivity
3. Verify device status in MPS API
```

### Problem: device-age-report.php returns 100

```
Cause:
- Using broken mps-api/query endpoint
- Cache is incomplete

Fix:
1. Check error logs for [AGE-REPORT-DIAG] markers
2. Apply fix from API_FIXES_DETAILED.md
3. Re-test with force=1
```

### Problem: get-devices.php times out

```
Cause:
- Direct call to broken endpoint
- Endpoint overloaded or down

Fix:
1. Apply cache fallback fix from API_FIXES_DETAILED.md
2. Reduce timeout if repeated
3. Monitor endpoint health
```

---

## Manual Test Script

```php
<?php
// Save as /cms/api/test-fleet-coverage.php

$secret = $_GET['secret'] ?? 'DEALER_API_2025';
$apis = [
    'get-duplicate-ips.php' => 'summary.totalValidDevices',
    'device-age-report.php' => 'total_devices_processed',
    'get-dealer-summary.php' => 'summary.totalDevices',
];

echo "<h1>Fleet Coverage Test</h1>";
echo "<table border='1'><tr><th>API</th><th>Count</th><th>Source</th><th>Status</th></tr>";

foreach ($apis as $api => $field) {
    $url = "https://mpsm.resolutionsbydesign.us/cms/api/{$api}?secret={$secret}&force=1";
    $response = json_decode(file_get_contents($url), true);

    $parts = explode('.', $field);
    $value = $response;
    foreach ($parts as $part) {
        $value = $value[$part] ?? null;
    }

    $source = $response['summary']['source'] ?? $response['source'] ?? 'unknown';
    $status = ($value >= 5000) ? '✓ PASS' : '✗ FAIL';

    echo "<tr><td>$api</td><td>$value</td><td>$source</td><td>$status</td></tr>";
}

echo "</table>";
?>
```

---

## Key Metrics to Track

```javascript
// Metrics to monitor for health:

1. Device Count by API
   - Target: 5000+ for all
   - Alert: < 1000
   - Critical: 0

2. Data Source Distribution
   - Expected: Mostly 'cache' or 'live-vendor-api'
   - Warning: Mostly 'live-query'
   - Critical: All 'mps-api/query'

3. Response Time
   - Cache: < 1 second
   - Live API: 5-30 seconds
   - Critical: > 60 seconds

4. Error Rate
   - Target: 0%
   - Warning: > 1%
   - Critical: > 10%

5. Cache Hit Rate
   - Target: > 90%
   - Warning: 50-90%
   - Critical: < 50%
```

---

## Files Modified Summary

### Fixed APIs
- ✓ `/cms/api/get-duplicate-ips.php` (2025-12-04 Claude)

### Needs Fixes
- ✗ `/cms/api/device-age-report.php` (Lines 22-55, 241-285)
- ✗ `/cms/api/get-devices.php` (Lines 36-173)
- ? `/cms/api/device-list.php` (Verify callMPSQuery implementation)

### Already Working
- ✓ `/cms/api/get-dealer-summary.php` (Lines 65-67, 186-201)

---

## Reference Documentation

See full documentation in:
- `API_FLEET_COVERAGE_ANALYSIS.md` - Detailed technical analysis
- `API_FIXES_DETAILED.md` - Implementation guide with code examples
- `FLEET_COVERAGE_TEST_REPORT.md` - Complete test report with findings

---

## One-Page Checklist

- [ ] Run test command above
- [ ] Verify all APIs return 5000+
- [ ] Check no APIs use 'live-query' source
- [ ] If failures: Apply fixes from API_FIXES_DETAILED.md
- [ ] Re-run test command
- [ ] Verify all PASS
- [ ] Monitor error logs for issues
- [ ] Set up automated monitoring

---

## Contact for Questions

See project repository for:
- Issue tracking: Report device count discrepancies
- PR template: For API fix submissions
- Changelog: Review recent fixes and patterns
