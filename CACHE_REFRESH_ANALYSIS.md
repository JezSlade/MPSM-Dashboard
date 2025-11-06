# Cache Refresh Analysis & Solutions

**Date:** 2025-11-06
**Issue:** Drill-down shows 100/200 (50%) instead of thousands of devices
**Root Cause:** API returns ~200 devices total for dealer filter

---

## Problem Analysis

### Issue 1: Device Modal SQL Error ✅ FIXED
**Error:** `SQLSTATE[HY093]: Invalid parameter number`
**Location:** [cms/api/get-device-deep-dive.php:387-392](cms/api/get-device-deep-dive.php#L387-L392)
**Root Cause:** Parameter name mismatch (`:serialNumber` → `:serial`)
**Fix Applied:** Standardized to `:serial` with explicit `bindValue()`

### Issue 2: Device Count Discrepancy
**Reported:** "Thousands of devices" should be cached
**Actual:** ~200 devices in cache
**Drill-Down Coverage:** 100/200 (50%)

### Investigation Results

**API Query Filter:**
```php
'FilterDealerId' => DEFAULT_DEALER_ID,           // 'SZ13qRwU5GtFLj0i_CbEgQ2'
'FilterDealerCodes' => [DEFAULT_DEALER_CODE],    // ['NY06AGDWUQ']
'PageRows' => 50,
'PageNumber' => 1-50 (up to 2,500 devices max)
```

**Pagination Logic:**
- Loops through pages 1-50 (max 2,500 devices)
- Breaks when page returns < 50 devices
- Breaking at page 4 (~200 devices total)

**Conclusion:** The MPS Monitor API is returning ~200 devices for this specific dealer code. This is either:
1. **Correct:** This dealer actually has ~200 devices
2. **Filter Issue:** Need different filtering approach to access "thousands"
3. **API Limitation:** Dealer code restricts visibility

---

## Current Cron Job Configuration

From [context/operations-playbook.md](context/operations-playbook.md#L53-L61):

```bash
# Every 5 minutes: Quick device list refresh (skip drill-down)
*/5 * * * * /usr/bin/timeout 240 /usr/bin/curl -s "https://mpsm.resolutionsbydesign.us/cms/api/refresh-cache-enhanced.php?skipDrilldown=1" >/dev/null 2>&1

# Daily at midnight: Full refresh with drill-down (force)
0 0 * * * /usr/bin/timeout 1800 /usr/bin/curl -s "https://mpsm.resolutionsbydesign.us/cms/api/refresh-cache-enhanced.php?force=1" >/dev/null 2>&1

# Every 30 min: Health check
0,30 * * * * /usr/bin/curl -s "https://mpsm.resolutionsbydesign.us/mps-api/health" >> /home/youruser/logs/mps-api-health.log

# Daily: Database monitor log
0 0 * * * /usr/bin/curl -s "https://mpsm.resolutionsbydesign.us/cms/api/get-database-monitor.php" >> /home/youruser/logs/database-monitor.log

# Weekly: Payload debugger cleanup
0 0 * * 0 /usr/bin/php /home/youruser/public_html/cms/api/cleanup-payload-debug.php >/dev/null 2>&1
```

**Status:** These cron jobs should already be configured in cPanel

---

## Solutions

### Solution 1: Verify Actual Device Count ✅ IMMEDIATE

Check if 200 devices is correct for this dealer:

```sql
-- Check cached device count
SELECT COUNT(*) as device_count FROM mpsm_cache_devices;

-- Check drill-down coverage
SELECT
    COUNT(*) as total_devices,
    COUNT(DISTINCT d.serial_number) as devices_with_drilldown,
    ROUND(COUNT(DISTINCT d.serial_number) * 100.0 / COUNT(*), 2) as coverage_percent
FROM mpsm_cache_devices c
LEFT JOIN mpsm_cache_device_drilldown d ON c.serial_number = d.serial_number;

-- Check latest cache timestamps
SELECT
    MIN(cached_at) as oldest_cache,
    MAX(cached_at) as newest_cache,
    COUNT(*) as count
FROM mpsm_cache_devices;
```

**Expected:** If query returns ~200, then that's the correct count for this dealer

### Solution 2: Test Direct API Query ✅ VERIFY

Test the MPS Monitor API directly:

```bash
curl -X POST "https://mpsm.resolutionsbydesign.us/mps-api/query" \
  -H "Content-Type: application/json" \
  -d '{
    "action": "Device/List",
    "params": {
      "FilterDealerId": "SZ13qRwU5GtFLj0i_CbEgQ2",
      "FilterDealerCodes": ["NY06AGDWUQ"],
      "PageNumber": 1,
      "PageRows": 100
    }
  }'
```

**Check:** How many devices does the API actually return?

### Solution 3: Expand Dealer Filtering (IF NEEDED)

If the API should return thousands but doesn't:

**Option A:** Remove dealer filtering (get ALL devices)
```php
$installedBaseParams = [
    'FilterDealerId' => null,  // Remove dealer filter
    'FilterDealerCodes' => null,  // Remove dealer code filter
    'PageRows' => 50,
    'SortColumn' => 'Id',
    'SortOrder' => 0,
];
```

**Option B:** Add multiple dealer codes
```php
'FilterDealerCodes' => ['NY06AGDWUQ', 'OTHER_CODE_1', 'OTHER_CODE_2'],
```

**Option C:** Query by customer codes instead
```php
'FilterCustomerCodes' => ['CUSTOMER1', 'CUSTOMER2', ...],  // Get customers from CMS
```

### Solution 4: Optimize Drill-Down Processing ✅ ALREADY DONE

Changes already deployed:
- ✅ Timeout: 10min → 20min
- ✅ API delay: 50ms → 250ms
- ✅ Retry attempts: 6 → 10
- ✅ Cron job: Every 5 min quick refresh
- ✅ Cron job: Daily full drill-down

**Expected:** Drill-down should reach 100% coverage for available devices

### Solution 5: Add Logging for Debugging

Create log directory and enable logging:

```bash
# On server via SSH or cPanel Terminal
mkdir -p /home/youruser/logs
chmod 755 /home/youruser/logs

# Test cache refresh manually with logging
curl "https://mpsm.resolutionsbydesign.us/cms/api/refresh-cache-enhanced.php" 2>&1 | tee ~/logs/manual-refresh.log
```

---

## Recommended Action Plan

### Phase 1: Verify Current State (5 minutes)

1. **Check database device count:**
   ```sql
   SELECT COUNT(*) FROM mpsm_cache_devices;
   SELECT COUNT(*) FROM mpsm_cache_device_drilldown;
   ```

2. **Test API directly:**
   ```bash
   curl -X POST "https://mpsm.resolutionsbydesign.us/mps-api/query" \
     -H "Content-Type: application/json" \
     -d '{"action":"Device/List","params":{"FilterDealerId":"SZ13qRwU5GtFLj0i_CbEgQ2","PageNumber":1,"PageRows":100}}'
   ```

3. **Check cron jobs:**
   - Login to cPanel
   - Go to "Cron Jobs"
   - Verify 5 cron jobs are configured
   - Check "Last Run" timestamps

### Phase 2: Deploy SQL Fix (2 minutes) ✅ IN PROGRESS

- [x] Fix SQL parameter bug in get-device-deep-dive.php
- [ ] Commit and push changes
- [ ] Wait for GitHub Actions deployment
- [ ] Test device modal drill-down

### Phase 3: Resolve Device Count Issue (Depends on findings)

**IF API returns ~200 devices (dealer only has 200):**
- ✅ System is working correctly
- ✅ Focus on getting 100% drill-down coverage for these 200
- ✅ Cron jobs will maintain cache freshness

**IF API returns thousands but cache has 200:**
- Investigate pagination logic
- Check for early breaks or errors
- Review API response structure
- Enable logging to diagnose

**IF dealer should access thousands across multiple customers:**
- Modify filtering strategy (Option B or C above)
- Test with expanded filters
- Update refresh-cache-enhanced.php

### Phase 4: Verify Cron Jobs (5 minutes)

1. **Check cPanel Cron configuration:**
   - Every 5 min: Quick refresh
   - Daily midnight: Full refresh with drill-down
   - Verify paths and timeouts match documentation

2. **Monitor first cron execution:**
   ```bash
   # Check if cache is updating every 5 minutes
   # Via phpMyAdmin or MySQL:
   SELECT MAX(cached_at) FROM mpsm_cache_devices;
   # Wait 5 minutes, check again - should update
   ```

3. **Verify drill-down completion:**
   ```sql
   SELECT
       COUNT(*) as devices,
       COUNT(DISTINCT d.serial_number) as with_drilldown,
       ROUND(COUNT(DISTINCT d.serial_number) * 100.0 / COUNT(*), 1) as percent
   FROM mpsm_cache_devices c
   LEFT JOIN mpsm_cache_device_drilldown d ON c.serial_number = d.serial_number;
   ```
   **Expected:** Coverage should reach 100% after daily full refresh

---

## Success Criteria

### Device Modal ✅
- [x] SQL error fixed (parameter mismatch)
- [ ] Device drill-down loads without errors
- [ ] Panel message history displays
- [ ] Meter readings show correctly
- [ ] Supply alerts display

### Cache Population
- [ ] Device count matches API response
- [ ] Drill-down coverage reaches 100%
- [ ] Cache updates every 5 minutes (quick)
- [ ] Full drill-down runs daily
- [ ] No stalls or rate limit exhaustion

### Performance
- [ ] Device modal loads in <2s with cache
- [ ] Dashboard shows accurate device counts
- [ ] Admin panel shows correct drill-down %
- [ ] No timeout errors on device queries

---

## Troubleshooting Guide

### Issue: "Still showing 100/200"

**Check:**
1. Has cron run since deployment? (check `cached_at` timestamps)
2. Is lock file stuck? (check `/cms/api/cache/enhanced-refresh.lock`)
3. Are cron jobs configured in cPanel?
4. Did the API response change?

**Fix:**
```bash
# Force refresh manually
curl "https://mpsm.resolutionsbydesign.us/cms/api/refresh-cache-enhanced.php?force=1"

# Wait 10-20 minutes, check coverage
curl "https://mpsm.resolutionsbydesign.us/cms/api/get-database-monitor.php"
```

### Issue: "Device modal still errors"

**Check:**
1. Was get-device-deep-dive.php deployed?
2. Check browser console for specific error
3. Test API directly:
   ```bash
   curl "https://mpsm.resolutionsbydesign.us/cms/api/get-device-deep-dive.php?serialNumber=DEVICE123"
   ```

### Issue: "Cron jobs not running"

**Check:**
1. cPanel → Cron Jobs → verify all 5 jobs listed
2. Check cPanel error log for cron failures
3. Verify curl is accessible: `/usr/bin/curl --version`
4. Test URL manually: `curl "https://mpsm.resolutionsbydesign.us/cms/api/refresh-cache-enhanced.php?skipDrilldown=1"`

---

## Next Steps

1. ✅ **Deploy SQL fix** (in progress)
2. **Verify device count** (user to check API response)
3. **Confirm cron jobs** (user to check cPanel)
4. **Monitor drill-down** (should reach 100% after daily run)
5. **Test device modal** (should work after deployment)

---

**Document Created:** 2025-11-06
**Status:** SQL fix ready for deployment, waiting for device count verification
**Next Update:** After SQL fix deployment and API verification
