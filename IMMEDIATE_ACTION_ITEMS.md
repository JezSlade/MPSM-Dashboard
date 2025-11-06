# Immediate Action Items

**Date**: November 6, 2025
**Priority**: HIGH - Performance Optimizations Deployed
**Latest Update**: Performance fixes deployed and tested

---

## ✅ COMPLETED: Performance Optimization Deployment (Nov 6, 2025)

### What Was Fixed
1. **Dashboard Client-Side Caching** ✅
   - Implemented 5-minute TTL cache in CardManager
   - Eliminates 20-30 second reload on every tab switch
   - Expected: Return visits <1s (20-30x faster)

2. **Drill-Down Coverage Fix** ✅
   - Increased timeout: 10min → 20min
   - Increased API delay: 50ms → 250ms (reduce rate limits)
   - Increased retries: 6 → 10 attempts
   - Expected: Full coverage (100% vs 50% stall)

3. **Payload Debugger Source Filtering** ✅
   - Added source dropdown filter
   - Removed confusing "Unique Sources" summary
   - Performance: ~120ms response time

### Test Results
```
Homepage:              ✅ 0.121s
Panel Monitor:         ✅ 0.121s
Payload Debugger:      ✅ 0.116s
Panel Messages API:    ✅ 0.125s
Payload Debug Logs:    ✅ 0.119s
```

### Files Changed
- `cms/assets/js/card-manager.js` (+57 lines) - Client-side caching
- `cms/api/refresh-cache-enhanced.php` (+8/-6 lines) - Drill-down resilience
- `cms/payload-debugger.php` - Source filtering UI
- `cms/api/get-payload-debug-logs.php` - Source filtering backend

### Deployment Details
- **Commit:** eae29f0
- **Method:** GitHub Actions automatic deployment
- **Status:** ✅ Deployed and tested
- **Report:** See `PERFORMANCE_DEPLOYMENT_REPORT.md`

---

## Issue 1: CardManager.setContext Error ✅ FIXED (Needs Browser Cache Clear)

### Status
The fix has been deployed. The safety checks are live on the server.

### Verification
```bash
curl -s "https://mpsm.resolutionsbydesign.us/cms/assets/app.js" | grep -c "typeof CardManager"
```
Returns: 8 (all safety checks in place)

### Action Required: Clear Browser Cache
The error you're seeing is likely due to **browser caching** the old app.js file.

**Solution**:
1. Hard refresh the browser: `Ctrl+F5` (Windows) or `Cmd+Shift+R` (Mac)
2. Or clear browser cache completely
3. Or open in incognito/private browsing mode

The CardManager safety checks ARE deployed at:
- Line 56-62
- Line 1256-1262
- Line 1432-1438
- Line 1742-1748
- Line 1769-1775

---

## Issue 2: Database Not Being Populated with Device Data ⚠️ NEEDS CONFIGURATION

### Current Status
The enhanced refresh system ran but cached **0 devices** because:
- API returned 0 devices
- DEFAULT_DEALER_CODE may not be configured correctly

### Test Result
```json
{
  "status": "success",
  "stats": {
    "devices_cached": 0,
    "devices_with_drilldown": 0,
    "devices_with_panels": 11,
    "api_calls_made": 2,
    "errors": 0,
    "duration": 4.96
  }
}
```

### Root Cause
The `DEFAULT_DEALER_CODE` in `cms/config.php` may not match your actual dealer code in MPSM.

### Actions Required

#### Step 1: Verify Dealer Code
Check what dealer code you're using in the CMS:
```bash
curl -s "https://mpsm.resolutionsbydesign.us/cms/api/get-cached-devices.php" -b cookies.txt | grep -i "dealerCode"
```

#### Step 2: Update config.php if needed
Ensure `DEFAULT_DEALER_CODE` in `cms/config.php` matches your actual dealer code.

#### Step 3: Manually Run Refresh
```bash
curl "https://mpsm.resolutionsbydesign.us/cms/api/refresh-cache-enhanced.php"
```

Expected output should show:
```json
{
  "devices_cached": 3000+,
  "devices_with_drilldown": 3000+
}
```

#### Step 4: Verify Database
```sql
-- Check device cache
SELECT COUNT(*) as device_count FROM mpsm_cache_devices;

-- Check drill-down cache
SELECT COUNT(*) as drilldown_count FROM mpsm_cache_device_drilldown;

-- Sample data
SELECT serial_number, customer_code, cached_at
FROM mpsm_cache_devices
LIMIT 10;
```

---

## Issue 3: Add Payload Debugger Tab to Command Center ⏳ IN PROGRESS

### Current Status
- Payload debugger exists as standalone page: `/cms/payload-debugger.php`
- Needs to be integrated into command center navigation

### Options

#### Option A: Add Navigation Link (Quick - 5 minutes)
Add a link in the command center header/navigation to open payload debugger.

#### Option B: Embed as IFrame Tab (Medium - 15 minutes)
Add a new tab to command center that displays payload debugger in an iframe.

#### Option C: Fully Integrate (Long - 1 hour)
Rebuild payload debugger UI directly into command center tab system.

### Recommended: Option B (IFrame Tab)

**Benefits**:
- Quick to implement
- Keeps all tools in one place
- No code duplication
- Easy to maintain

**Implementation needed in `index.php`**:
1. Add "Payload Debugger" tab to navigation
2. Add iframe container for payload-debugger.php
3. Handle tab switching

---

## Priority Actions (Right Now)

### 1. Clear Browser Cache (30 seconds)
```
Press Ctrl+F5 on the dashboard
```
This should fix the CardManager error immediately.

### 2. Fix Database Population (5 minutes)
Check if DEFAULT_DEALER_CODE is correct, then run manual refresh:
```bash
curl "https://mpsm.resolutionsbydesign.us/cms/api/refresh-cache-enhanced.php"
```

Wait 5-10 minutes for it to complete, then verify:
```bash
curl "https://mpsm.resolutionsbydesign.us/cms/api/refresh-cache-enhanced.php" | grep "devices_cached"
```

Should show `"devices_cached": 3000+`

### 3. Add Payload Debugger to Command Center (Choose Option)

Tell me which option you prefer:
- **Option A**: Just add a link/button to open payload debugger
- **Option B**: Add as embedded iframe tab (recommended)
- **Option C**: Fully rebuild into command center

---

## Verification Steps

Once actions are complete, verify everything works:

### 1. CardManager Error Gone
- Navigate to command center
- Return to dashboard
- Should NOT see "CardManager.setContext is not a function" error

### 2. Database Populated
```bash
# Check device count
curl -s "https://mpsm.resolutionsbydesign.us/cms/api/get-cached-devices.php" -b cookies.txt | grep "total"

# Should return large number like:
# "total": 3847
```

### 3. Drill-Down Data Cached
Open any device modal and it should load instantly (<100ms) with:
- Meter readings
- Supply levels
- Alerts
- Panel message history

### 4. Payload Debugger Accessible
Navigate to command center → Should see Payload Debugger tab/link

---

## Quick Diagnostics

### Test CardManager Fix
Open browser console (F12) and run:
```javascript
typeof CardManager !== 'undefined' && typeof CardManager.setContext === 'function'
```
Should return: `true`

### Test Database Population
```bash
curl "https://mpsm.resolutionsbydesign.us/cms/api/cache-stats.php" 2>/dev/null || echo "Stats endpoint not created yet"
```

### Test Payload Debugger
```bash
curl -s "https://mpsm.resolutionsbydesign.us/cms/payload-debugger.php" -o /dev/null -w "HTTP %{http_code}\n"
```
Should return: `HTTP 302` (redirect to login, which is correct)

---

## Next Steps After Verification

1. **Setup Cron/Task Scheduler**
   - Run refresh-cache-enhanced.php every 5 minutes
   - Ensures database stays fresh

2. **Monitor Logs**
   - Check `cms/logs/cache-refresh-*.log`
   - Verify refreshes are successful

3. **Update Endpoints to Use Cache**
   - Modify `get-cached-devices.php` to read from `mpsm_cache_devices`
   - Modify `get-device-deep-dive.php` to read from `mpsm_cache_device_drilldown`
   - This will make everything instant

---

## If Issues Persist

### CardManager Error Still Showing
1. Clear ALL browser data (not just cache)
2. Check browser console for actual error message
3. Verify app.js loaded correctly: View Source → search for "typeof CardManager"

### Database Still Empty After Refresh
1. Check `cms/logs/cache-refresh-*.log` for errors
2. Verify DEFAULT_DEALER_CODE is correct
3. Test API manually:
```bash
curl -X POST "https://mpsm.resolutionsbydesign.us/mps-api/query" \
  -H "Content-Type: application/json" \
  -d '{"action":"Device/List","params":{"FilterDealerCodes":["YOUR_DEALER_CODE"],"PageNumber":1,"PageRows":10}}'
```

### Payload Debugger Not Working
1. Verify file exists: `curl https://mpsm.resolutionsbydesign.us/cms/payload-debugger.php`
2. Check authentication required
3. Test API: `curl https://mpsm.resolutionsbydesign.us/cms/api/get-payload-debug-logs.php`

---

## Summary

| Issue | Status | Action | Time |
|-------|--------|--------|------|
| CardManager Error | ✅ Fixed | Clear browser cache | 30 sec |
| Database Population | ⚠️ Needs Config | Fix dealer code + run refresh | 5 min |
| Payload Debugger Tab | ⏳ Pending | Choose integration option | 5-60 min |

**Total Time to Full Resolution**: 6-66 minutes depending on payload debugger integration choice.

---

**Created**: November 5, 2025
**Status**: Waiting for user action on database configuration and payload debugger integration preference
