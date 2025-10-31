# MPSM Dashboard - Known Pain Points & Solutions

**Last Updated**: 2025-10-31
**Maintainer**: Project Team

---

## Purpose

This document captures known pain points, recurring issues, and their workarounds. When you encounter a problem, check here first before investigating. When you solve a problem, document it here for the next developer.

---

## Table of Contents

1. [Development Environment](#development-environment)
2. [API & Backend](#api--backend)
3. [Frontend & UI](#frontend--ui)
4. [Deployment](#deployment)
5. [Testing](#testing)
6. [Data & State Management](#data--state-management)

---

## Development Environment

### Pain Point 1.1: File Paths in Windows/Git Bash

**Problem**: Bash scripts fail with Windows paths (`C:\Users\...`)

**Symptoms**:
```bash
curl: (37) Couldn't open file /c/Users/...
/usr/bin/bash: c:/tmp/file.json: No such file or directory
```

**Root Cause**: Git Bash translates Windows paths inconsistently

**Solution**:
- Use `/tmp/` for temporary files (works in Git Bash)
- Use relative paths in scripts
- For curl output, use `/tmp/cookies.txt` not `c:/tmp/cookies.txt`
- For Python file reads, use `c:/tmp/file.json` not `/tmp/file.json`

**Example**:
```bash
# GOOD
curl ... -o /tmp/response.json
python -c "import sys, json; data = json.load(sys.stdin)" < /tmp/response.json

# BAD
curl ... -o c:/tmp/response.json  # Fails in bash
```

**Status**: Permanent Limitation

---

### Pain Point 1.2: PowerShell Command Syntax Errors

**Problem**: Inline PowerShell commands fail with syntax errors when using special characters

**Symptoms**:
```
= : The term '=' is not recognized as the name of a cmdlet
! : Invalid escape sequence
```

**Root Cause**: Bash escaping conflicts with PowerShell syntax

**Solution**: Always create separate `.ps1` script files for PowerShell commands

**Example**:
```bash
# BAD - Inline PowerShell
powershell -Command "$var = 'value'; Write-Host $var"

# GOOD - Separate script file
# 1. Create deploy.ps1
Write-File deploy.ps1 "..."

# 2. Execute script
powershell -ExecutionPolicy Bypass -File deploy.ps1
```

**Workaround**: Use `Write` tool to create `.ps1` file first

**Status**: Permanent Limitation

---

### Pain Point 1.3: JSON Parsing Errors with Python

**Problem**: `python -m json.tool` fails with "Expecting value" or "Invalid argument"

**Symptoms**:
```
OSError: [Errno 22] Invalid argument
Expecting value: line 1 column 1 (char 0)
```

**Root Cause**:
- Piping large JSON through head/tail truncates mid-structure
- Windows line endings in JSON
- Extra text before/after JSON

**Solution**:
- Use `python -c` with json.load() instead of json.tool
- Don't pipe JSON through `head` or `tail`
- Use `sys.stdin` for piped input

**Example**:
```bash
# BAD
curl ... | python -m json.tool | head -50  # Truncates JSON

# GOOD
curl ... | python -c "import sys, json; data = json.load(sys.stdin); print(data['devices'][0])"
```

**Status**: Permanent Limitation

---

## API & Backend

### Pain Point 2.1: Device Search Doesn't Find All Devices

**Problem**: API pagination limits results, search only finds first 100-200 devices

**Symptoms**:
- Device "EB821" not found despite being in system
- Search returns "No devices found" for known devices
- Total devices = 3,306 but search only covers ~100

**Root Cause**:
- API returns max 200 devices per request
- Backend further limits to ~100 devices per page
- Search operates on loaded data only (no pagination)

**Solution**: Implemented client-side pagination with caching (ADR-0005)

**Workaround** (for modal searches):
- Load first page, note total devices
- Manually paginate through pages to find device
- Use global search bar (header) which now paginates automatically

**Fixed**: 2025-10-31 (for global search only)

**Remaining Issue**: Modal search bars still limited to loaded data

---

### Pain Point 2.2: API Returns Different Device Counts

**Problem**: API returns inconsistent device counts depending on parameters

**Symptoms**:
```bash
# Same customer, different counts
GET /api/get-devices.php → 957 devices
GET /api/get-devices.php?allCustomers=true → 3,306 devices
```

**Root Cause**: Default customer filter limits results to current customer

**Solution**:
- Use `allCustomers=true` for global searches
- Use default (no parameter) for customer-specific views
- Document expected behavior in API docs

**Workaround**: Always specify `allCustomers` parameter explicitly

**Status**: By Design (not a bug)

---

### Pain Point 2.3: OAuth Token Refresh Failures

**Problem**: API calls fail with "token expired" despite refresh logic

**Symptoms**:
- Intermittent 401 Unauthorized errors
- "Failed to contact mps-api backend" messages
- Works after page refresh

**Root Cause**: Token cached in PHP static variable doesn't survive process restart

**Solution**: Implemented token reset on failure (Bug Fix #6)

**Code Location**: `cms/functions.php` getMPSToken()

**Workaround**: Refresh page to trigger new token acquisition

**Fixed**: 2025-10-31

---

### Pain Point 2.4: Slow API Response Times

**Problem**: Device list API takes 15-30 seconds to respond

**Symptoms**:
- Loading spinner shows for extended time
- User thinks page is frozen
- Timeout errors occasionally

**Root Cause**:
- Backend API timeout was 30 seconds
- HP SDS API is slow (~10-20 seconds per request)
- No response streaming

**Solution**: Reduced timeout to 15 seconds (Bug Fix #5)

**Workaround**:
- Show loading indicator with progress text
- Allow user to continue interacting with page
- Implement request cancellation

**Partially Fixed**: 2025-10-31 (timeout reduced, but still slow)

---

## Frontend & UI

### Pain Point 3.1: CSS Variables Undefined

**Problem**: Global search bar and other components show broken styling

**Symptoms**:
- Search bar has no background color
- Buttons show default browser styles
- Theme toggle doesn't work
- Console shows `var(--primary-color)` as invalid

**Root Cause**: CSS variables used but not defined in `:root`

**Solution**: Added all missing variables to `style.css` (lines 8-45)

**Variables Added**:
- `--primary-color`, `--primary-rgb`
- `--card-background`
- `--text-muted`
- `--hover-background`
- `--status-danger`

**Fixed**: 2025-10-31

---

### Pain Point 3.2: Pagination Inconsistency

**Problem**: Different tables show different rows per page (5, 20, 25, 50)

**Symptoms**:
- Endpoint Catalog shows 5 rows
- Device List shows 25 rows
- Export Library shows 25 rows
- Print Volume shows 20 rows

**Root Cause**: Each table hardcoded different `pageSize` value

**Solution**: Standardized all tables to 50 rows per page

**Files Modified**:
- `cms/assets/app.js` (multiple locations)
- `cms/assets/js/card-registry.js` (7 tables updated)

**Fixed**: 2025-10-31

---

### Pain Point 3.3: Race Conditions in Device Loading

**Problem**: Clicking "Refresh" rapidly causes duplicate API calls and state corruption

**Symptoms**:
- Multiple identical API requests in network tab
- Incorrect device counts
- UI flickers
- Console warnings about state mutations

**Root Cause**: No loading flag to prevent concurrent requests

**Solution**: Added `isLoadingDevices` and `isLoadingAlerts` flags (Bug Fix #1)

**Code Location**: `cms/assets/app.js` lines 25, 2051-2143

**Workaround**: Disable refresh button while loading

**Fixed**: 2025-10-31

---

### Pain Point 3.4: Export Download Doesn't Trigger

**Problem**: Clicking "Download" button doesn't save file to Downloads folder

**Symptoms**:
- Button click has no visible effect
- No error message
- File blob created but not downloaded
- Works sporadically in different browsers

**Root Cause**: Browser security restrictions on programmatic downloads

**Solution**: Implemented 3-tier download strategy (Bug Fix #8):
1. `link.click()` (most reliable)
2. `dispatchEvent(MouseEvent)` (fallback)
3. `window.open()` (last resort with instructions)

**Code Location**: `cms/assets/js/card-registry.js` lines 967-1025

**Workaround**: Right-click on generated URL and "Save As..."

**Partially Fixed**: 2025-10-31 (more reliable, but browser-dependent)

---

### Pain Point 3.5: Modal Search Doesn't Search All Data

**Problem**: Search bar inside device list modal only searches visible/loaded devices

**Symptoms**:
- Searching for "EB821" in modal returns no results
- Searching in global header finds it
- Modal search shows "0 of 3306"

**Root Cause**: Modal uses TableUtils which only searches currently loaded rows

**Solution**: Not yet implemented

**Workaround**: Use global search bar in header (searches all devices)

**Status**: Known Issue - Low Priority

---

## Deployment

### Pain Point 4.1: FTP Upload Failures

**Problem**: PowerShell FTP upload randomly fails with "Could not upload file"

**Symptoms**:
```
ERROR uploading cms/index.php : Exception calling "UploadFile"
```

**Root Cause**:
- Network timeout
- FTP connection limit reached
- File permissions issue
- FTP server temporary unavailability

**Solution**: Retry mechanism with exponential backoff

**Workaround**:
1. Check file exists locally: `Test-Path cms/index.php`
2. Verify FTP credentials
3. Try uploading single file first
4. Wait 30 seconds and retry

**Example Retry Script**:
```powershell
$retries = 3
for ($i = 1; $i -le $retries; $i++) {
    try {
        $webclient.UploadFile($remotePath, $localFile)
        break
    } catch {
        if ($i -eq $retries) { throw }
        Start-Sleep -Seconds ($i * 5)
    }
}
```

**Status**: Permanent Limitation (FTP reliability)

---

### Pain Point 4.2: Deployment Doesn't Immediately Reflect

**Problem**: After deploying files, live site still shows old version

**Symptoms**:
- Deployed new code but site unchanged
- Browser shows old JavaScript
- API returns old responses

**Root Cause**:
- Browser caching (CSS/JS)
- CDN caching
- PHP opcache
- Service worker caching

**Solution**:
1. Hard refresh browser: Ctrl+Shift+R (Windows) or Cmd+Shift+R (Mac)
2. Clear PHP opcache: (if available) `opcache_reset()`
3. Add cache-busting query param: `app.js?v=timestamp`

**Workaround**:
- Wait 2-5 minutes for cache expiry
- Test in incognito/private browser window
- Use curl to bypass browser cache: `curl -H "Cache-Control: no-cache" URL`

**Example Verification**:
```bash
# Check if file deployed
curl -s "https://mpsm.resolutionsbydesign.us/cms/assets/app.js" | grep "fetchAllDevicesForSearch"

# If not found, wait and retry
```

**Status**: Permanent Limitation (caching)

---

### Pain Point 4.3: Git Line Ending Warnings

**Problem**: Git shows CRLF warnings when committing

**Symptoms**:
```
warning: in the working copy of '.claude/settings.local.json', LF will be replaced by CRLF
```

**Root Cause**: Windows (CRLF) vs Unix (LF) line ending differences

**Solution**: Configure Git to handle line endings automatically

```bash
# Set core.autocrlf
git config --global core.autocrlf true  # Windows
git config --global core.autocrlf input # Mac/Linux

# Or add .gitattributes
echo "* text=auto" > .gitattributes
```

**Workaround**: Ignore warnings (Git handles it automatically)

**Status**: Cosmetic Issue (safe to ignore)

---

## Testing

### Pain Point 5.1: Can't Test Live Site Without Login

**Problem**: Testing API endpoints requires authentication session

**Symptoms**:
```bash
curl https://mpsm.resolutionsbydesign.us/cms/api/get-devices.php
# Returns: 302 Redirect to login.html
```

**Root Cause**: PHP session authentication required for all API endpoints

**Solution**: Login first and save session cookie

**Example**:
```bash
# 1. Login and save cookie
curl -s -X POST "https://mpsm.resolutionsbydesign.us/cms/api/login.php" \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin"}' \
  -c /tmp/cookies.txt

# 2. Use cookie for subsequent requests
curl -s "https://mpsm.resolutionsbydesign.us/cms/api/get-devices.php" \
  -b /tmp/cookies.txt
```

**Workaround**: Create test harness HTML file (see `test_frontend.html`)

**Status**: By Design (security requirement)

---

### Pain Point 5.2: Offline Device Count Always Shows Zero

**Problem**: Dashboard shows "0 Offline Devices" despite devices being unplugged

**Symptoms**:
- Device EB821 is unplugged (verified physically)
- Dashboard shows "0 offline"
- Device modal shows "Not online" status
- API returns `IsOffline: false` for unplugged device

**Root Cause**: Backend determines `IsOffline` based on last contact time, not real-time status

**Solution**: Not yet investigated

**Workaround**:
- Check "Last Contacted" timestamp
- If > 24 hours, assume offline
- Backend may need to adjust offline threshold

**Status**: Known Issue - Under Investigation

---

### Pain Point 5.3: Search Test Data Scattered Across Pages

**Problem**: Test device "EB821" not in first 15 pages, hard to verify search works

**Symptoms**:
- Manual testing requires loading many pages
- Automated tests can't find known test device
- No way to seed test data

**Root Cause**: Production data source (HP API), no test environment

**Solution**: Create mock API endpoint for testing

**Workaround**:
- Search for common devices: "HP LASERJET"
- Use ExternalIdentifier from first page: "EE482", "FU999"
- Accept that EB821 location is unknown until user tests

**Status**: Test Environment Needed (future enhancement)

---

## Data & State Management

### Pain Point 6.1: Device Lookup Map Race Condition

**Problem**: `getEquipmentIdFromDevice()` returns 'N/A' for valid devices

**Symptoms**:
- Equipment ID shows 'N/A' in UI
- Device lookup map empty
- Device modal fails to open

**Root Cause**: Lookup map not yet hydrated when function called

**Solution**: Deferred sanitization if lookup map empty (Bug Fix #4)

**Code Location**: `cms/assets/app.js` lines 75-88 (sanitizeCardOrder)

**Workaround**: Wait for device load to complete before opening modals

**Fixed**: 2025-10-31

---

### Pain Point 6.2: Alert Summary Null Reference Errors

**Problem**: Console errors "Cannot read property of null" in alert processing

**Symptoms**:
```
Uncaught TypeError: Cannot read properties of null (reading 'total')
```

**Root Cause**: `alertSummary` initialized as `null` instead of `{}`

**Solution**: Changed initialization to empty object (Bug Fix #3)

**Code Location**: `cms/assets/app.js` line 25 (state.alertSummary)

**Fixed**: 2025-10-31

---

### Pain Point 6.3: Total Count Extraction Failures

**Problem**: Device total count shows 0 despite devices being returned

**Symptoms**:
- Pagination shows "Page 1 of 0"
- `total` field missing or zero in API response
- Devices display correctly but count wrong

**Root Cause**: Backend API returns total count in different fields (`total_rows`, `total_count`, `TotalCount`, `TotalRows`)

**Solution**: Fallback logic with logging (Bug Fix #7)

**Code Location**: `cms/api/get-devices.php` lines 85-95

**Workaround**: If total missing, use `count($devices)` as fallback

**Fixed**: 2025-10-31

---

## How to Add a Pain Point

When you encounter a new issue:

1. **Verify it's reproducible**: Test in multiple scenarios
2. **Check this document**: May already be documented
3. **Document the pain point** using this template:

```markdown
### Pain Point X.Y: [Short Title]

**Problem**: One-sentence description

**Symptoms**:
- Bullet list of how it manifests
- Include error messages
- Include console output

**Root Cause**: Technical explanation

**Solution**: How it was fixed (if resolved)

**Code Location**: `file.ext` line(s) X-Y

**Workaround**: Temporary solution if not fixed

**Status**: [Known Issue | Fixed YYYY-MM-DD | Under Investigation | Wont Fix]
```

4. **Submit PR** with pain point addition
5. **Update** when resolved with fix details

---

## Quick Reference

### Most Common Issues:
1. **Can't find device in search** → Use global search bar (header), not modal search
2. **API returns 0 devices** → Add `allCustomers=true` parameter
3. **PowerShell script fails** → Create separate `.ps1` file, don't use inline
4. **Download button doesn't work** → Check browser console, may need popup permission
5. **Deployed code not showing** → Hard refresh browser (Ctrl+Shift+R)

### Emergency Contacts:
- **Production Site**: https://mpsm.resolutionsbydesign.us/cms/
- **FTP Server**: ftp.resolutionsbydesign.us
- **GitHub Repo**: https://github.com/JezSlade/MPSM-Dashboard
- **GitHub Issues**: https://github.com/JezSlade/MPSM-Dashboard/issues

---

### Pain Point 6.3: Device EB821 Not Found in Search

**Problem**: Device exists in HP MPSM official site but not in dashboard search or exports

**Symptoms**:
- Device visible on HP MPSM website
- Device does NOT appear in dashboard global search
- Device does NOT appear in dashboard exports
- Error: "No devices found (searched X devices)"

**Root Cause**: HP SDS API has TWO separate device lists:
1. `Device/List` - Currently installed/active devices only
2. `Device/Deleted/List` - Uninstalled/historical devices (where EB821 lives)

Dashboard was only querying `Device/List`, missing uninstalled devices.

**Solution**: Query BOTH endpoints and combine results
1. Modified `fetchAllDevices()` to include uninstalled devices by default
2. Created `get-deleted-devices.php` API endpoint
3. Updated global search to use server-side cache with both datasets
4. Added `IsUninstalled` flag and visual badge in UI

**Status**: ✅ Fixed (2025-10-31)

---

### Pain Point 6.4: Global Search Takes 30+ Seconds

**Problem**: Global search extremely slow on first load, every user waits 30+ seconds

**Root Cause**:
1. Sequential pagination - 34 pages fetched one-by-one
2. No shared cache - each user's browser has separate cache
3. Client-side caching - expires per-user, no background refresh

**Solution**: Server-side cache with background refresh
1. Created `get-cached-devices.php` - Serves pre-warmed cache
2. Created `refresh-cache-cron.php` - Background refresh every 5 minutes
3. Setup cron: `*/5 * * * * curl https://mpsm.resolutionsbydesign.us/cms/api/refresh-cache-cron.php`

**Performance**: 30s → <1s (instant for all users)

**Status**: ✅ Fixed (2025-10-31) - Requires cron setup

---

### Pain Point 6.5: Supply Alerts Show Serial Numbers Instead of Equipment IDs

**Problem**: Supply Alerts modal shows serial numbers in Equipment ID column instead of proper Equipment IDs

**Root Cause**: `getEquipmentIdFromAlert()` had different logic than `getEquipmentIdFromDevice()`

**Solution**: Aligned alert logic with device logic - Priority: AssetNumber > ExternalIdentifier > SerialNumber

**Status**: ✅ Fixed (2025-10-31)

---

*Last Updated: 2025-10-31 | Next Review: 2025-11-30*
