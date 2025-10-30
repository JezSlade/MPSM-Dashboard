# Bug Fix Testing Plan
**Date**: 2025-10-30
**Deployment**: Production (https://mpsm.resolutionsbydesign.us/cms/)
**Bugs Fixed**: 8 total

---

## Test Environment Setup

1. **Open Site**: https://mpsm.resolutionsbydesign.us/cms/
2. **Login**: username: `admin`, password: `admin`
3. **Open Browser Console**: Press F12, go to Console tab
4. **Clear Console**: To see fresh logs

---

## Test Cases

### ✅ Test #1: Login and Initial Load (Bug #3)
**What to Test**: Alert summary null handling
**Steps**:
1. Login with admin/admin
2. Watch the dashboard load
3. **Expected**: No "undefined" or null errors in console
4. **Expected**: Dashboard metrics show numbers (not N/A)

**Pass Criteria**:
- [ ] No JavaScript errors about "Cannot read property of null"
- [ ] Alert totals display correctly

---

### ✅ Test #2: Device Loading (Bug #1, #2)
**What to Test**: Concurrent request prevention and device lookup
**Steps**:
1. Go to Dashboard tab (if not already there)
2. Look for "Fleet Devices" card
3. Click refresh button multiple times rapidly (5+ times quickly)
4. Check console for "Device loading already in progress" message
5. Wait for devices to load
6. Click on any device row

**Pass Criteria**:
- [ ] Console shows "Device loading already in progress" for duplicate requests
- [ ] Only one API request completes (check Network tab)
- [ ] Clicking device row opens device modal correctly

---

### ✅ Test #3: API Error Messages (Bug #5)
**What to Test**: Improved error handling and timeout
**Steps**:
1. Open browser DevTools Network tab
2. Find any request to `get-devices.php`
3. Look at response time
4. **Expected**: Response completes within 15 seconds
5. If there's an error, check the error message is specific (not generic)

**Pass Criteria**:
- [ ] API timeout is max 15 seconds (not 30)
- [ ] Error messages include HTTP status codes and details
- [ ] No "Failed to contact mps-api backend" without details

---

### ✅ Test #4: Card Layout Loading (Bug #4)
**What to Test**: Card registry race condition
**Steps**:
1. Refresh the page (F5)
2. Watch console logs during page load
3. Look for "Card registry not loaded yet" message
4. **Expected**: Cards load without errors

**Pass Criteria**:
- [ ] No errors about invalid card IDs
- [ ] Cards display correctly
- [ ] Console may show "Card registry not loaded yet, deferring sanitization" (this is normal)

---

### ✅ Test #5: Pagination Stress Test (Bug #1)
**What to Test**: Prevent concurrent pagination requests
**Steps**:
1. Scroll to Fleet Devices table
2. If pagination buttons exist, click Next/Previous rapidly 5+ times
3. Watch console for loading flags
4. Check Network tab - should not see overlapping requests

**Pass Criteria**:
- [ ] Console shows "Device loading already in progress" for rapid clicks
- [ ] No duplicate API requests in Network tab
- [ ] Pagination completes correctly

---

### ✅ Test #6: Total Count Handling (Bug #7)
**What to Test**: Device/alert total count extraction
**Steps**:
1. Look at device count in dashboard metrics
2. Check if it shows "0" or actual count
3. Check browser console for logs like "Total count missing or zero"
4. **Expected**: Accurate device counts displayed

**Pass Criteria**:
- [ ] Device totals are accurate (not 0 when devices exist)
- [ ] No pagination showing "Page 1 of 0" errors

---

### ✅ Test #7: Export Downloads (Bug #8) **MOST IMPORTANT**
**What to Test**: Export download functionality
**Steps**:
1. Navigate to Admin tab
2. Find "Export Library" or "Endpoint Catalog" section
3. Find any export with a "Download" button
4. Click the Download button
5. **Expected**: After 1-2 seconds, file download starts
6. Check your Downloads folder for the exported file

**Pass Criteria**:
- [ ] Button shows spinner briefly
- [ ] Download starts automatically (check browser download bar)
- [ ] File appears in Downloads folder
- [ ] Toast message shows "Export downloaded: [filename]"
- [ ] If download blocked, new window opens instead

**Note**: If no download button visible, this feature may require specific data first.

---

### ✅ Test #8: OAuth Token Handling (Bug #6)
**What to Test**: Token failure recovery
**Steps**:
1. This is hard to test directly
2. Watch for any "OAuth" errors in console
3. If OAuth fails, check PHP error logs on server

**Pass Criteria**:
- [ ] No repeated OAuth failures
- [ ] If token fails, error is logged and retried correctly
- [ ] No stuck authentication states

---

## Console Logs to Look For

### ✅ Good Logs (Expected):
```
[INFO] Device loading already in progress, skipping duplicate request
[INFO] Card registry not loaded yet, deferring sanitization
[INFO] Loading user preferences...
[INFO] Devices loaded successfully
```

### ❌ Bad Logs (Report These):
```
[ERROR] Failed to load devices: <vague error>
Uncaught TypeError: Cannot read property 'total' of null
Uncaught ReferenceError: hydrateDeviceLookup is not defined
Download click failed: <error>
```

---

## Quick Smoke Test (2 minutes)

If you're short on time, run this quick test:

1. **Login** → Should work without errors
2. **Dashboard loads** → Metrics show numbers
3. **Click device row** → Modal opens
4. **Click Download on any export** → File downloads
5. **Check console** → No red errors

If all 5 pass = ✅ Deployment successful!

---

## Rollback Plan

If critical issues found:

1. Navigate to: `C:\Users\jez.slade\Desktop\Projects\MPSM-Dashboard`
2. Run: `git reset --hard 75b7aa2` (previous working commit)
3. Run: `powershell -ExecutionPolicy Bypass -File deploy-all.ps1`

---

## Contact

If you find bugs not listed here, please provide:
1. Screenshot of browser console (F12)
2. Steps to reproduce
3. Expected vs actual behavior

---

**Status**: Ready for Testing
**Tester**: Jez Slade
**Browser**: Chrome/Edge recommended
