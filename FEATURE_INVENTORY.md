# MPSM Dashboard - Complete Feature & Function Inventory

**Version:** 2.1.0 (Performance Optimized)
**Date:** 2025-11-06
**Purpose:** Comprehensive testing checklist for end-user validation

---

## 1. Authentication & Session Management

### 1.1 Login System
- [ ] **URL:** https://mpsm.resolutionsbydesign.us/cms/login.html
- [ ] Enter username: `admin`
- [ ] Enter password: `admin`
- [ ] Click "Login" button
- [ ] ✅ **PASS:** Redirects to dashboard
- [ ] ❌ **FAIL:** Shows error message or stays on login page

### 1.2 Session Persistence
- [ ] Login successfully
- [ ] Refresh page (F5)
- [ ] ✅ **PASS:** Stays logged in
- [ ] Navigate to different tabs
- [ ] ✅ **PASS:** No re-authentication required

### 1.3 Logout
- [ ] Click user icon/logout button (if visible)
- [ ] Or navigate to `/cms/api/logout.php`
- [ ] ✅ **PASS:** Redirects to login page
- [ ] Try accessing dashboard
- [ ] ✅ **PASS:** Redirects back to login

### 1.4 Session Timeout
- [ ] Login successfully
- [ ] Wait 60+ minutes (session timeout)
- [ ] Try to interact with dashboard
- [ ] ✅ **PASS:** Session expires, redirects to login

---

## 2. Dashboard UI (Main Page)

### 2.1 Dashboard Tab
- [ ] **URL:** https://mpsm.resolutionsbydesign.us/cms/
- [ ] ✅ **PASS:** Dashboard tab is active by default
- [ ] ✅ **PASS:** Cards are visible in grid layout

### 2.2 Dashboard Cards (Verify All Load)

#### Customer Overview Card
- [ ] Icon: Building icon (fas fa-building)
- [ ] Title: "Customer Snapshot"
- [ ] ✅ **PASS:** Shows device count headline
- [ ] ✅ **PASS:** Shows metrics (Connectors, Alerts, Enabled)
- [ ] Click card to open modal
- [ ] ✅ **PASS:** Modal shows device contact, books, supply alerts

#### Connectors Card
- [ ] Icon: Network icon (fas fa-network-wired)
- [ ] Title: "Connectors"
- [ ] ✅ **PASS:** Shows total connector count
- [ ] ✅ **PASS:** Shows Online/Offline status badge
- [ ] ✅ **PASS:** Shows active count (24h)
- [ ] Click card to open modal
- [ ] ✅ **PASS:** Modal shows connector details table

#### Devices Card
- [ ] Icon: Printer icon (fas fa-print)
- [ ] Title: "Devices"
- [ ] ✅ **PASS:** Shows total device count
- [ ] ✅ **PASS:** Shows offline device count (red if > 0)
- [ ] ✅ **PASS:** Shows model variety count
- [ ] Click card to open modal
- [ ] ✅ **PASS:** Modal shows paginated device table with:
  - Equipment ID column
  - Model column
  - Serial Number column
  - IP Address column
  - Location column
  - Status column (Online/Offline badges)
  - Toner level columns (K, C, M, Y with color badges)

#### Supply Alerts Card
- [ ] Icon: Warning icon (fas fa-exclamation-triangle)
- [ ] Title: "Supply Alerts"
- [ ] ✅ **PASS:** Shows total alert count
- [ ] ✅ **PASS:** Shows breakdown by severity
- [ ] Click card to open modal
- [ ] ✅ **PASS:** Modal shows supply alerts table with:
  - Device information
  - Supply type
  - Alert level
  - Initial date
  - Sorting capability

### 2.3 Card Loading States
- [ ] Hard refresh page (Ctrl+Shift+R)
- [ ] ✅ **PASS:** Cards show spinner initially
- [ ] ✅ **PASS:** Cards populate with data (< 3 seconds)
- [ ] ✅ **PASS:** No cards stuck in loading state

### 2.4 Card Error Handling
- [ ] Simulate API failure (disconnect network)
- [ ] Refresh dashboard
- [ ] ✅ **PASS:** Cards show error message (not blank)
- [ ] ✅ **PASS:** Error icon displayed (fas fa-exclamation-triangle)

---

## 3. Global Device Search

### 3.1 Search Bar
- [ ] **Location:** Top of dashboard (search icon)
- [ ] ✅ **PASS:** Search bar is visible
- [ ] ✅ **PASS:** Placeholder text: "Search devices..."

### 3.2 Search Functionality
- [ ] Type device serial number (e.g., "VNC2345678")
- [ ] ✅ **PASS:** Dropdown appears with results (< 1 second)
- [ ] ✅ **PASS:** Shows top 10 matching devices
- [ ] ✅ **PASS:** Each result shows: Model, Serial, Location

### 3.3 Search by Different Fields
- [ ] Search by serial number
- [ ] ✅ **PASS:** Finds device
- [ ] Search by asset number
- [ ] ✅ **PASS:** Finds device
- [ ] Search by IP address
- [ ] ✅ **PASS:** Finds device
- [ ] Search by model name
- [ ] ✅ **PASS:** Finds matching devices
- [ ] Search by customer name
- [ ] ✅ **PASS:** Finds devices for that customer

### 3.4 Search Result Selection
- [ ] Type search query
- [ ] Click on a result
- [ ] ✅ **PASS:** Device deep-dive modal opens
- [ ] ✅ **PASS:** Modal shows selected device details

### 3.5 Search Edge Cases
- [ ] Search with no results (e.g., "XYZABC999")
- [ ] ✅ **PASS:** Shows "No results found" message
- [ ] Search with special characters (e.g., "192.168.1.1")
- [ ] ✅ **PASS:** Handles gracefully
- [ ] Clear search box
- [ ] ✅ **PASS:** Dropdown closes

---

## 4. Device Deep-Dive Modal

### 4.1 Open Device Modal
- [ ] **Method 1:** Search for device, click result
- [ ] **Method 2:** Click device row in card table
- [ ] **Method 3:** Call `window.MPSM.openDeviceModal(deviceId)` in console
- [ ] ✅ **PASS:** Modal opens (< 500ms)

### 4.2 Modal Header
- [ ] ✅ **PASS:** Shows device model name
- [ ] ✅ **PASS:** Shows serial number
- [ ] ✅ **PASS:** Close button (X) visible

### 4.3 Device Information Section
- [ ] ✅ **PASS:** Equipment ID displayed
- [ ] ✅ **PASS:** Serial Number displayed
- [ ] ✅ **PASS:** Model displayed
- [ ] ✅ **PASS:** Location/Office displayed
- [ ] ✅ **PASS:** IP Address displayed
- [ ] ✅ **PASS:** Asset Number displayed (if available)

### 4.4 Meter Readings Section
- [ ] ✅ **PASS:** Section titled "Counter Details" or "Meters"
- [ ] ✅ **PASS:** Black & White counter shown
- [ ] ✅ **PASS:** Color counter shown (if color device)
- [ ] ✅ **PASS:** Total counter shown
- [ ] ✅ **PASS:** Meter date shown

### 4.5 Supply Levels Section
- [ ] ✅ **PASS:** Section titled "Supply Levels" or "Toner"
- [ ] ✅ **PASS:** Black toner percentage (K)
- [ ] ✅ **PASS:** Cyan toner percentage (C)
- [ ] ✅ **PASS:** Magenta toner percentage (M)
- [ ] ✅ **PASS:** Yellow toner percentage (Y)
- [ ] ✅ **PASS:** Color-coded badges (black, cyan, magenta, yellow)
- [ ] ✅ **PASS:** Low supplies highlighted in red/orange

### 4.6 Supply Alerts Section
- [ ] ✅ **PASS:** Section titled "Supply Alerts"
- [ ] Device with alerts:
  - [ ] ✅ **PASS:** Shows list of active alerts
  - [ ] ✅ **PASS:** Alert severity indicated (color badges)
  - [ ] ✅ **PASS:** Alert date shown
- [ ] Device without alerts:
  - [ ] ✅ **PASS:** Shows "No active supply alerts"

### 4.7 Device Health Section
- [ ] ✅ **PASS:** Section titled "Device Health" or "Actions"
- [ ] Device with health data:
  - [ ] ✅ **PASS:** Shows recommended actions
  - [ ] ✅ **PASS:** Shows firmware update status
  - [ ] ✅ **PASS:** Shows health indicators
- [ ] Device without health data:
  - [ ] ✅ **PASS:** Shows "No health data available"

### 4.8 Panel Message History Section
- [ ] ✅ **PASS:** Section titled "Panel Message History"
- [ ] Device with panel messages:
  - [ ] ✅ **PASS:** Shows recent messages (last 100)
  - [ ] ✅ **PASS:** Each message shows timestamp
  - [ ] ✅ **PASS:** Each message shows alert code
  - [ ] ✅ **PASS:** Each message shows configuration
  - [ ] ✅ **PASS:** Messages sorted by date (newest first)
- [ ] Device without panel messages:
  - [ ] ✅ **PASS:** Shows "No panel messages recorded"

### 4.9 Modal Performance
- [ ] Open device modal 5 times (different devices)
- [ ] ✅ **PASS:** Each modal opens in < 500ms
- [ ] ✅ **PASS:** Data loads from cache (not live API)
- [ ] Check browser Network tab
- [ ] ✅ **PASS:** Uses `get-device-deep-dive.php?serialNumber=...`
- [ ] ✅ **PASS:** Response time < 100ms (cached)

### 4.10 Modal Close
- [ ] Click X button
- [ ] ✅ **PASS:** Modal closes
- [ ] Click outside modal (backdrop)
- [ ] ✅ **PASS:** Modal closes
- [ ] Press Escape key
- [ ] ✅ **PASS:** Modal closes

---

## 5. Admin Tab

### 5.1 Admin Tab Navigation
- [ ] Click "Admin" tab at top
- [ ] ✅ **PASS:** Admin tab becomes active
- [ ] ✅ **PASS:** Dashboard cards hidden
- [ ] ✅ **PASS:** Admin cards displayed

### 5.2 System Health Card
- [ ] ✅ **PASS:** Card visible with title "System Health"
- [ ] Click card to view details
- [ ] ✅ **PASS:** Shows sections:
  - **Database Status:**
    - [ ] Connection status (green = good)
    - [ ] Query time (< 10ms)
    - [ ] Database name
    - [ ] Table count
    - [ ] Visitor log entries
  - **MPS API Status:**
    - [ ] Connection status
    - [ ] Response time
    - [ ] OAuth token status
  - **Cache Status:**
    - [ ] Enabled/Disabled
    - [ ] Cached entries count
    - [ ] Fresh entries count
    - [ ] Storage size (MB)
  - **Session Status:**
    - [ ] Active session indicator
    - [ ] Current username
  - **Server Metrics:**
    - [ ] PHP version
    - [ ] Memory usage
    - [ ] Disk space
    - [ ] Load average (if available)

### 5.3 Database Monitor Card
- [ ] ✅ **PASS:** Card visible
- [ ] Click to open
- [ ] ✅ **PASS:** Shows database statistics:
  - [ ] Total devices cached
  - [ ] Total drill-downs cached
  - [ ] Panel messages count
  - [ ] Visitor log entries
  - [ ] Cache age (time since last refresh)

### 5.4 Visitor Analytics Card
- [ ] ✅ **PASS:** Card visible
- [ ] Click to open modal
- [ ] ✅ **PASS:** Shows visitor log table:
  - [ ] Username column
  - [ ] IP Address column
  - [ ] Page URL column
  - [ ] Timestamp column
  - [ ] User Agent column
- [ ] ✅ **PASS:** Pagination controls work
- [ ] ✅ **PASS:** Filter by username works
- [ ] ✅ **PASS:** Filter by IP works
- [ ] ✅ **PASS:** Date range filter works
- [ ] ✅ **PASS:** Export to CSV button works

### 5.5 Error Logs Card
- [ ] ✅ **PASS:** Card visible
- [ ] Click to open
- [ ] ✅ **PASS:** Shows PHP error log entries
- [ ] ✅ **PASS:** Sorted by timestamp (newest first)
- [ ] ✅ **PASS:** Shows error type (Warning, Error, Fatal)
- [ ] ✅ **PASS:** Shows error message
- [ ] ✅ **PASS:** Shows file and line number

### 5.6 Endpoint Catalog Card
- [ ] ✅ **PASS:** Card visible
- [ ] Click to open
- [ ] ✅ **PASS:** Shows MPS API endpoint list (544 endpoints)
- [ ] ✅ **PASS:** Search/filter endpoints
- [ ] ✅ **PASS:** Shows endpoint parameters
- [ ] ✅ **PASS:** Shows endpoint descriptions
- [ ] ✅ **PASS:** Grouped by category (Device, Customer, Supply, etc.)

---

## 6. Panel Message Monitoring

### 6.1 Panel Message Monitor Page
- [ ] **URL:** https://mpsm.resolutionsbydesign.us/cms/panel-message-monitor.php
- [ ] ✅ **PASS:** Page loads successfully
- [ ] ✅ **PASS:** Shows panel message list

### 6.2 Message List Display
- [ ] ✅ **PASS:** Shows messages in table format:
  - [ ] ID column
  - [ ] Timestamp column
  - [ ] Customer column
  - [ ] Device Serial column
  - [ ] Alert Code column
  - [ ] Panel Configuration column
  - [ ] Payload button

### 6.3 Time Window Filter
- [ ] Filter dropdown visible (1 hour, 3 hours, 6 hours, 12 hours, 24 hours, 48 hours, 72 hours, 1 week)
- [ ] Select "Last 24 hours"
- [ ] ✅ **PASS:** Table updates to show messages from last 24 hours
- [ ] Select "Last 1 hour"
- [ ] ✅ **PASS:** Table updates to show recent messages only

### 6.4 Display Limit Filter
- [ ] Limit dropdown visible (10, 25, 50, 100, 200, 500)
- [ ] Select "50"
- [ ] ✅ **PASS:** Table shows max 50 messages
- [ ] Select "500"
- [ ] ✅ **PASS:** Table shows up to 500 messages

### 6.5 Device Serial Search
- [ ] Search box visible
- [ ] Enter device serial number
- [ ] ✅ **PASS:** Table filters to show only that device's messages
- [ ] Clear search
- [ ] ✅ **PASS:** Shows all messages again

### 6.6 Payload Viewer
- [ ] Click "View Payload" button on any message
- [ ] ✅ **PASS:** Modal opens
- [ ] ✅ **PASS:** Shows full JSON payload
- [ ] ✅ **PASS:** JSON is formatted/prettified
- [ ] ✅ **PASS:** Includes customer, device, alert details
- [ ] Close modal
- [ ] ✅ **PASS:** Returns to message list

### 6.7 Auto-Refresh
- [ ] Leave panel message monitor open
- [ ] Wait 30 seconds
- [ ] ✅ **PASS:** Page auto-refreshes (new messages appear)
- [ ] ✅ **PASS:** Refresh counter visible

### 6.8 Empty State
- [ ] Filter to time window with no messages
- [ ] ✅ **PASS:** Shows "No panel messages found" message

---

## 7. Payload Debugger

### 7.1 Payload Debugger Page
- [ ] **URL:** https://mpsm.resolutionsbydesign.us/cms/payload-debugger.php
- [ ] ✅ **PASS:** Page loads successfully
- [ ] ✅ **PASS:** Shows payload debug log

### 7.2 Debug Log Display
- [ ] ✅ **PASS:** Shows debug entries in table:
  - [ ] ID column
  - [ ] Timestamp column
  - [ ] IP Address column
  - [ ] HTTP Method column
  - [ ] Status column (SUCCESS/ERROR badges)
  - [ ] Message column
  - [ ] View Details button

### 7.3 Status Filter
- [ ] Filter dropdown visible (All, SUCCESS, ERROR, PROCESSING)
- [ ] Select "SUCCESS"
- [ ] ✅ **PASS:** Shows only successful webhook calls
- [ ] Select "ERROR"
- [ ] ✅ **PASS:** Shows only failed webhook calls

### 7.4 Auto-Refresh
- [ ] Leave payload debugger open
- [ ] Wait 5 seconds
- [ ] ✅ **PASS:** Page auto-refreshes
- [ ] ✅ **PASS:** New debug entries appear (if any)

### 7.5 Debug Entry Details
- [ ] Click "View Details" on any entry
- [ ] ✅ **PASS:** Modal opens with full details:
  - [ ] Request headers (JSON formatted)
  - [ ] Raw body (JSON formatted)
  - [ ] Content-Type
  - [ ] User-Agent
  - [ ] HTTP status code
  - [ ] Error message (if any)

### 7.6 Statistics Dashboard
- [ ] ✅ **PASS:** Shows summary cards:
  - [ ] Total requests (last hour)
  - [ ] Success rate percentage
  - [ ] Error count
  - [ ] Last request timestamp

---

## 8. Background Cache System

### 8.1 Manual Cache Refresh
- [ ] **URL:** https://mpsm.resolutionsbydesign.us/cms/api/refresh-cache-enhanced.php
- [ ] Load URL in browser
- [ ] ✅ **PASS:** JSON response returned
- [ ] ✅ **PASS:** Shows stats:
  ```json
  {
    "status": "success",
    "stats": {
      "devices_cached": 3000,
      "deleted_devices": 150,
      "devices_with_drilldown": 2950,
      "api_calls_made": 6000,
      "duration": 480.23
    }
  }
  ```

### 8.2 Cache Refresh Logging
- [ ] After manual refresh, check log file
- [ ] **File:** `cms/logs/cache-refresh-YYYY-MM-DD.log`
- [ ] ✅ **PASS:** Log file exists
- [ ] ✅ **PASS:** Contains entries:
  - [ ] "Starting enhanced cache refresh"
  - [ ] "Fetched X devices total"
  - [ ] "Progress: 50 devices cached"
  - [ ] "Cache refresh completed"
  - [ ] Duration and stats

### 8.3 Cache Lock Mechanism
- [ ] Start cache refresh in one tab
- [ ] Immediately start cache refresh in another tab
- [ ] ✅ **PASS:** Second request returns:
  ```json
  {
    "status": "skipped",
    "reason": "refresh in progress"
  }
  ```

### 8.4 Verify Cached Data
- [ ] After cache refresh completes
- [ ] Check database:
  ```sql
  SELECT COUNT(*) FROM mpsm_cache_devices;
  SELECT COUNT(*) FROM mpsm_cache_device_drilldown;
  ```
- [ ] ✅ **PASS:** Row counts match device count
- [ ] ✅ **PASS:** `cached_at` timestamps are recent

### 8.5 Scheduled Refresh (Cron)
- [ ] Wait 5 minutes (or trigger cron manually)
- [ ] Check cache log file
- [ ] ✅ **PASS:** New log entries appear automatically
- [ ] ✅ **PASS:** No errors in log
- [ ] Check database `cached_at` timestamps
- [ ] ✅ **PASS:** Timestamps update every 5 minutes

---

## 9. API Endpoints (Backend Testing)

### 9.1 Authentication Endpoints

#### /cms/api/login.php
```bash
curl -X POST https://mpsm.resolutionsbydesign.us/cms/api/login.php \
  -H "Content-Type: application/json" \
  -d '{"username": "admin", "password": "admin"}'
```
- [ ] ✅ **PASS:** Returns `{"success": true}`

#### /cms/api/logout.php
```bash
curl https://mpsm.resolutionsbydesign.us/cms/api/logout.php
```
- [ ] ✅ **PASS:** Returns `{"success": true}`

### 9.2 Device Endpoints

#### /cms/api/get-cached-devices.php
```bash
curl https://mpsm.resolutionsbydesign.us/cms/api/get-cached-devices.php
```
- [ ] ✅ **PASS:** Returns device list
- [ ] ✅ **PASS:** Response time < 100ms
- [ ] ✅ **PASS:** Contains `{"success": true, "devices": [...], "total": 3000, "cached": true}`

#### /cms/api/get-device-deep-dive.php
```bash
curl "https://mpsm.resolutionsbydesign.us/cms/api/get-device-deep-dive.php?serialNumber=VNC2345678"
```
- [ ] ✅ **PASS:** Returns device details
- [ ] ✅ **PASS:** Response time < 200ms
- [ ] ✅ **PASS:** Contains device, counterDetails, supplyAlerts, panelHistory

#### /cms/api/search-devices.php
```bash
curl "https://mpsm.resolutionsbydesign.us/cms/api/search-devices.php?query=VNC2345"
```
- [ ] ✅ **PASS:** Returns matching devices
- [ ] ✅ **PASS:** Response time < 1 second

### 9.3 Panel Message Endpoints

#### /cms/api/get-panel-messages.php
```bash
curl "https://mpsm.resolutionsbydesign.us/cms/api/get-panel-messages.php?limit=50&hours=24"
```
- [ ] ✅ **PASS:** Returns panel messages
- [ ] ✅ **PASS:** Respects limit and hours parameters
- [ ] ✅ **PASS:** Response time < 100ms (with indexes)

#### /cms/api/get-payload-debug-logs.php
```bash
curl "https://mpsm.resolutionsbydesign.us/cms/api/get-payload-debug-logs.php?status=SUCCESS"
```
- [ ] ✅ **PASS:** Returns debug logs
- [ ] ✅ **PASS:** Filters by status correctly

### 9.4 Admin Endpoints

#### /cms/api/system-health.php
```bash
curl https://mpsm.resolutionsbydesign.us/cms/api/system-health.php
```
- [ ] ✅ **PASS:** Returns system health data
- [ ] ✅ **PASS:** Contains database, mpsApi, cache, server sections

#### /cms/api/get-visitor-logs.php
```bash
curl "https://mpsm.resolutionsbydesign.us/cms/api/get-visitor-logs.php?page=1&limit=50"
```
- [ ] ✅ **PASS:** Returns visitor log entries
- [ ] ✅ **PASS:** Pagination works

### 9.5 Webhook Endpoints

#### /mps-api/callbacks/panel-message.php
```bash
curl -X POST https://mpsm.resolutionsbydesign.us/mps-api/callbacks/panel-message.php \
  -H "Content-Type: application/json" \
  -d '{
    "callbackSecret": "mpsm-panel-message-v1",
    "customer": {"code": "TEST01", "description": "Test Customer"},
    "deviceSerial": "TEST123456",
    "maintenanceAlert": {"code": "C123", "id": "alert-123"}
  }'
```
- [ ] ✅ **PASS:** Returns `{"success": true, "stored": true}`
- [ ] ✅ **PASS:** Message appears in `mpsm_panel_messages` table
- [ ] ✅ **PASS:** Debug entry created in `mpsm_panel_callback_debug`

---

## 10. Browser Compatibility

### 10.1 Chrome/Edge (Chromium)
- [ ] Test on Chrome 120+
- [ ] ✅ **PASS:** Dashboard loads
- [ ] ✅ **PASS:** All cards display
- [ ] ✅ **PASS:** Device modal opens
- [ ] ✅ **PASS:** Search works
- [ ] ✅ **PASS:** No console errors

### 10.2 Firefox
- [ ] Test on Firefox 120+
- [ ] ✅ **PASS:** Dashboard loads
- [ ] ✅ **PASS:** All cards display
- [ ] ✅ **PASS:** Device modal opens
- [ ] ✅ **PASS:** Search works
- [ ] ✅ **PASS:** No console errors

### 10.3 Safari (Mac)
- [ ] Test on Safari 17+
- [ ] ✅ **PASS:** Dashboard loads
- [ ] ✅ **PASS:** All cards display
- [ ] ✅ **PASS:** Device modal opens
- [ ] ✅ **PASS:** Search works
- [ ] ✅ **PASS:** No console errors

### 10.4 Mobile (Responsive Design)
- [ ] Test on mobile device or browser DevTools (mobile view)
- [ ] ✅ **PASS:** Layout adapts to narrow screen
- [ ] ✅ **PASS:** Cards stack vertically
- [ ] ✅ **PASS:** Touch targets are large enough
- [ ] ✅ **PASS:** Modal scrolls properly

---

## 11. Performance Benchmarks

### 11.1 Dashboard Load Time
- [ ] Clear browser cache
- [ ] Navigate to dashboard
- [ ] Measure time until all cards show data
- [ ] ✅ **TARGET:** < 3 seconds
- [ ] ✅ **GOAL:** < 1 second (with optimizations)

### 11.2 Device Modal Load Time
- [ ] Click device from search
- [ ] Measure time until modal fully populated
- [ ] ✅ **TARGET:** < 500ms
- [ ] ✅ **GOAL:** < 100ms (with cache)

### 11.3 Search Response Time
- [ ] Type search query
- [ ] Measure time until dropdown appears
- [ ] ✅ **TARGET:** < 1 second
- [ ] ✅ **GOAL:** < 500ms

### 11.4 Panel Message Query Time
- [ ] Load panel message monitor
- [ ] Measure SQL query time (check logs)
- [ ] ✅ **TARGET:** < 100ms
- [ ] ✅ **GOAL:** < 50ms (with indexes)

### 11.5 Page Size
- [ ] Open DevTools → Network
- [ ] Clear and reload dashboard
- [ ] Check total page size
- [ ] ✅ **TARGET:** < 5MB
- [ ] ✅ **GOAL:** < 2MB

---

## 12. Error Handling

### 12.1 Network Errors
- [ ] Disconnect internet
- [ ] Try to load dashboard
- [ ] ✅ **PASS:** Shows error message (not blank page)
- [ ] Reconnect internet
- [ ] Refresh page
- [ ] ✅ **PASS:** Dashboard loads normally

### 12.2 API Errors
- [ ] Simulate API failure (stop mps-api service)
- [ ] Try to load card
- [ ] ✅ **PASS:** Card shows error state
- [ ] ✅ **PASS:** Other cards continue to work
- [ ] Restore API service
- [ ] Refresh card
- [ ] ✅ **PASS:** Card recovers

### 12.3 Database Errors
- [ ] Simulate database connection failure
- [ ] Try to access dashboard
- [ ] ✅ **PASS:** Shows meaningful error message
- [ ] ✅ **PASS:** Does not expose database credentials

### 12.4 Invalid Input
- [ ] Enter SQL injection in search: `' OR 1=1 --`
- [ ] ✅ **PASS:** Handled safely (no SQL error)
- [ ] Enter XSS attempt: `<script>alert('XSS')</script>`
- [ ] ✅ **PASS:** Escaped properly (no alert fires)

---

## 13. Security Tests

### 13.1 Authentication Required
- [ ] Log out
- [ ] Try to access `/cms/api/get-devices.php` directly
- [ ] ✅ **PASS:** Returns 401 Unauthorized or redirects to login

### 13.2 Session Security
- [ ] Inspect cookies in DevTools
- [ ] ✅ **PASS:** Session cookie has `HttpOnly` flag
- [ ] ✅ **PASS:** Session cookie has `SameSite=Lax` or `Strict`

### 13.3 SQL Injection Prevention
- [ ] Verified: All queries use prepared statements (PDO)
- [ ] ✅ **PASS:** No string concatenation in SQL queries

### 13.4 CORS Headers
- [ ] Check response headers
- [ ] ✅ **PASS:** `Access-Control-Allow-Origin` set to same-origin or trusted domains
- [ ] ✅ **PASS:** No `Access-Control-Allow-Origin: *` (wildcard)

---

## 14. Data Integrity

### 14.1 Device Count Consistency
- [ ] Check dashboard device count
- [ ] Check database: `SELECT COUNT(*) FROM mpsm_cache_devices`
- [ ] Check MPS API device count
- [ ] ✅ **PASS:** All three counts match (±5 tolerance)

### 14.2 Panel Message Storage
- [ ] Send test webhook
- [ ] Check `mpsm_panel_messages` table
- [ ] ✅ **PASS:** Message stored with correct data
- [ ] ✅ **PASS:** JSON payload preserved exactly

### 14.3 Cache Staleness
- [ ] Check cache age: `SELECT MAX(cached_at) FROM mpsm_cache_devices`
- [ ] ✅ **PASS:** Cache is < 10 minutes old (with cron running)

---

## 15. User Experience

### 15.1 Loading Indicators
- [ ] All API calls show loading spinner
- [ ] ✅ **PASS:** User never sees blank screen
- [ ] ✅ **PASS:** User knows system is working

### 15.2 Error Messages
- [ ] Errors are user-friendly (not technical stack traces)
- [ ] ✅ **PASS:** Clear, actionable messages
- [ ] Example: "Unable to load devices. Please try again."

### 15.3 Responsive Feedback
- [ ] Click actions provide immediate feedback
- [ ] ✅ **PASS:** Buttons show active state
- [ ] ✅ **PASS:** Hover states on interactive elements

### 15.4 Accessibility
- [ ] Tab navigation works
- [ ] ✅ **PASS:** Can navigate without mouse
- [ ] Screen reader test (if available)
- [ ] ✅ **PASS:** Alt text on images
- [ ] ✅ **PASS:** ARIA labels on interactive elements

---

## Test Summary Template

**Date:** ___________
**Tested By:** ___________
**Browser:** ___________
**Performance:**
- Dashboard Load: _____ seconds
- Device Modal: _____ ms
- Search Response: _____ ms

**Issues Found:**
1. ___________________________________________
2. ___________________________________________
3. ___________________________________________

**Critical Bugs:** ___________
**Minor Issues:** ___________
**Suggestions:** ___________

**Overall Status:**
- [ ] ✅ PASS - Ready for production
- [ ] ⚠️ PASS WITH ISSUES - Minor fixes needed
- [ ] ❌ FAIL - Critical issues, rollback recommended

---

## Quick Smoke Test Checklist (5 Minutes)

Use this for quick validation after deployment:

1. [ ] Login works
2. [ ] Dashboard loads with data
3. [ ] Search returns results
4. [ ] Device modal opens
5. [ ] Panel message monitor shows messages
6. [ ] System health shows green
7. [ ] No JavaScript errors in console
8. [ ] No PHP errors in logs

**If all 8 pass:** ✅ Deployment successful!

---

**END OF FEATURE INVENTORY**
