# Refactor Plan: Achieve 5000+ Device Coverage

**Date**: 2025-11-08
**Current State**: 200 devices cached (should be 5000+)
**Goal**: Full coverage of all MPSM API devices with drill-down data

---

## Current State Analysis

### Issues Identified:

1. **Cache is Stale (50 hours old)**
   - Last refresh: Nov 6, 21:21:42
   - Current time: Nov 9, 23:54:21
   - **Root cause**: Cron job not running OR server timing out

2. **Only 200 Devices Cached**
   - API has 5000+ devices
   - Code is correct (fixed pagination)
   - **Root cause**: Cache refresh only ran once and stopped at page 2-3

3. **50% Drill-Down Coverage**
   - Only 100 of 200 devices have drill-down
   - **Root cause**: Drill-down fetch incomplete/interrupted

4. **439 Devices Have Panel Messages But No Cache**
   - Panel messages arriving for devices not in cache
   - **Root cause**: Those devices haven't been fetched yet

5. **0 Active Notifications**
   - 4 rules active, 1,112 callbacks received
   - **Root cause**: Rules don't match actual alert codes (808, 807, 1, 3, 801)

---

## Root Cause Deep Dive

### Why Only 200 Devices?

Looking at the current code (lines 258-307), the fix IS there:
- ✅ Changed to check `< 100` devices
- ✅ Increased to 500 pages max
- ✅ Direct array usage (no extraction)

**BUT** the cache shows only 200 devices from Nov 6. This means:
- The cache refresh RAN on Nov 6
- It stopped after page 2 (200 devices)
- It hasn't run since (cron not working)

**Possible causes:**
1. **API returned empty response on page 3**
2. **Rate limiting kicked in and wasn't handled**
3. **Server timeout (20 min limit)**
4. **Process was killed mid-execution**

---

## Refactor Strategy

### Phase 1: Immediate Fixes (Deploy & Test)

**Goal**: Get cache refresh working and populating all devices

#### Fix 1: Add Better Error Handling & Logging
```php
// Current issue: Silent failures
if (!$response) {
    logMessage("Device/List returned empty response on page {$pageNumber}; stopping pagination.");
    break;  // ← This might be triggering too early
}

// Better approach:
if (!$response) {
    logMessage("WARNING: Empty response on page {$pageNumber}, retrying...");
    sleep(2);
    $pageNumber--;  // Retry same page
    $retryCount++;
    if ($retryCount > 3) {
        logMessage("ERROR: Page {$pageNumber} failed after 3 retries, stopping.");
        break;
    }
    continue;
}
```

#### Fix 2: Handle Partial Responses
```php
// API might return partial pages before empty
if ($deviceCount > 0 && $deviceCount < 100) {
    logMessage("Partial page detected: {$deviceCount} devices. Continuing...");
    $allDevices = array_merge($allDevices, $pageDevices);
    continue;  // Don't break yet, check next page
}

if ($deviceCount === 0) {
    logMessage("Empty page at {$pageNumber}. Stopping pagination.");
    break;
}
```

#### Fix 3: Increase Timeout & Add Progress Tracking
```php
set_time_limit(3600); // 60 minutes (was 20 minutes)
ini_set('memory_limit', '1G'); // Increase memory for 5000+ devices

// Add progress reporting every 10 pages
if ($pageNumber % 10 === 0) {
    $totalSoFar = count($allDevices);
    logMessage("Progress: Page {$pageNumber}, Total devices: {$totalSoFar}");
}
```

### Phase 2: Drill-Down Optimization

**Goal**: Ensure all fetched devices get drill-down data

#### Fix 4: Batch Drill-Down Processing
```php
// Current: Processes all in one run (times out)
// Better: Process in batches, resume on next run

// Track which devices already have drill-down
$devicesNeedingDrilldown = [];
foreach ($devices as $device) {
    $serial = $device['SerialNumber'] ?? null;
    if (!$serial) continue;

    // Check if already cached
    $stmt = $pdo->prepare("SELECT 1 FROM {$prefix}cache_device_drilldown WHERE serial_number = ?");
    $stmt->execute([$serial]);
    if (!$stmt->fetchColumn()) {
        $devicesNeedingDrilldown[] = $device;
    }
}

// Process only what's needed
logMessage("Drill-down queue: " . count($devicesNeedingDrilldown) . " devices need processing");
```

#### Fix 5: Smart Prioritization
```php
// Prioritize devices with panel messages
$stmt = $pdo->query("
    SELECT DISTINCT device_serial
    FROM {$prefix}panel_messages
    WHERE device_serial NOT IN (
        SELECT serial_number FROM {$prefix}cache_device_drilldown
    )
    LIMIT 500
");
$priorityDevices = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Process priority devices first
foreach ($priorityDevices as $serial) {
    // Fetch device from cache
    // Run drill-down
    // Cache results
}
```

### Phase 3: Monitoring & Validation

**Goal**: Ensure system maintains 5000+ coverage

#### Fix 6: Health Checks
```php
// Add to refresh-cache-enhanced.php end
$expectedMinimum = 4000; // Expect at least 4000 devices
if ($stats['devices_cached'] < $expectedMinimum) {
    logMessage("WARNING: Only {$stats['devices_cached']} devices cached (expected {$expectedMinimum}+)");
    // Send alert email/notification
}
```

#### Fix 7: Incremental Updates
```php
// Don't re-fetch devices already cached
// Only update devices older than 24 hours
$stmt = $pdo->query("
    SELECT serial_number
    FROM {$prefix}cache_devices
    WHERE cached_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)
");
$staleDevices = $stmt->fetchAll(PDO::FETCH_COLUMN);
```

---

## Optimized Refactor Plan

### Step 1: Fix Pagination Robustness
**File**: `cms/api/refresh-cache-enhanced.php`
**Changes**:
- Add retry logic for empty responses
- Handle partial pages correctly
- Increase timeout to 60 minutes
- Add progress logging every 10 pages
- Don't break on first empty page, verify 3 consecutive empties

### Step 2: Separate Device Fetch from Drill-Down
**New Strategy**:
- Run 1: Fetch ALL device metadata (fast, 5-10 min)
- Run 2: Populate drill-down for priority devices (panel messages)
- Run 3: Background drill-down for remaining devices

### Step 3: Create Incremental Refresh Mode
**Parameters**:
- `?full=1` - Full refresh (all devices, all drill-down)
- `?quick=1` - Device list only (skip drill-down)
- `?priority=1` - Only devices with panel messages
- Default: Incremental (update stale + new devices)

### Step 4: Add Circuit Breakers
**Prevent Infinite Loops**:
```php
$maxEmptyPages = 3;
$consecutiveEmpty = 0;

if ($deviceCount === 0) {
    $consecutiveEmpty++;
    if ($consecutiveEmpty >= $maxEmptyPages) {
        break;
    }
} else {
    $consecutiveEmpty = 0; // Reset on successful page
}
```

### Step 5: Monitoring Dashboard
**New file**: `cms/api/cache-monitoring.php`
- Real-time refresh progress
- Device count tracking over time
- Drill-down coverage percentage
- Panel message integration status

---

## Regression Prevention

### 1. Automated Tests
```php
// tests/CacheRefreshTest.php
function testPaginationFetchesAllDevices() {
    // Mock API with 500 devices across 5 pages
    $result = fetchAllDevices();
    assert(count($result) === 500, "Should fetch all 500 devices");
}

function testEmptyPageHandling() {
    // Mock API that returns empty on page 3
    $result = fetchAllDevices();
    // Should fetch pages 1-2, skip 3, continue to 4-5
}
```

### 2. Health Monitoring
```php
// cms/api/health-check.php
$checks = [
    'device_count' => ['min' => 4000, 'current' => $deviceCount],
    'cache_age' => ['max_hours' => 6, 'current_hours' => $cacheAgeHours],
    'drilldown_coverage' => ['min_percent' => 80, 'current_percent' => $coverage],
];

foreach ($checks as $check => $thresholds) {
    if (fails($check, $thresholds)) {
        alertAdmin($check);
    }
}
```

### 3. Rollback Strategy
```bash
# Before deploy
git tag cache-refresh-v1.0

# After deploy, if issues
git revert HEAD
git push origin main
```

### 4. Gradual Rollout
- Test on staging first
- Deploy to production with monitoring
- Run manual refresh, verify counts
- Enable cron only after verification

---

## Execution Steps

### Patch 1: Pagination Robustness
1. Modify `fetchAllDevices()` - add retry logic
2. Add circuit breaker (3 consecutive empty pages)
3. Increase timeout to 60 minutes
4. Add progress logging
5. Deploy
6. Run manual refresh with `?force=1`
7. Verify > 4000 devices cached

### Patch 2: Drill-Down Optimization
1. Create `refreshDrilldownIncremental()` function
2. Prioritize devices with panel messages
3. Process in batches of 100
4. Track progress in database
5. Deploy
6. Run drill-down refresh
7. Verify > 80% coverage

### Patch 3: Monitoring
1. Create cache-monitoring.php dashboard
2. Add health-check.php endpoint
3. Setup alerts for failures
4. Deploy
5. Monitor for 24 hours
6. Verify stability

---

## Success Criteria

### Phase 1 Complete:
- ✅ Device cache: 4000+ devices
- ✅ Cache age: < 6 hours
- ✅ No pagination errors in logs

### Phase 2 Complete:
- ✅ Drill-down coverage: 80%+ of cached devices
- ✅ Priority devices (with panel messages): 100% coverage
- ✅ Drill-down age: < 24 hours

### Phase 3 Complete:
- ✅ Total devices: 5000+ maintained
- ✅ Auto-refresh working (cron running)
- ✅ Health checks passing
- ✅ No regression for 48 hours

---

## Timeline

**Patch 1 (Pagination)**: 30 minutes code + 20 minute refresh = 50 min
**Patch 2 (Drill-Down)**: 45 minutes code + 60 minute refresh = 105 min
**Patch 3 (Monitoring)**: 30 minutes code + 10 minute deploy = 40 min

**Total**: ~3 hours to full 5000+ coverage

---

## Risk Mitigation

### Risk 1: API Rate Limiting
**Mitigation**: Exponential backoff already implemented, increase delays if needed

### Risk 2: Server Timeout
**Mitigation**: Increased timeout to 60 min, batch processing for drill-down

### Risk 3: Memory Exhaustion
**Mitigation**: Increased to 1GB, streaming pagination (merge incrementally)

### Risk 4: Data Loss
**Mitigation**: Don't TRUNCATE, use REPLACE INTO for upserts

---

**Ready to Execute**: YES
**Estimated Completion**: 3 hours
**Expected Final State**: 5000+ devices with 80%+ drill-down coverage
