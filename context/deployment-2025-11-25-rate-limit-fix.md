# Deployment Log: Rate Limit Fix Phase 1

**Date:** 2025-11-25
**Commit:** e26e17b
**Status:** DEPLOYED ✅
**Deployment Time:** ~15:46 GMT

---

## Changes Deployed

### 1. card-registry.js - Cache-First Loading
**Lines Modified:** 680-715, 1077-1108

**device-inventory card:**
- Now calls `api/get-cached-devices.php` first
- Automatic fallback to `api/get-devices.php` if cache empty or stale (>30 min)
- Console warnings for debugging

**top-devices card:**
- Same cache-first logic with fallback
- Reduces redundant API calls

**Impact:** -4 API calls per dashboard load (2 per card × 2 cards)

---

### 2. cache-engine.js - Disable Browser Auto-Refresh
**Lines Modified:** 48-58

**Before:**
- Browser triggered cache refresh every 5 minutes
- Multiple users = multiple refreshes = rate limit amplification

**After:**
- Auto-refresh DISABLED
- Manual refresh still available via `window.refreshDeviceCache()`
- Server cron handles cache population

**Impact:** -200+ API calls per user per hour

---

### 3. card-manager.js - Batch Loading
**Lines Modified:** 106-129

**Before:**
- All 10 cards loaded in parallel via `Promise.all()`
- 9 API calls in <1 second → instant rate limit

**After:**
- Load 3 cards at a time with 500ms delay between batches
- Spreads API calls over 2-3 seconds
- Reduces rate limit pressure

**Impact:** Temporal distribution of API calls

---

### 4. Documentation Added
- [context/rca-rate-limit-2025-11-25.md](context/rca-rate-limit-2025-11-25.md) - Complete root cause analysis
- [context/patch-plan-rate-limit-fix.md](context/patch-plan-rate-limit-fix.md) - Implementation plan with Phase 2 roadmap

---

## Expected Improvements

| Metric | Before | After (Target) |
|--------|--------|----------------|
| API calls per load | 9 | 5 |
| Rate limit errors | 90% | 40% |
| Dashboard load time | 10-30s | 3-5s |
| Failed cards | 5-8 | 0-2 |

---

## Testing Checklist

### Pre-Test: Hard Refresh Browser
**CRITICAL:** Browser may have cached old JavaScript files

**Steps:**
1. Open https://mpsm.resolutionsbydesign.us/cms/
2. Press **Ctrl+Shift+R** (Windows) or **Cmd+Shift+R** (Mac)
3. Verify cache cleared (check DevTools → Network tab → "Disable cache" checkbox)

---

### Test 1: Network Tab Inspection

**Steps:**
1. Open DevTools (F12)
2. Go to Network tab
3. Enable "Disable cache" checkbox
4. Load dashboard
5. Monitor requests

**Success Criteria:**
- ✅ Zero 429 (Too Many Requests) errors
- ✅ See requests to `get-cached-devices.php` (not `get-devices.php`)
- ✅ API calls to `/mps-api/query` should be ≤5
- ✅ Cards load sequentially (batch delay visible)

**Expected Console Output:**
```
[CACHE ENGINE] Browser auto-refresh DISABLED - relying on server cron
[CACHE ENGINE] Manual refresh available via: window.refreshDeviceCache()
```

**If Cache Fallback Triggers:**
```
[device-inventory] Cache empty or stale, falling back to live API
[top-devices] Cache empty or stale, falling back to live API
```

---

### Test 2: Device Card Functionality

**Steps:**
1. Verify "Devices" card displays
2. Check device count is correct
3. Check offline count shown
4. Click card to open modal
5. Verify device list displays
6. Verify pagination works
7. Click a device row

**Success Criteria:**
- ✅ Card loads with data
- ✅ Device count matches expected
- ✅ Modal opens
- ✅ Device list renders
- ✅ Pagination functional
- ✅ Row click opens device detail modal

---

### Test 3: Top Devices Card Functionality

**Steps:**
1. Verify "Top Devices" card displays
2. Check top 5 devices shown
3. Check volume numbers displayed
4. Click card to open modal
5. Verify table displays correctly

**Success Criteria:**
- ✅ Card loads with data
- ✅ Shows exactly 5 devices
- ✅ Volume metrics visible
- ✅ Modal opens
- ✅ Table sorts correctly

---

### Test 4: Other Cards (Should Still Work)

**Steps:**
1. Verify "Customer Snapshot" card loads
2. Verify "Connectors" card loads
3. Verify "Supply Alerts" card loads
4. Check all cards display data

**Success Criteria:**
- ✅ All cards load successfully
- ✅ No "No data" errors
- ✅ Modals open correctly

---

### Test 5: Cache Age Verification

**Steps:**
1. Open DevTools Console
2. Run: `fetch('api/get-cached-devices.php').then(r => r.json()).then(console.log)`
3. Check response

**Success Criteria:**
- ✅ `cached: true`
- ✅ `cache_age_seconds` < 1800 (30 min)
- ✅ `devices` array has length > 0
- ✅ `source: "mysql_cache"`

**Example Response:**
```json
{
  "success": true,
  "devices": [...],
  "total": 150,
  "cached": true,
  "cache_age_seconds": 300,
  "cache_age_human": "5 minutes",
  "source": "mysql_cache"
}
```

---

### Test 6: Fallback Logic (Optional)

**Simulate Empty Cache:**
```sql
-- Run on database
TRUNCATE TABLE mpsm_cache_devices;
```

**Steps:**
1. Load dashboard
2. Check console for fallback warnings
3. Verify cards still load (using live API)

**Success Criteria:**
- ✅ Console shows: `[device-inventory] Cache empty or stale, falling back to live API`
- ✅ Cards still display data
- ✅ No errors thrown

**Restore Cache:**
```bash
curl "https://mpsm.resolutionsbydesign.us/cms/api/refresh-cache-enhanced.php?force=1"
```

---

## Monitoring (First 24 Hours)

### Check Every 2 Hours:

**1. Error Logs**
```bash
tail -f cms/logs/php_errors.log
```
Look for:
- JavaScript errors in card-registry.js
- API timeouts
- Database connection issues

**2. Cache Refresh Logs**
```bash
tail -f cms/logs/cache-refresh-*.log
```
Verify cron is running and populating cache

**3. Dashboard Load Success Rate**
- Load dashboard 5 times
- Count 429 errors
- Should be 0-1 per 5 loads (was 4-5 before)

---

## Rollback Procedure (If Needed)

### Immediate Rollback (Git Revert)

```bash
cd "c:\Users\jez.slade\Desktop\Projects\MPSM-Dashboard"
git revert e26e17b
git push origin main
# Wait 2 minutes for deployment
```

**When to Rollback:**
- Dashboard completely fails to load
- Device cards show "No data" when cache is populated
- JavaScript console errors prevent card loading
- User reports critical functionality broken

---

### Partial Rollback (Edit Specific Card)

If only one card breaks, revert just that card:

**Edit card-registry.js on GitHub:**
```javascript
// Revert device-inventory to live API
const data = await helpers.fetchJson('api/get-devices.php', {
    customerCode: context.customerCode,
    dealerCode: context.dealerCode,
    dealerId: context.dealerId,
    pageRows: 200,
    sortColumn: 'AssetNumber',
    sortOrder: 'Asc'
});
```

Commit + push → deploys in 2 min

---

## Success Metrics (After 24 Hours)

### Primary KPIs

**Rate Limit Errors:**
- Before: 90% of dashboard loads
- Target: <40% of dashboard loads
- Measure: Check error logs + user reports

**Dashboard Load Time:**
- Before: 10-30 seconds (with retries)
- Target: 3-5 seconds
- Measure: Chrome DevTools Performance tab

**Card Failure Rate:**
- Before: 5-8 cards fail per load
- Target: 0-2 cards fail per load
- Measure: Console errors + visual inspection

### Secondary KPIs

**API Call Volume:**
- Before: 9 calls per dashboard load
- Target: 5 calls per dashboard load
- Measure: Network tab inspection

**Browser Cache Refresh:**
- Before: Every 5 min per user
- Target: Zero (cron only)
- Measure: Console logs

---

## Known Limitations

**1. Cache Staleness**
- Cache refreshes every 30-60 min (cron schedule)
- Device changes may not appear immediately
- **Mitigation:** Phase 2 will add real-time updates

**2. First Load After Cache Clear**
- If cache is empty, cards fall back to live API
- May trigger rate limit if multiple users load simultaneously
- **Mitigation:** Cron keeps cache populated

**3. No Visual Cache Indicator**
- Users don't see "Last updated: 5 min ago"
- May assume data is real-time
- **Mitigation:** Phase 2 will add cache age UI

---

## Phase 2 Planning (Next Steps)

**After 24hr success + zero rollbacks:**

1. Wire supply-alerts card to cache drill-down table
2. Add aggregated customer dashboard cache
3. Reduce cache refresh interval for critical cards
4. Add cache age indicators in UI
5. Monitor for additional rate limit reduction

**Target:** Reduce rate limit errors from 40% → <10%

---

## Deployment Verification

**Files Deployed:**
- ✅ cms/api/cache-engine.js (Last-Modified: 2025-11-25 15:46 GMT)
- ✅ cms/assets/js/card-manager.js
- ✅ cms/assets/js/card-registry.js
- ✅ context/patch-plan-rate-limit-fix.md
- ✅ context/rca-rate-limit-2025-11-25.md

**Git Status:**
- ✅ Commit: e26e17b
- ✅ Branch: main
- ✅ Remote: origin/main (pushed)

**Deployment Method:** GitHub Actions auto-deploy

---

## Contact & Support

**Issues?**
- Check [context/patch-plan-rate-limit-fix.md](context/patch-plan-rate-limit-fix.md) for troubleshooting
- Review [context/rca-rate-limit-2025-11-25.md](context/rca-rate-limit-2025-11-25.md) for technical details
- Rollback immediately if critical functionality broken

**Questions?**
- Is cache populated? Run: `SELECT COUNT(*) FROM mpsm_cache_devices;`
- Is cron running? Check: `cms/logs/cache-refresh-*.log`
- Are cards loading? Check DevTools Console for warnings

---

**Deployed by:** Claude Sonnet 4.5
**Session:** 2025-11-25 Rate Limit Mitigation
**Status:** LIVE - MONITORING REQUIRED
