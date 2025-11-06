# 🧪 Live Site Testing Checklist

**URL:** https://mpsm.resolutionsbydesign.us/cms/
**Status:** Ready for testing
**Time Required:** 15-30 minutes

---

## 🚀 Pre-Test: Deployment Verification

Before testing features, verify deployment completed:

### ✅ Deployment Checklist
- [ ] Database indexes applied (`database_optimizations.sql`)
- [ ] `cms/functions.php` uploaded
- [ ] `cms/api/get-cached-devices.php` uploaded
- [ ] Cache populated (`refresh-cache-enhanced.php` ran successfully)
- [ ] Cron job scheduled (every 5 minutes)

**If any item not checked, complete deployment first using `DEPLOY_NOW.md`**

---

## 🧪 Quick Smoke Test (5 Minutes)

**CRITICAL: Test these immediately after deployment**

### Test 1: Login
- [ ] Navigate to: https://mpsm.resolutionsbydesign.us/cms/
- [ ] Redirects to login.html
- [ ] Enter credentials: admin / admin
- [ ] Click "Login"
- [ ] ✅ **PASS:** Redirects to dashboard
- [ ] ❌ **FAIL:** Shows error or stays on login

### Test 2: Dashboard Load Performance
- [ ] Start timer when dashboard appears
- [ ] Wait for all cards to show data (not spinners)
- [ ] Stop timer
- [ ] ⏱️ **Time:** _____ seconds
- [ ] ✅ **PASS:** < 3 seconds (FAST!)
- [ ] ⚠️ **WARN:** 3-10 seconds (slower than expected)
- [ ] ❌ **FAIL:** > 10 seconds (no improvement)

### Test 3: Cards Display Data
- [ ] Customer Snapshot card shows device count
- [ ] Connectors card shows status
- [ ] Devices card shows total devices
- [ ] Supply Alerts card shows alert count
- [ ] ✅ **PASS:** All cards show data
- [ ] ❌ **FAIL:** Cards stuck on loading spinner

### Test 4: Device Search
- [ ] Click search box at top
- [ ] Type any device serial number or model
- [ ] Wait for dropdown to appear
- [ ] ⏱️ **Time:** _____ seconds
- [ ] ✅ **PASS:** < 1 second (instant results)
- [ ] ⚠️ **WARN:** 1-3 seconds (slower than expected)
- [ ] ❌ **FAIL:** > 3 seconds or no results

### Test 5: Device Modal Performance
- [ ] Click on a search result
- [ ] Start timer
- [ ] Wait for modal to fully load with all data
- [ ] Stop timer
- [ ] ⏱️ **Time:** _____ milliseconds
- [ ] ✅ **PASS:** < 500ms (instant!)
- [ ] ⚠️ **WARN:** 500ms-2s (slower than expected)
- [ ] ❌ **FAIL:** > 2s (no improvement)

### Test 6: Console Errors
- [ ] Press F12 (open DevTools)
- [ ] Go to "Console" tab
- [ ] Look for red errors
- [ ] ✅ **PASS:** No red errors
- [ ] ⚠️ **WARN:** Yellow warnings only
- [ ] ❌ **FAIL:** Red JavaScript errors

### Test 7: Panel Message Monitor
- [ ] Navigate to: https://mpsm.resolutionsbydesign.us/cms/panel-message-monitor.php
- [ ] Wait for page to load
- [ ] Check if messages appear
- [ ] ⏱️ **Time:** _____ seconds
- [ ] ✅ **PASS:** < 2 seconds and shows messages
- [ ] ❌ **FAIL:** Slow or errors

### Test 8: System Health
- [ ] Click "Admin" tab
- [ ] Find "System Health" card
- [ ] Click card or wait for data
- [ ] Check database status
- [ ] Check cache status
- [ ] ✅ **PASS:** Database = Connected, Cache = Enabled
- [ ] ❌ **FAIL:** Any status shows error

**📊 Smoke Test Results:**
- **Tests Passed:** ___/8
- **Tests Failed:** ___/8
- **Overall:** ✅ PASS (7-8 pass) | ⚠️ WARN (5-6 pass) | ❌ FAIL (<5 pass)

**If smoke test PASSES:** Proceed to comprehensive testing
**If smoke test FAILS:** Check troubleshooting section in DEPLOY_NOW.md

---

## 📊 Performance Benchmark Test

**Measure actual performance improvements:**

### Before/After Comparison

Open browser DevTools (F12) → Network tab → Hard refresh (Ctrl+Shift+R)

| Endpoint | Expected Time | Actual Time | Status |
|----------|---------------|-------------|--------|
| **get-cached-devices.php** | <100ms | _____ms | ✅/❌ |
| **get-device-deep-dive.php** | <200ms | _____ms | ✅/❌ |
| **get-panel-messages.php** | <100ms | _____ms | ✅/❌ |
| **Dashboard Total Load** | <3s | _____s | ✅/❌ |

**How to measure:**
1. Open DevTools (F12)
2. Go to Network tab
3. Hard refresh (Ctrl+Shift+R)
4. Find endpoint in network list
5. Look at "Time" column
6. Record time in table above

---

## 🧪 Comprehensive Feature Test

**Use FEATURE_INVENTORY.md for complete testing (200+ test cases)**

### Quick Comprehensive Test (15 minutes)

#### Authentication & Session
- [ ] Login works
- [ ] Session persists on refresh
- [ ] Logout works
- [ ] Session timeout works (after 60 min)

#### Dashboard UI
- [ ] Dashboard tab active by default
- [ ] All cards visible in grid
- [ ] Cards load with data
- [ ] No cards stuck loading
- [ ] Card error handling works (test by disabling network)

#### Device Search
- [ ] Search by serial number works
- [ ] Search by asset number works
- [ ] Search by IP address works
- [ ] Search by model name works
- [ ] Search dropdown shows top 10 results
- [ ] Clicking result opens modal

#### Device Deep-Dive Modal
- [ ] Modal opens fast (<500ms)
- [ ] Shows device information (serial, model, location)
- [ ] Shows meter readings (B&W, Color counts)
- [ ] Shows supply levels (toner percentages with colors)
- [ ] Shows supply alerts (if any)
- [ ] Shows device health status
- [ ] Shows panel message history
- [ ] Modal close button works
- [ ] Click outside modal closes it
- [ ] ESC key closes modal

#### Admin Tab
- [ ] Admin tab navigation works
- [ ] System Health card displays
- [ ] Database Monitor card works
- [ ] Visitor Analytics card works
- [ ] Error Logs card accessible
- [ ] Endpoint Catalog card works

#### Panel Message Monitoring
- [ ] Panel message monitor page loads
- [ ] Messages display in table
- [ ] Time window filter works (1h, 24h, etc.)
- [ ] Display limit filter works (10, 50, 100)
- [ ] Device serial search works
- [ ] Payload viewer modal opens
- [ ] Auto-refresh works (30 seconds)

#### Payload Debugger
- [ ] Payload debugger page loads
- [ ] Debug log displays
- [ ] Status filter works (SUCCESS/ERROR)
- [ ] Auto-refresh works (5 seconds)
- [ ] Debug entry details modal opens

#### Background Cache System
- [ ] Cache refresh endpoint accessible
- [ ] Cache refresh returns JSON with stats
- [ ] Cache log file exists and updates
- [ ] Cron job running (check logs every 5 min)

---

## 🌐 Browser Compatibility Test

Test on multiple browsers:

### Chrome/Edge (Chromium)
- [ ] Dashboard loads ✅
- [ ] All features work ✅
- [ ] No console errors ✅

### Firefox
- [ ] Dashboard loads ✅
- [ ] All features work ✅
- [ ] No console errors ✅

### Safari (if available)
- [ ] Dashboard loads ✅
- [ ] All features work ✅
- [ ] No console errors ✅

### Mobile Browser
- [ ] Responsive layout adapts ✅
- [ ] Touch interactions work ✅
- [ ] Modal scrolls properly ✅

---

## 🔍 Data Integrity Test

### Device Count Verification
1. Note dashboard device count: _____
2. Check database:
   ```sql
   SELECT COUNT(*) FROM mpsm_cache_devices;
   ```
3. Count: _____
4. ✅ **PASS:** Counts match (±5 tolerance)
5. ❌ **FAIL:** Significant difference

### Cache Staleness Check
1. Check system health for cache age
2. Cache age: _____ minutes
3. ✅ **PASS:** < 10 minutes (cron is working)
4. ⚠️ **WARN:** 10-60 minutes (cron may be delayed)
5. ❌ **FAIL:** > 60 minutes (cron not running)

### Panel Message Storage
1. Send test webhook (if able)
2. Check `mpsm_panel_messages` table
3. ✅ **PASS:** Message stored correctly
4. ❌ **FAIL:** Message not stored

---

## 🐛 Error Detection Test

### Check Error Logs
- [ ] Navigate to: `/public_html/cms/logs/php_errors.log`
- [ ] Recent errors: _____ (count)
- [ ] ✅ **PASS:** 0 errors in last hour
- [ ] ⚠️ **WARN:** <5 minor errors
- [ ] ❌ **FAIL:** Many errors or critical errors

### Check Cache Refresh Logs
- [ ] Navigate to: `/public_html/cms/logs/cache-refresh-YYYY-MM-DD.log`
- [ ] Last refresh time: _____
- [ ] Devices cached: _____
- [ ] Errors: _____
- [ ] ✅ **PASS:** Recent refresh, 0 errors
- [ ] ❌ **FAIL:** No recent refresh or many errors

### Check Browser Console
- [ ] Open DevTools (F12) → Console
- [ ] Errors: _____ (count)
- [ ] ✅ **PASS:** 0 red errors
- [ ] ⚠️ **WARN:** Only yellow warnings
- [ ] ❌ **FAIL:** Red JavaScript errors

---

## 📈 Performance Validation

### Dashboard Load Time
- **Target:** < 3 seconds
- **Actual:** _____ seconds
- **Improvement:** _____ seconds faster than before
- **Status:** ✅ Target met | ❌ Target not met

### Device Modal Open Time
- **Target:** < 500ms
- **Actual:** _____ ms
- **Improvement:** _____ ms faster than before
- **Status:** ✅ Target met | ❌ Target not met

### Search Response Time
- **Target:** < 1 second
- **Actual:** _____ seconds
- **Improvement:** _____ seconds faster than before
- **Status:** ✅ Target met | ❌ Target not met

### Database Query Time
- **Target:** < 100ms (panel messages)
- **Actual:** _____ ms
- **Improvement:** _____ ms faster than before
- **Status:** ✅ Target met | ❌ Target not met

---

## ✅ Test Summary

**Date:** __________
**Tested By:** __________
**Browser:** __________

### Results

**Quick Smoke Test:**
- Passed: ___/8
- Failed: ___/8
- Status: ✅ PASS | ⚠️ WARN | ❌ FAIL

**Performance Benchmarks:**
- Dashboard: _____ seconds (target: <3s)
- Device Modal: _____ ms (target: <500ms)
- Search: _____ seconds (target: <1s)
- Status: ✅ All targets met | ⚠️ Some targets met | ❌ No targets met

**Comprehensive Features:**
- Tested: _____ features
- Passed: _____ features
- Failed: _____ features
- Status: ✅ PASS | ⚠️ WARN | ❌ FAIL

**Overall Assessment:**
- [ ] ✅ **PASS** - Ready for production (all critical tests pass)
- [ ] ⚠️ **PASS WITH ISSUES** - Production ready with minor issues
- [ ] ❌ **FAIL** - Critical issues found, rollback recommended

### Issues Found
1. _________________________________
2. _________________________________
3. _________________________________

### Recommendations
1. _________________________________
2. _________________________________
3. _________________________________

---

## 🎯 Success Criteria

**Deployment is successful if:**
- [x] All smoke tests pass (7-8/8)
- [x] Performance targets met (dashboard <3s, modal <500ms)
- [x] No critical errors in logs
- [x] No JavaScript errors in console
- [x] Cache system working (refreshing every 5 min)
- [x] All core features functional

**If all criteria met:** ✅ Deployment SUCCESSFUL! 🎉

**If criteria not met:** Review troubleshooting in DEPLOY_NOW.md

---

## 📝 Next Steps After Testing

### If Testing Passes (24-48 hours later):

1. **Finalize Dead Code Removal:**
   ```powershell
   .\finalize-dead-code-removal.ps1
   ```

2. **Update Documentation:**
   - Note performance improvements
   - Document any issues encountered
   - Update team on changes

3. **Monitor Long-Term:**
   - Check logs daily for first week
   - Watch cache hit rates
   - Monitor user feedback

### If Testing Fails:

1. **Review Error Logs:**
   - Check PHP error log
   - Check cache refresh log
   - Check browser console

2. **Run Rollback:**
   ```powershell
   .\rollback-database-optimizations.ps1
   .\restore-dead-code.ps1
   ```

3. **Report Issues:**
   - Document error messages
   - Capture screenshots
   - Review deployment log

---

## 🚀 Happy Testing!

**Remember:**
- Take your time with each test
- Document any unexpected behavior
- Performance improvements should be obvious
- System should feel significantly faster

**Good luck! The refactor is designed to make everything 7-10x faster. You should notice the difference immediately! 🎉**
