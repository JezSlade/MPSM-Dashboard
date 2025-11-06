# Critical Performance Issues Analysis

**Date:** 2025-11-06
**Status:** IN PROGRESS

---

## Issues Identified

### 1. Dashboard Loading Performance (20-30 seconds every navigation)
**Severity:** CRITICAL
**Impact:** Poor user experience, unusable dashboard

**Symptoms:**
- Dashboard takes 20-30 seconds to load
- Cards reload every time user navigates back to dashboard
- No client-side caching implemented
- Users must wait for API calls to complete each time

**Root Cause Analysis:**
- CardManager calls APIs fresh on every tab switch
- No in-memory caching of card data
- No "warm" state preservation
- Each card makes individual API calls sequentially or in parallel without coordination

**Solution Plan:**
1. Implement client-side cache layer in MPSM.state
2. Cache card data with timestamps
3. Only refresh if data is >5 minutes old
4. Keep cards "warm" in memory when switching tabs
5. Add loading indicators instead of blank cards

**Implementation:**
- Add `cardDataCache` to MPSM.state with TTL
- Modify CardManager to check cache before API calls
- Implement cache invalidation strategy
- Add manual refresh button for users

---

### 2. Panel Message Monitor Slow Loading (minutes to load)
**Severity:** CRITICAL
**Impact:** Monitor unusable for real-time operations

**Symptoms:**
- Takes several minutes to load panel-message-monitor.php
- Page appears stuck or frozen
- Eventually loads but very slowly

**Root Cause (Likely):**
- Large table query without pagination
- Missing database indexes on panel messages
- No LIMIT clause or it's too high
- Inefficient JOIN queries
- Loading all messages instead of recent ones

**Solution Plan:**
1. Add LIMIT 100 by default to initial load
2. Implement virtual scrolling or pagination
3. Ensure indexes exist on:
   - `received_at` (for time-based queries)
   - `device_serial` (for device filtering)
   - `customer_code` (for customer filtering)
4. Use prepared statements with proper WHERE clauses
5. Add loading indicator

**Investigation Needed:**
- Check get-panel-messages.php query
- Verify database indexes applied (from database_optimizations.sql)
- Check row count in mpsm_panel_messages table
- Profile query execution time

---

### 3. Payload Debugger Slow Loading (2-3 minutes)
**Severity:** HIGH
**Impact:** Debugging difficult, slows development

**Symptoms:**
- Payload debugger takes 2-3 minutes to populate
- Similar issue to panel monitor

**Root Cause (Likely):**
- Same as panel monitor - large table without optimization
- mpsm_panel_callback_debug table growing large
- No pagination or LIMIT enforcement
- Missing indexes

**Solution Plan:**
1. Enforce LIMIT in get-payload-debug-logs.php (already has limit param)
2. Add database indexes:
   - `timestamp` DESC (for recent-first queries)
   - `status` (for filtering)
   - `unique_source` (for new source filter)
3. Implement cleanup job to remove old entries (>30 days)
4. Add loading state to UI

**Status:** Source filtering added, performance fix pending

---

### 4. Drill-Down Coverage Stalled at 50% (100/200 devices)
**Severity:** HIGH
**Impact:** Device data incomplete, modals slow

**Symptoms:**
- Drill-down coverage stuck at 50% (100/200)
- Should be thousands of devices
- Stalls for 20+ minutes
- Database monitor shows no progress

**Root Cause Analysis:**
Investigating `refresh-cache-enhanced.php`:
- Rate limiting hitting too hard
- Queue processing stalling
- Retry logic giving up after 6 attempts
- Server timeout killing long-running processes
- 10-minute PHP timeout may be insufficient

**Specific Issues Found:**
```php
// Line 15: Only 10 minutes allowed
set_time_limit(600); // May be too short for thousands of devices

// Line 132-144: Rate limit retry logic
if ($attempts > 6) {
    // Giving up after 6 attempts
    // May need higher threshold or different strategy
}

// Rate limiting exponential backoff
$drilldownDelayMicroseconds = 100000; // 100ms between calls
// May be too aggressive for API rate limits
```

**Solution Plan:**
1. Increase base delay between drill-down calls (250ms instead of 100ms)
2. Increase max attempts from 6 to 10
3. Implement better rate limit detection and handling
4. Split drill-down into batches with checkpointing
5. Log more detailed progress information
6. Consider running drill-down separately from main refresh

**Alternative:** Run drill-down caching as separate overnight job

---

### 5. Missing Device CRUD
**Severity:** MEDIUM
**Impact:** Feature missing from UI

**Issue:**
- Device CRUD was "newly conceived" but not visible in UI
- May not have been implemented
- May have been implemented but not integrated into tabs

**Investigation Needed:**
- Search codebase for device CRUD implementations
- Check if there's a devices management tab
- Verify API endpoints exist

**Locations to Check:**
- cms/index.php (tab structure)
- cms/assets/app.js (tab switching)
- cms/api/device-*.php endpoints
- Admin tab implementation

---

## Deployment Plan

### Phase 1: Quick Wins (30 minutes)
1. **Payload Debugger Source Filter** ✅ DEPLOYED
   - Already pushed to production
   - Test after 2-minute deployment window

2. **Database Index Verification**
   - Check if database_optimizations.sql was applied
   - If not, apply via phpMyAdmin
   - Verify with: `SHOW INDEX FROM mpsm_panel_messages;`

3. **Add Missing Indexes for Debug Table**
   ```sql
   ALTER TABLE mpsm_panel_callback_debug 
   ADD INDEX idx_timestamp_desc (timestamp DESC),
   ADD INDEX idx_status (status),
   ADD INDEX idx_unique_source (unique_source);
   ```

### Phase 2: Performance Fixes (2 hours)
1. **Fix Panel Monitor Query**
   - Add default LIMIT 100
   - Add proper ORDER BY with index
   - Implement pagination

2. **Fix Dashboard Caching**
   - Add client-side cache layer
   - Implement TTL-based refresh
   - Keep cards warm

3. **Fix Drill-Down Stalling**
   - Increase delays and retry limits
   - Add better logging
   - Implement batching

### Phase 3: Missing Features (1 hour)
1. **Find/Implement Device CRUD**
   - Search for existing implementation
   - If missing, create basic CRUD
   - Integrate into UI

### Phase 4: Testing & Documentation (1 hour)
1. Test all fixes on live site
2. Verify performance improvements
3. Update context documentation
4. Create performance baseline report

---

## Success Criteria

### Dashboard Performance
- ✅ First load: < 3 seconds
- ✅ Return visits: < 1 second (cached)
- ✅ No blank cards during load
- ✅ Smooth tab switching

### Panel Monitor
- ✅ Initial load: < 2 seconds
- ✅ Shows last 100 messages
- ✅ Pagination works smoothly
- ✅ Real-time updates functional

### Payload Debugger
- ✅ Load time: < 2 seconds
- ✅ Source filtering works
- ✅ Shows last 100 entries
- ✅ Can filter and sort quickly

### Drill-Down Coverage
- ✅ Processes all devices (not just 100)
- ✅ Completes within 30 minutes
- ✅ Provides progress feedback
- ✅ Handles rate limits gracefully

---

## Next Actions

**Immediate (Now):**
1. Wait 2 minutes for payload debugger deployment
2. Test source filtering on live site
3. Check database indexes status

**Next Session:**
1. Debug panel monitor query performance
2. Implement dashboard caching
3. Fix drill-down stalling issue
4. Find device CRUD implementation

**Priority Order:**
1. Panel monitor (blocks daily operations)
2. Dashboard caching (user experience)
3. Drill-down coverage (data completeness)
4. Device CRUD (feature completeness)

---

**Created:** 2025-11-06
**Status:** Payload debugger fix deployed, remaining issues pending
