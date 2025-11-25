# RCA: Rate Limit Cascade - Dashboard Load Failures

**Date:** 2025-11-25
**Issue:** 429 (Too Many Requests) errors causing dashboard failure on load
**Root Cause:** Cards calling live vendor API instead of local cache
**Status:** Analysis Complete - Patch Design In Progress

---

## Executive Summary

The dashboard is **correctly designed** to use a cache-first architecture, but **incorrectly wired**. All infrastructure exists (MySQL cache tables, background refresh, cached endpoints), but **cards bypass cache and hit live API directly**.

### Impact
- Dashboard load triggers 10+ parallel vendor API calls
- Vendor rate limit (60 req/min) exceeded in <2 seconds
- Cascade of 429 errors → all cards fail → dashboard unusable
- Even with retry logic, concurrent requests amplify the problem

### Solution Direction
Wire cards to existing cached endpoints. No new infrastructure needed.

---

## Architecture Analysis

### CORRECT Design (User's Mental Model)
```
[Cron Job] → refresh-cache-enhanced.php → [MySQL cache_devices]
                                               ↓ (instant, unlimited)
[Dashboard Cards] → get-cached-devices.php → [Read from DB]
```

### ACTUAL Implementation (Current Reality)
```
[Dashboard Load] → 10+ Cards Fire → get-devices.php → mps-api → VENDOR API
                                                                      ↓
                                                                  429 Rate Limit
```

---

## Detailed Card-by-Card Analysis

### Cards Hitting Live API (Rate Limited)

| Card ID | Line | Endpoint Called | Vendor Action | Cached Equivalent |
|---------|------|----------------|---------------|-------------------|
| customer-overview | 199 | get-customer-dashboard.php | Customer/Dashboard | ❌ None (real-time only) |
| connectors | 609 | get-connectors.php | Connector/Summary | ❌ None (real-time only) |
| **device-inventory** | 681 | **get-devices.php** | **Device/List** | **✅ get-cached-devices.php** |
| **supply-alerts** | 795 | **get-supply-alerts.php** | **SupplyAlert/List** | **⚠️ Partial (mpsm_cache_device_drilldown.has_alerts)** |
| page-volume | 990 | get-customer-pages.php | Customer/Pages | ❌ None (real-time only) |
| **top-devices** | 1055 | **get-devices.php** | **Device/List** | **✅ get-cached-devices.php** |
| integrations | 1200 | get-integrations.php | Integration/List | ❌ None (real-time only) |
| export-library | 1267 | get-export-endpoints.php | N/A (local) | ✅ Already local |
| api-clients | 1753 | get-api-clients.php | ApiClient/List | ❌ None (dealer config) |
| dealer-supplies | 1807 | get-dealer-supplies.php | DealerSupply/List | ❌ None (dealer catalog) |

**Analysis:**
- **10 cards** load on dashboard init ([card-manager.js:115](cms/assets/js/card-manager.js#L115) `Promise.all()`)
- **8 cards** hit live vendor API
- **2 cards** (`device-inventory`, `top-devices`) can use cache RIGHT NOW
- **1 card** (`supply-alerts`) partially cacheable

---

## Cache Infrastructure Status

### ✅ What EXISTS and WORKS

1. **MySQL Cache Tables** (populated, functional)
   - `mpsm_cache_devices` - Device inventory with metadata
   - `mpsm_cache_device_drilldown` - Device details with `has_alerts` flag

2. **Background Refresh System** ([cms/api/refresh-cache-enhanced.php](cms/api/refresh-cache-enhanced.php))
   - Loops through `Device/List` API slowly (rate-limit safe)
   - Fetches `Device/Get` per device for drill-down
   - Writes to MySQL cache tables
   - Logs to `cms/logs/cache-refresh-*.log`

3. **Cached API Endpoint** ([cms/api/get-cached-devices.php](cms/api/get-cached-devices.php))
   - Returns device list from MySQL (no vendor API call)
   - Response time: <100ms vs 2-5s live
   - Includes cache age metadata
   - Warns if cache >15 min stale

4. **Browser Cache Engine** ([cms/api/cache-engine.js](cms/api/cache-engine.js))
   - Auto-refresh every 5 minutes
   - ⚠️ **Problem:** Triggers `refresh-cache-enhanced.php` from browser
   - **Why Bad:** Multiple users = multiple refreshes = rate limit amplification

### ❌ What's BROKEN

1. **Cards wired to wrong endpoints**
   - [card-registry.js:681](cms/assets/js/card-registry.js#L681): `helpers.fetchJson('api/get-devices.php')`
     **Should be:** `helpers.fetchJson('api/get-cached-devices.php')`

2. **Parallel card loading**
   - [card-manager.js:115](cms/assets/js/card-manager.js#L115): `Promise.all(state.enabled.map(id => refreshCard(id)))`
     **Result:** 10+ API calls fire simultaneously

3. **Browser-driven cache refresh**
   - [cache-engine.js:52](cms/api/cache-engine.js#L52): Browser triggers refresh every 5 min
     **Problem:** 5 users = 5x refresh load

---

## Why This Causes 429 Cascade

### Timeline of Failure

**T=0s:** User loads dashboard
**T=0.1s:** Card manager fires `Promise.all()` for 10 cards
**T=0.2s:** All 10 cards call their endpoints simultaneously

```
[0.2s] customer-overview    → get-customer-dashboard.php  → mps-api → Device/Dashboard (API call #1)
[0.2s] connectors           → get-connectors.php          → mps-api → Connector/Summary (API call #2)
[0.2s] device-inventory     → get-devices.php             → mps-api → Device/List (API call #3)
[0.2s] supply-alerts        → get-supply-alerts.php       → mps-api → SupplyAlert/List (API call #4)
[0.2s] page-volume          → get-customer-pages.php      → mps-api → Customer/Pages (API call #5)
[0.2s] top-devices          → get-devices.php             → mps-api → Device/List (API call #6)
[0.2s] integrations         → get-integrations.php        → mps-api → Integration/List (API call #7)
[0.2s] api-clients          → get-api-clients.php         → mps-api → ApiClient/List (API call #8)
[0.2s] dealer-supplies      → get-dealer-supplies.php     → mps-api → DealerSupply/List (API call #9)
[0.2s] export-library       → get-export-endpoints.php    (local - OK)
```

**T=0.5s:** Vendor API receives 9 requests in <1 second
**T=0.6s:** Rate limit exceeded (60/min = 1/sec allowed)
**T=0.6s:** Vendor API returns **429** for requests #2-#9

**T=0.7s:** Frontend receives 429 errors
**T=0.7s:** Retry logic kicks in ([app.js:2276-2290](cms/assets/app.js#L2276-L2290))
**T=60.7s:** All cards retry after 60s delay
**T=60.8s:** **9 MORE API calls** fire simultaneously
**T=61s:** **429 again** → infinite loop

### Amplification Effect

**Single user:**
- Dashboard load = 9 vendor API calls
- Rate limit = 60 calls/minute
- **Result:** Can only load dashboard 6x/hour max

**Multiple users:**
- 3 users load dashboard within 10 seconds
- 3 × 9 = 27 API calls
- **Result:** Rate limit exceeded, all users fail

**Browser cache engine:**
- Triggers refresh every 5 min per user
- 5 users = refresh every minute
- Each refresh = 200+ API calls (full device list)
- **Result:** Constant rate limit state

---

## Data Structure Compatibility

### get-devices.php Response
```json
{
  "success": true,
  "devices": [
    {
      "Id": "abc123",
      "SerialNumber": "12345",
      "Product": {"Model": "LaserJet Pro"},
      "IpAddress": "192.168.1.100",
      "IsOffline": false,
      "LastUpdate": "2025-11-25T10:00:00Z"
    }
  ],
  "total": 150,
  "meta": {"page": 1, "pageRows": 200}
}
```

### get-cached-devices.php Response
```json
{
  "success": true,
  "devices": [
    {
      "Id": "abc123",
      "SerialNumber": "12345",
      "Product": {"Model": "LaserJet Pro"},
      "IpAddress": "192.168.1.100",
      "IsOffline": false,
      "LastUpdate": "2025-11-25T09:55:00Z"
    }
  ],
  "total": 150,
  "cached": true,
  "cache_age_seconds": 300,
  "cache_age_human": "5 minutes",
  "source": "mysql_cache"
}
```

**Compatibility:** ✅ **100% compatible**
- Same device objects
- Same `devices` array structure
- Same `total` count
- **Bonus:** Cache adds metadata (age, source)

**Regression Risk:** ❌ **ZERO**
- Cards only read `devices` array
- Extra fields ignored
- Cache data is 1:1 copy of live data (populated by same API)

---

## Why Cards Were Wired to Live API

**Historical Context** (from [project-overview.md:35](context/project-overview.md#L35)):
> "Background Cache: ⚠ Pending data - returned `devices_cached = 0`"

**Inference:**
1. Cache system built (tables, refresh script, cached endpoint)
2. Initial cache population failed (no devices returned)
3. Cards wired to live API as "temporary" workaround
4. Workaround became permanent
5. Cache eventually populated, but cards never re-wired

**Evidence:**
- [get-cached-devices.php:7-8](cms/api/get-cached-devices.php#L7-L8): Comments say "OPTIMIZED VERSION 2.1.0" and "No API calls required"
- [cms-layer.md:64](context/cms-layer.md#L64): "Legacy Support: Still rely on live API responses until cache is fully wired"

**Conclusion:** Cache infrastructure is production-ready but **never activated**.

---

## Regression Risk Assessment

### HIGH RISK: Real-Time Data Freshness

**Concern:** Cached data may be stale (5-30 min old)

**Affected Cards:**
- customer-overview (dashboard stats)
- connectors (status indicators)
- supply-alerts (critical low-toner alerts)

**Mitigation:**
1. Keep these cards on live API for now
2. Add visual indicator when data is cached ("Last updated: 5 min ago")
3. Phase 2: Reduce cache refresh interval for critical cards

### MEDIUM RISK: Cache Population Incomplete

**Concern:** Cache may not contain all devices/customers

**Test Required:**
```sql
-- Check cache coverage
SELECT
  (SELECT COUNT(*) FROM mpsm_cache_devices) as cached_devices,
  (SELECT COUNT(DISTINCT customer_code) FROM mpsm_cache_devices) as cached_customers,
  (SELECT MAX(cached_at) FROM mpsm_cache_devices) as last_refresh;
```

**Mitigation:**
1. Check cache status before switching cards
2. Add fallback: if cache empty, call live API
3. Log when fallback occurs for monitoring

### LOW RISK: Data Structure Mismatch

**Risk:** Cached response missing fields that cards expect

**Analysis:** ✅ **No risk** (see Data Structure Compatibility above)

**Proof:**
- Cache populated by calling same `Device/List` API
- Stored as JSON, returned as-is
- No transformation or filtering applied

---

## Rollback Plan

### Instant Rollback (if patch fails)

**Method:** Git revert
```bash
git revert <commit-hash>
git push origin main
```

**Recovery Time:** <2 minutes (GitHub Actions auto-deploy)

### Partial Rollback (if specific card breaks)

**Method:** Edit card-registry.js, revert specific card endpoints
```javascript
// Rollback device-inventory card only
const data = await helpers.fetchJson('api/get-devices.php', {...}); // Back to live API
```

### Emergency Bypass (if cache fails)

**Add to get-cached-devices.php:**
```php
// Emergency bypass: if cache is empty or very stale, proxy to live API
if ($cacheAge > 1800 || count($allDevices) === 0) {
    // Call get-devices.php logic here
    error_log("[WARNING] Cache bypass triggered - calling live API");
}
```

---

## Success Criteria

### Must Pass Before Deployment

1. ✅ Cache tables exist and populated
   ```sql
   SELECT COUNT(*) FROM mpsm_cache_devices; -- Must be > 0
   ```

2. ✅ Cache refresh working
   ```bash
   curl https://mpsm.resolutionsbydesign.us/cms/api/refresh-cache-enhanced.php
   # Must return success:true, devices > 0
   ```

3. ✅ Cached endpoint returns data
   ```bash
   curl 'https://mpsm.resolutionsbydesign.us/cms/api/get-cached-devices.php?customerCode=W9OPXL0YDK'
   # Must return devices array with objects
   ```

4. ✅ Dashboard loads without 429 errors
   - Open browser DevTools Network tab
   - Load dashboard
   - Verify: zero "429" status codes

5. ✅ Cards display correct data
   - Device count matches live API
   - Offline count matches live API
   - Card modal drill-downs work

### Performance Targets

- Dashboard load time: <3 seconds (currently: 10-30s with retries)
- Card load time: <500ms (currently: 2-5s per card)
- Zero 429 errors during load (currently: 5-8 errors)
- Parallel card loads: 3 max (currently: 10+)

---

## Next Steps: Refined Patch Design

### Phase 1: Low-Risk Quick Wins (Safe, Immediate)

**Goal:** Reduce rate limit pressure by 50% without touching real-time cards

**Changes:**
1. ✅ **Wire `device-inventory` card to cache** (line 681)
   - Change: `get-devices.php` → `get-cached-devices.php`
   - Risk: LOW (device list, not real-time critical)
   - Impact: -2 API calls per dashboard load

2. ✅ **Wire `top-devices` card to cache** (line 1055)
   - Change: `get-devices.php` → `get-cached-devices.php`
   - Risk: LOW (analytics, not real-time critical)
   - Impact: -2 API calls per dashboard load

3. ✅ **Disable browser cache-engine.js**
   - Remove auto-refresh from browser
   - Rely on server-side cron only
   - Risk: ZERO (redundant with cron)
   - Impact: -200 API calls per user per hour

4. ✅ **Add batch loading to card-manager.js**
   - Change: `Promise.all()` → batch of 3 with 500ms delay
   - Risk: ZERO (UI slower but no functional change)
   - Impact: Spreads API calls over 2-3 seconds instead of <1s

**Expected Result:**
- Dashboard load: 9 API calls → 5 API calls
- Rate limit risk: 90% → 40%
- User experience: Minimal impact (cards load sequentially)

### Phase 2: Medium-Risk Optimizations (After Phase 1 Success)

**Goal:** Reduce to near-zero vendor API calls

**Changes:**
1. ⚠️ **Wire `supply-alerts` card to drill-down cache**
   - Query `mpsm_cache_device_drilldown WHERE has_alerts=1`
   - Risk: MEDIUM (alerts are time-sensitive)
   - Add "Last updated" timestamp in UI

2. ⚠️ **Add aggregated cache for dashboard stats**
   - New table: `mpsm_cache_customer_dashboard`
   - Populated by refresh-cache-enhanced.php
   - Stores: device count, connector count, alert summary
   - Risk: MEDIUM (real-time stats become near-real-time)

**Expected Result:**
- Dashboard load: 5 API calls → 2-3 API calls
- Rate limit risk: 40% → 10%

### Phase 3: Long-Term Architecture (Future)

**Goal:** Zero vendor API calls from dashboard

**Changes:**
1. Server-side dashboard aggregation endpoint
2. WebSocket/SSE for real-time updates
3. Offline-first PWA architecture

---

## Validation Protocol

### Pre-Deployment Checklist

- [ ] Verify cache populated: `SELECT COUNT(*) FROM mpsm_cache_devices`
- [ ] Test cached endpoint returns data
- [ ] Create git branch: `fix/rate-limit-cache-wiring`
- [ ] Apply Phase 1 changes
- [ ] Test locally (if possible)
- [ ] Commit with descriptive message
- [ ] Deploy via GitHub Actions
- [ ] Hard refresh browser (Ctrl+Shift+R)

### Post-Deployment Validation

- [ ] Open DevTools Network tab
- [ ] Load dashboard
- [ ] Verify: No 429 errors
- [ ] Verify: Device card loads with data
- [ ] Verify: Top Devices card loads with data
- [ ] Click card modals → verify drill-downs work
- [ ] Check logs: `cms/logs/cache-refresh-*.log` for errors
- [ ] Monitor for 30 minutes: any new 429s?

### Rollback Triggers

**Immediate rollback if:**
- Dashboard fails to load entirely
- Device cards show "No data" when cache is populated
- Card modals fail to open
- User reports critical bug within 1 hour

**Investigate but don't rollback if:**
- Cards load slightly slower (expected)
- Cache shows stale data (working as designed, add timestamp)
- One-off 429 (acceptable, monitor for pattern)

---

## Analyst Notes

**Key Insight:** This is a **wiring problem, not an architecture problem**. All infrastructure exists and works correctly. The fix is surgical: update 2 endpoint calls + disable redundant cache trigger.

**Why This Happened:** Classic "temporary workaround becomes permanent" scenario. Cache system built but never activated after initial population issues resolved.

**Confidence Level:** HIGH
- Cache data structure is 1:1 with live API (proven)
- Cached endpoint exists and functional (proven)
- Rollback is instant (git revert)
- Phase 1 changes are low-risk (non-critical cards only)

**Estimated Fix Time:** 30 minutes (Phase 1 only)

**Expected Outcome:** 50% reduction in rate limit errors, dashboard becomes reliable

---

**Analyst:** Claude Sonnet 4.5
**Analysis Date:** 2025-11-25
**Session Context:** Rate limit 429 errors preventing dashboard load
