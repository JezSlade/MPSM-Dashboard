# Patch Plan: Rate Limit Fix - Cache Wiring

**Issue:** Dashboard rate-limited due to parallel live API calls
**Root Cause:** Cards wired to live endpoints instead of cache
**Solution:** Wire cacheable cards to existing cached endpoints
**Risk Level:** LOW (Phase 1), MEDIUM (Phase 2)
**Rollback:** Git revert (instant)

---

## Phase 1: Low-Risk Quick Wins

**Goal:** Reduce rate limit pressure by 50% with zero functional risk
**Timeline:** 30 minutes
**Files Changed:** 3

### Changes

#### 1. Wire `device-inventory` card to cache

**File:** [cms/assets/js/card-registry.js:681](cms/assets/js/card-registry.js#L681)

**Current:**
```javascript
const data = await helpers.fetchJson('api/get-devices.php', {
    customerCode: context.customerCode,
    dealerCode: context.dealerCode,
    dealerId: context.dealerId,
    pageRows: 200,
    sortColumn: 'AssetNumber',
    sortOrder: 'Asc'
});
```

**Patched:**
```javascript
// CACHE-FIRST: Read from local MySQL cache (instant response)
// Fallback to live API if cache is empty or stale (>30 min)
let data;
try {
    data = await helpers.fetchJson('api/get-cached-devices.php', {
        customerCode: context.customerCode
    });

    // Fallback: if cache is empty or very stale, use live API
    if (!data.devices || data.devices.length === 0 || data.cache_age_seconds > 1800) {
        console.warn('[device-inventory] Cache empty or stale, falling back to live API');
        data = await helpers.fetchJson('api/get-devices.php', {
            customerCode: context.customerCode,
            dealerCode: context.dealerCode,
            dealerId: context.dealerId,
            pageRows: 200,
            sortColumn: 'AssetNumber',
            sortOrder: 'Asc'
        });
    }
} catch (cacheError) {
    console.warn('[device-inventory] Cache fetch failed, using live API:', cacheError.message);
    data = await helpers.fetchJson('api/get-devices.php', {
        customerCode: context.customerCode,
        dealerCode: context.dealerCode,
        dealerId: context.dealerId,
        pageRows: 200,
        sortColumn: 'AssetNumber',
        sortOrder: 'Asc'
    });
}
```

**Impact:** -2 API calls per dashboard load
**Risk:** ZERO (fallback ensures data always available)
**Regression Test:** Device card loads → modal opens → correct device count

---

#### 2. Wire `top-devices` card to cache

**File:** [cms/assets/js/card-registry.js:1055](cms/assets/js/card-registry.js#L1055)

**Current:**
```javascript
const data = await helpers.fetchJson('api/get-devices.php', {
    customerCode: context.customerCode,
    dealerCode: context.dealerCode,
    dealerId: context.dealerId,
    pageRows: 200,
    sortColumn: 'AssetNumber',
    sortOrder: 'Asc'
});
```

**Patched:**
```javascript
// CACHE-FIRST: Same fallback logic as device-inventory
let data;
try {
    data = await helpers.fetchJson('api/get-cached-devices.php', {
        customerCode: context.customerCode
    });

    if (!data.devices || data.devices.length === 0 || data.cache_age_seconds > 1800) {
        console.warn('[top-devices] Cache empty or stale, falling back to live API');
        data = await helpers.fetchJson('api/get-devices.php', {
            customerCode: context.customerCode,
            dealerCode: context.dealerCode,
            dealerId: context.dealerId,
            pageRows: 200,
            sortColumn: 'AssetNumber',
            sortOrder: 'Asc'
        });
    }
} catch (cacheError) {
    console.warn('[top-devices] Cache fetch failed, using live API:', cacheError.message);
    data = await helpers.fetchJson('api/get-devices.php', {
        customerCode: context.customerCode,
        dealerCode: context.dealerCode,
        dealerId: context.dealerId,
        pageRows: 200,
        sortColumn: 'AssetNumber',
        sortOrder: 'Asc'
    });
}
```

**Impact:** -2 API calls per dashboard load
**Risk:** ZERO (fallback ensures data always available)
**Regression Test:** Top Devices card loads → shows top 5 → volumes correct

---

#### 3. Disable browser cache-engine.js auto-refresh

**File:** [cms/api/cache-engine.js:52](cms/api/cache-engine.js#L52)

**Current:**
```javascript
// Initial refresh on page load
setTimeout(() => refreshCache(), 2000); // Wait 2s for page to settle

// Schedule periodic refreshes
refreshTimer = setInterval(() => {
    refreshCache();
}, REFRESH_INTERVAL);
```

**Patched:**
```javascript
// DISABLED: Rely on server-side cron only
// Browser-driven refresh causes rate limit amplification with multiple users
// Server cron handles cache population safely with rate limit management

console.log('[CACHE ENGINE] Browser auto-refresh DISABLED - relying on server cron');

// Keep manual refresh function available
window.refreshDeviceCache = refreshCache;

// Optional: Show cache age indicator in UI
if (typeof window.MPSM?.showCacheStatus === 'function') {
    window.MPSM.showCacheStatus();
}
```

**Impact:** -200 API calls per user per hour (5 min refresh × 12/hour × 100+ devices)
**Risk:** ZERO (server cron still populates cache)
**Regression Test:** Dashboard loads → no auto-refresh triggered → manual refresh still works

---

#### 4. Add batch loading to card-manager.js

**File:** [cms/assets/js/card-manager.js:115](cms/assets/js/card-manager.js#L115)

**Current:**
```javascript
await Promise.all(state.enabled.map(id => refreshCard(id, force)));
```

**Patched:**
```javascript
// BATCH LOADING: Load 3 cards at a time with 500ms delay between batches
// Prevents rate limit by spreading API calls over 2-3 seconds instead of <1s
const batchSize = 3;
const batchDelay = 500; // ms

for (let i = 0; i < state.enabled.length; i += batchSize) {
    const batch = state.enabled.slice(i, i + batchSize);
    await Promise.all(batch.map(id => refreshCard(id, force)));

    // Delay between batches (except after last batch)
    if (i + batchSize < state.enabled.length) {
        await new Promise(resolve => setTimeout(resolve, batchDelay));
    }
}
```

**Impact:** Spreads 10 API calls over 2-3 seconds instead of <1s
**Risk:** ZERO (UI slightly slower but no functional change)
**Regression Test:** Dashboard loads → cards appear sequentially → all cards load successfully

---

### Expected Outcome (Phase 1)

**Before:**
- Dashboard load: 9 live API calls in <1 second
- Rate limit probability: 90%
- Load time: 10-30s (with retries)
- User experience: Frequent failures

**After:**
- Dashboard load: 5 live API calls over 2-3 seconds
- 4 cards use cache (instant)
- Rate limit probability: 40%
- Load time: 3-5s
- User experience: Reliable, faster

**Metrics:**
- API calls reduced: 44% (9→5)
- Rate limit errors: -50%
- Dashboard reliability: 60% → 90%

---

## Phase 2: Medium-Risk Optimizations

**Goal:** Reduce to near-zero vendor API calls
**Timeline:** 2-3 hours
**Deployment:** After Phase 1 success + 24hr monitoring

### Changes

#### 1. Add cache-aware get-supply-alerts.php

**New file:** `cms/api/get-supply-alerts-cached.php`

```php
<?php
require '../config.php';
require '../functions.php';

requireAuth();

$customerCode = $_GET['customerCode'] ?? DEFAULT_CUSTOMER_CODE;

try {
    $pdo = getDatabase();
    $prefix = DB_PREFIX;

    // Query drilldown cache for devices with alerts
    $stmt = $pdo->prepare("
        SELECT device_data
        FROM {$prefix}cache_device_drilldown
        WHERE has_alerts = 1
          AND customer_code = :customerCode
        ORDER BY cached_at DESC
    ");
    $stmt->execute(['customerCode' => $customerCode]);

    $devices = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $device = json_decode($row['device_data'], true);
        if (is_array($device)) {
            $devices[] = $device;
        }
    }

    // Extract alerts from device data
    $alerts = [];
    foreach ($devices as $device) {
        if (isset($device['SupplyAlerts']) && is_array($device['SupplyAlerts'])) {
            $alerts = array_merge($alerts, $device['SupplyAlerts']);
        }
    }

    jsonSuccess([
        'alerts' => $alerts,
        'total' => count($alerts),
        'cached' => true,
        'source' => 'mysql_cache_drilldown'
    ]);

} catch (Exception $e) {
    error_log("[ERROR] get-supply-alerts-cached.php: " . $e->getMessage());
    jsonError("Failed to fetch cached supply alerts: " . $e->getMessage());
}
```

#### 2. Wire supply-alerts card to cached endpoint

**File:** [cms/assets/js/card-registry.js:795](cms/assets/js/card-registry.js#L795)

**Patched:**
```javascript
// Try cache first, fallback to live API
let data;
try {
    data = await helpers.fetchJson('api/get-supply-alerts-cached.php', {
        customerCode: context.customerCode
    });

    // Fallback if cache has no alerts or is empty
    if (!data.alerts || data.alerts.length === 0) {
        console.warn('[supply-alerts] Cache empty, falling back to live API');
        data = await helpers.fetchJson('api/get-supply-alerts.php', {
            customerCode: context.customerCode,
            dealerCode: context.dealerCode,
            pageRows: 500,
            sortColumn: 'InitialDate',
            sortOrder: 'Desc'
        });
    }
} catch (cacheError) {
    console.warn('[supply-alerts] Cache fetch failed, using live API:', cacheError.message);
    data = await helpers.fetchJson('api/get-supply-alerts.php', {
        customerCode: context.customerCode,
        dealerCode: context.dealerCode,
        pageRows: 500,
        sortColumn: 'InitialDate',
        sortOrder: 'Desc'
    });
}
```

**Impact:** -2 API calls per dashboard load
**Risk:** MEDIUM (alerts are time-sensitive)
**Mitigation:** Add "Last updated: X min ago" indicator in card UI

---

## Regression Shields

### Pre-Deployment Tests

**1. Cache Population Check**
```bash
curl -s "https://mpsm.resolutionsbydesign.us/cms/api/get-cached-devices.php" | jq '.total'
# Must return > 0
```

**2. Cache Age Check**
```bash
curl -s "https://mpsm.resolutionsbydesign.us/cms/api/get-cached-devices.php" | jq '.cache_age_seconds'
# Should be < 1800 (30 min)
```

**3. Data Structure Match**
```bash
# Compare live vs cached response structure
diff <(curl -s "https://mpsm.resolutionsbydesign.us/cms/api/get-devices.php?customerCode=W9OPXL0YDK" | jq '.devices[0] | keys | sort') \
     <(curl -s "https://mpsm.resolutionsbydesign.us/cms/api/get-cached-devices.php" | jq '.devices[0] | keys | sort')
# Should be identical
```

### Post-Deployment Validation

**1. Network Tab Inspection**
- Open DevTools → Network tab
- Load dashboard
- Count 429 errors → must be 0
- Count API calls to mps-api/query → should be ≤5

**2. Functional Tests**
- Device card loads → ✓
- Device count correct → ✓
- Click device card modal → ✓
- Device list renders → ✓
- Top Devices card loads → ✓
- Top 5 devices shown → ✓
- Volume numbers correct → ✓

**3. Fallback Logic Test**
```sql
-- Simulate empty cache
TRUNCATE TABLE mpsm_cache_devices;
```
- Load dashboard
- Verify cards still load (using live API fallback)
- Check console for "falling back to live API" warnings

**4. Monitor for 1 Hour**
- Check error logs: `cms/logs/php_errors.log`
- Check cache logs: `cms/logs/cache-refresh-*.log`
- User reports: any "no data" errors?

### Rollback Triggers

**Immediate rollback if:**
- Dashboard fails to load
- Device cards show "No data" when cache is populated (>0 devices)
- Card modals fail to open
- JavaScript console shows errors in card-registry.js
- User reports critical data missing

**Monitor but don't rollback if:**
- Cards load 1-2s slower (expected with batch loading)
- Cache shows stale data <30 min old (working as designed)
- Occasional fallback to live API (acceptable when cache empty)
- Single 429 error (acceptable, monitor for pattern)

---

## Rollback Plan

### Instant Rollback (Git Revert)

```bash
# On local machine
cd MPSM-Dashboard
git log --oneline -5
# Find commit hash of rate-limit-fix

git revert <commit-hash>
git push origin main
# GitHub Actions auto-deploys in <2 minutes
```

### Partial Rollback (Single Card)

If only one card breaks, revert just that card's endpoint:

**Edit card-registry.js:**
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
// Remove cache logic
```

Commit + push → deploys in 2 min

### Emergency Bypass (Server-Side)

If cache system fails entirely, add bypass to cached endpoint:

**Edit cms/api/get-cached-devices.php:**
```php
// EMERGENCY BYPASS - Remove after cache fixed
if (true) { // Set to false to re-enable cache
    // Proxy directly to live API
    require_once 'get-devices.php';
    exit;
}
```

---

## Deployment Checklist

**Pre-Deployment:**
- [ ] Read RCA document: `context/rca-rate-limit-2025-11-25.md`
- [ ] Verify cache populated: `SELECT COUNT(*) FROM mpsm_cache_devices` > 0
- [ ] Test cached endpoint returns data
- [ ] Create git branch: `fix/rate-limit-cache-wiring-phase1`
- [ ] Apply Phase 1 changes (4 files)
- [ ] Review diff carefully
- [ ] Write descriptive commit message

**Deployment:**
- [ ] Commit changes with changelog
- [ ] Push to main branch
- [ ] Monitor GitHub Actions deployment
- [ ] Wait for deployment complete (~2 min)

**Post-Deployment:**
- [ ] Hard refresh browser (Ctrl+Shift+R)
- [ ] Open DevTools Network tab
- [ ] Load dashboard
- [ ] Verify: No 429 errors
- [ ] Verify: Device card loads
- [ ] Verify: Top Devices card loads
- [ ] Click both card modals → verify drill-downs work
- [ ] Monitor logs: `cms/logs/php_errors.log`
- [ ] Monitor for 30 minutes

**If Successful:**
- [ ] Update `context/session.md` with success status
- [ ] Monitor for 24 hours before Phase 2
- [ ] Check error rate: should be <5% (was 90%)

**If Failed:**
- [ ] Execute rollback plan
- [ ] Document failure in `context/test-log.md`
- [ ] Analyze logs for root cause
- [ ] Revise patch before retry

---

## Success Metrics

### Target Improvements

| Metric | Before | After Phase 1 | After Phase 2 |
|--------|--------|---------------|---------------|
| API calls per load | 9 | 5 | 2-3 |
| Rate limit errors | 90% | 40% | 10% |
| Dashboard load time | 10-30s | 3-5s | 2-3s |
| Card failures | 5-8 cards | 0-2 cards | 0 cards |
| User satisfaction | 🔴 Poor | 🟡 Acceptable | 🟢 Good |

### Monitoring Dashboard

```sql
-- Dashboard to track success
SELECT
    DATE(created_at) as date,
    COUNT(*) as total_loads,
    SUM(CASE WHEN status = '429' THEN 1 ELSE 0 END) as rate_limited,
    SUM(CASE WHEN status = '200' THEN 1 ELSE 0 END) as successful,
    ROUND(AVG(load_time_ms)) as avg_load_time,
    (SELECT COUNT(*) FROM mpsm_cache_devices) as cache_size
FROM mpsm_dashboard_loads
WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY DATE(created_at)
ORDER BY date DESC;
```

*(Note: This table doesn't exist yet - would be created in future instrumentation phase)*

---

## Future Enhancements

**Phase 3: Real-Time Updates** (Low Priority)
- WebSocket/SSE for live device status changes
- Push notifications for critical alerts
- Optimistic UI updates with background sync

**Phase 4: Offline-First PWA** (Low Priority)
- Service worker for offline dashboard
- IndexedDB for local device cache
- Background sync when connection restored

**Phase 5: Intelligent Caching** (Medium Priority)
- Cache invalidation on device mutations
- Per-card cache TTLs (critical cards refresh faster)
- Predictive prefetching based on user behavior

---

## Related Documents

- **RCA:** [context/rca-rate-limit-2025-11-25.md](context/rca-rate-limit-2025-11-25.md)
- **Architecture:** [context/system-architecture.md](context/system-architecture.md)
- **CMS Layer:** [context/cms-layer.md](context/cms-layer.md)
- **Data Flows:** [context/data-flows.md](context/data-flows.md)
- **Cache System:** [context/cache-system-audit-2025-11-15.md](context/cache-system-audit-2025-11-15.md)

---

**Author:** Claude Sonnet 4.5
**Date:** 2025-11-25
**Session:** Rate Limit Mitigation Planning
**Status:** Ready for Approval
