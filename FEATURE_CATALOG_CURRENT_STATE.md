# Complete Feature Catalog - Current State

**Date:** 2025-11-07
**Purpose:** Comprehensive inventory of ALL features to validate against post-refactor state
**Status:** Pre-Refactor Baseline

---

## Authentication & Session Management

### Login System
- **File:** [cms/login.html](cms/login.html), [cms/api/login.php](cms/api/login.php)
- **Features:**
  - Username/password authentication
  - Session creation with secure cookies
  - Multiple input method support (php://input, $_POST, raw body)
  - Bcrypt password hashing
  - Visitor tracking on login
- **Access Level:** Public (unauthenticated)
- **Status:** ✅ Working
- **Test:** Login with admin/admin

### Logout
- **File:** [cms/api/logout.php](cms/api/logout.php)
- **Features:**
  - Session destruction
  - Cookie clearing
  - Redirect to login
- **Access Level:** Authenticated
- **Status:** ✅ Working
- **Test:** Click logout, verify session cleared

### Session Persistence
- **File:** [cms/config.php](cms/config.php)
- **Features:**
  - 1-hour session timeout
  - HttpOnly cookies
  - SameSite=Lax
  - Secure cookies on HTTPS
  - Cross-browser compatibility
- **Access Level:** System
- **Status:** ✅ Working
- **Test:** Refresh page, session persists

---

## Dashboard (Main UI)

### Dashboard Shell
- **File:** [cms/index.php](cms/index.php)
- **Features:**
  - Tab navigation (Dashboard, Admin)
  - Global search
  - Theme toggle (light/dark)
  - User preferences loading
  - Card layout persistence
- **Access Level:** Authenticated users
- **Status:** ✅ Working
- **Test:** Load dashboard, verify tabs visible

### Customer Overview Card
- **File:** [cms/assets/app.js](cms/assets/app.js)
- **API:** [cms/api/get-customers.php](cms/api/get-customers.php)
- **Features:**
  - Total device count
  - Connector count
  - Alert count
  - Enabled devices
  - Modal with device breakdown
- **Access Level:** Authenticated users
- **Status:** ✅ Working
- **Test:** View customer overview card

### Connectors Card
- **API:** [cms/api/get-connectors.php](cms/api/get-connectors.php)
- **Features:**
  - Total connector count
  - Online/offline status
  - Active (24h) count
  - Connector details modal
- **Access Level:** Authenticated users
- **Status:** ✅ Working
- **Test:** View connectors card

### Devices Card
- **API:** [cms/api/get-devices.php](cms/api/get-devices.php), [cms/api/get-cached-devices.php](cms/api/get-cached-devices.php)
- **Features:**
  - Total device count
  - Offline device count
  - Model variety
  - Paginated device table
  - Equipment ID, Model, Serial, IP, Location, Status
  - Toner levels (K, C, M, Y)
- **Access Level:** Authenticated users
- **Status:** ✅ Working
- **Test:** View devices card, verify pagination

### Supply Alerts Card
- **API:** [cms/api/get-supply-alerts.php](cms/api/get-supply-alerts.php)
- **Features:**
  - Total alert count
  - Severity breakdown
  - Alert details table
  - Sortable columns
- **Access Level:** Authenticated users
- **Status:** ✅ Working
- **Test:** View supply alerts card

---

## Global Search

### Device Search
- **File:** [cms/assets/app.js](cms/assets/app.js)
- **API:** [cms/api/search-devices.php](cms/api/search-devices.php)
- **Features:**
  - Search by serial number
  - Search by asset number
  - Search by IP address
  - Search by model
  - Search by customer name
  - Dropdown with top 10 results
  - 60-second cache
  - Opens device modal on selection
- **Access Level:** Authenticated users
- **Status:** ✅ Working
- **Test:** Type device serial, verify dropdown

---

## Device Deep-Dive Modal

### Device Details View
- **API:** [cms/api/get-device-deep-dive.php](cms/api/get-device-deep-dive.php)
- **Features:**
  - Device information (Equipment ID, Serial, Model, Location, IP, Asset Number)
  - Meter readings (B&W, Color, Total, Date)
  - Supply levels (K, C, M, Y with color badges)
  - Supply alerts list
  - Device health indicators
  - Panel message history (last 100 messages)
  - Cached drilldown data fallback
- **Access Level:** Authenticated users
- **Status:** ✅ Working
- **Test:** Click device, verify modal opens with all sections

### Panel Message History (in Modal)
- **API:** Embedded in [cms/api/get-device-deep-dive.php](cms/api/get-device-deep-dive.php)
- **Features:**
  - Last 100 panel messages for device
  - Timestamp, alert code, configuration
  - Newest first sorting
- **Access Level:** Authenticated users
- **Status:** ✅ Working
- **Test:** Open device with panel messages

---

## Admin Tab

### System Health Card
- **API:** [cms/api/system-health.php](cms/api/system-health.php)
- **Features:**
  - Database status (connection, query time, table count)
  - MPS API status (connection, response time, OAuth)
  - Cache status (entries, fresh entries, size)
  - Session status (active, username)
  - Server metrics (PHP version, memory, disk, load)
- **Access Level:** Authenticated users (should be Admin only)
- **Status:** ✅ Working
- **Test:** Click Admin tab, view System Health

### Database Monitor Card
- **API:** [cms/api/get-database-monitor.php](cms/api/get-database-monitor.php)
- **Features:**
  - Total devices cached
  - Total drill-downs cached
  - Drill-down coverage percentage
  - Panel messages count
  - Missing drill-down sample (20 devices)
  - Refresh lock status
  - Sample rows from all tables
- **Access Level:** Authenticated users (should be Admin only)
- **Status:** ✅ Working
- **Test:** View Database Monitor card

### Visitor Analytics Card
- **API:** [cms/api/get-visitor-logs.php](cms/api/get-visitor-logs.php)
- **Features:**
  - Visitor log table (username, IP, URL, timestamp, user agent)
  - Pagination controls
  - Filter by username
  - Filter by IP
  - Date range filter
  - Export to CSV
- **Access Level:** Authenticated users (should be Admin only)
- **Status:** ✅ Working
- **Test:** View visitor logs, test filters

### Error Logs Card
- **API:** [cms/api/get-error-logs.php](cms/api/get-error-logs.php)
- **Features:**
  - PHP error log entries
  - Sorted by timestamp (newest first)
  - Error type (Warning, Error, Fatal)
  - Error message
  - File and line number
- **Access Level:** Authenticated users (should be Admin only)
- **Status:** ✅ Working
- **Test:** View error logs

### Endpoint Catalog Card
- **API:** [cms/api/get-endpoint-catalog.php](cms/api/get-endpoint-catalog.php)
- **Features:**
  - List of 544 MPS API endpoints
  - Search/filter endpoints
  - Endpoint parameters
  - Endpoint descriptions
  - Grouped by category
- **Access Level:** Authenticated users (should be Admin only)
- **Status:** ✅ Working
- **Test:** View endpoint catalog, test search

---

## Panel Message Monitoring

### Panel Message Monitor Page
- **File:** [cms/command-center.php?tab=panel](cms/command-center.php?tab=panel)
- **API:** [cms/api/get-panel-messages.php](cms/api/get-panel-messages.php)
- **Features:**
  - Panel message list table
  - Time window filter (1h, 3h, 6h, 12h, 24h, 48h, 72h, 1 week)
  - Display limit filter (10, 25, 50, 100, 200, 500)
  - Device serial search
  - Payload viewer modal (formatted JSON)
  - Auto-refresh every 30 seconds
  - Refresh counter
  - Tabs: Messages, Device CRUD (disabled), Payload Debugger
- **Access Level:** Authenticated users (should be Admin only)
- **Status:** ✅ Working
- **Test:** Visit /cms/command-center.php?tab=panel

---

## Payload Debugger

### Payload Debugger Page
- **File:** [cms/payload-debugger.php](cms/payload-debugger.php)
- **API:** [cms/api/get-payload-debug-logs.php](cms/api/get-payload-debug-logs.php)
- **Features:**
  - Debug log table (ID, timestamp, IP, method, status, message)
  - Status filter (All, SUCCESS, ERROR, PROCESSING)
  - Auto-refresh every 5 seconds
  - View details modal (headers, raw body, content-type, user-agent, HTTP code)
  - Statistics dashboard (total requests, success rate, error count, last request)
  - Unique source tracking
  - Forwarded IP tracking
- **Access Level:** Authenticated users (should be Admin only)
- **Status:** ✅ Working
- **Test:** Visit /cms/payload-debugger.php

---

## Device CRUD (Lifecycle Management)

### Device Lifecycle Page
- **File:** [cms/device-lifecycle.php](cms/device-lifecycle.php)
- **API:**
  - [cms/api/device-list.php](cms/api/device-list.php)
  - [cms/api/device-create.php](cms/api/device-create.php)
  - [cms/api/device-update.php](cms/api/device-update.php)
  - [cms/api/device-delete.php](cms/api/device-delete.php)
- **Features:**
  - Device inventory list
  - Create offline device
  - Update device
  - Delete device
  - Pagination
  - Search
  - Dealer scoping
  - Audit logging to cms/logs/device-crud-*.log
  - Cache invalidation on mutations
- **Access Level:** Admin only (requires permission system)
- **Status:** 🔴 DISABLED (FEATURE_DEVICE_CRUD = false)
- **Test:** Currently hidden behind feature flag

---

## Background Cache System

### Enhanced Cache Refresh
- **File:** [cms/api/refresh-cache-enhanced.php](cms/api/refresh-cache-enhanced.php)
- **Features:**
  - Device list caching (Device/List)
  - Deleted device caching (Device/Deleted/List)
  - Per-device drill-down caching (Device/Get)
  - Panel message coverage counting
  - Lock mechanism (prevents concurrent runs)
  - Force mode (?force=1)
  - Skip drill-down mode (?skipDrilldown=1)
  - Retry logic with exponential backoff
  - Rate limit handling (250ms delays)
  - Progress logging to cms/logs/cache-refresh-*.log
  - Stats reporting (devices cached, API calls, duration)
- **Access Level:** System (cron job)
- **Status:** ✅ Working
- **Test:** curl /cms/api/refresh-cache-enhanced.php

### Legacy Cache Refresh
- **Files:**
  - [cms/api/refresh-cache.php](cms/api/refresh-cache.php)
  - [cms/api/refresh-cache-v2.php](cms/api/refresh-cache-v2.php)
  - [cms/api/refresh-cache-cron.php](cms/api/refresh-cache-cron.php)
- **Status:** ⚠️ DEPRECATED (use enhanced version)

### Cache Clearing
- **File:** [cms/api/clear-cache.php](cms/api/clear-cache.php)
- **Features:**
  - Manual cache clearing
  - File-based cache cleanup
- **Access Level:** Authenticated users (should be Admin only)
- **Status:** ✅ Working
- **Test:** Call endpoint, verify cache cleared

---

## MPS API Integration (Engine Proxy)

### Query Endpoint
- **File:** [mps-api/index.php](mps-api/index.php)
- **Features:**
  - POST /mps-api/query
  - Action dispatcher (544 actions)
  - OAuth token management
  - Rate limiting
  - Retry logic
  - Response caching (ActionCache)
  - Error handling
- **Access Level:** CMS only (internal)
- **Status:** ✅ Working
- **Test:** CMS endpoints use this internally

### Health Check
- **Endpoint:** GET /mps-api/health
- **Features:**
  - Engine availability check
  - Returns healthy/degraded/unhealthy
- **Access Level:** Public (monitoring)
- **Status:** ✅ Working
- **Test:** curl /mps-api/health

### Diagnostics
- **Endpoint:** GET /mps-api/diagnostics
- **Features:**
  - Configuration validation
  - Filesystem checks
  - OAuth readiness
  - Request counters
  - Debug information
- **Access Level:** Public (should be restricted)
- **Status:** ✅ Working
- **Test:** curl /mps-api/diagnostics

### Available Endpoints
- **Endpoint:** GET /mps-api/endpoints
- **Features:**
  - List all 544 supported actions
- **Access Level:** Public
- **Status:** ✅ Working
- **Test:** curl /mps-api/endpoints

### Swagger Schema
- **Endpoint:** GET /mps-api/swagger.json
- **Features:**
  - Machine-readable API schema
  - ChatGPT Actions compatible
- **Access Level:** Public
- **Status:** ✅ Working
- **Test:** curl /mps-api/swagger.json

---

## Webhooks & Callbacks

### Panel Message Callback (Production)
- **File:** [mps-api/callbacks/panel-message.php](mps-api/callbacks/panel-message.php)
- **Features:**
  - Receives MPS Monitor webhooks
  - Validates shared secret (mpsm-panel-message-v1)
  - Stores in mpsm_panel_messages
  - Logs to mps-api/logs/panel-message-*.log
  - Returns {success: true, stored: true}
- **Access Level:** Vendor API (webhook)
- **Status:** ✅ Working
- **Test:** POST with valid payload

### Panel Message Callback (Debug)
- **File:** [mps-api/callbacks/panel-message-debug.php](mps-api/callbacks/panel-message-debug.php)
- **Features:**
  - Full request capture (headers, body, IP)
  - Stores in mpsm_panel_callback_debug
  - Tracks unique source fingerprint
  - Tracks forwarded IPs
  - Completed timestamp
  - Same validation as production
- **Access Level:** Vendor API (webhook)
- **Status:** ✅ Working
- **Test:** POST with valid payload, check debugger

---

## User Preferences

### Get Preferences
- **File:** [cms/api/get-preferences.php](cms/api/get-preferences.php)
- **Features:**
  - Fetch user preferences (theme, card layout)
- **Access Level:** Authenticated users
- **Status:** ✅ Working
- **Test:** Load dashboard, preferences applied

### Save Preferences
- **File:** [cms/api/save-preferences.php](cms/api/save-preferences.php)
- **Features:**
  - Save user preferences
  - Theme persistence
  - Card layout persistence
- **Access Level:** Authenticated users
- **Status:** ✅ Working
- **Test:** Change theme, refresh page

---

## Export & Reporting

### Export Endpoints
- **File:** [cms/api/get-export-endpoints.php](cms/api/get-export-endpoints.php)
- **Features:**
  - List available export actions
- **Access Level:** Authenticated users
- **Status:** ✅ Working

### Run Export
- **File:** [cms/api/run-export.php](cms/api/run-export.php)
- **Features:**
  - Execute export actions
  - CSV download
- **Access Level:** Authenticated users
- **Status:** ✅ Working
- **Test:** Export device list

---

## Database Tables

### User Tables
- `mpsm_users` - User accounts
- `mpsm_user_preferences` - User settings
- `mpsm_visitor_log` - Login/page visit tracking

### Device Cache Tables
- `mpsm_cache_devices` - Device list cache
- `mpsm_cache_device_drilldown` - Device detail cache

### Panel Message Tables
- `mpsm_panel_messages` - Production panel messages
- `mpsm_panel_callback_debug` - Debug webhook logs

### System Tables
- Created dynamically via `initializeTables()` in [cms/functions.php](cms/functions.php)

---

## Access Control Requirements (Post-Refactor)

### Current State
- **Authentication:** Session-based (logged_in flag)
- **Authorization:** NONE - all authenticated users see everything
- **Problem:** No role-based access control

### Required Access Levels

**Level 1: Viewer** (Read-Only)
- ✅ Dashboard tab
- ✅ View devices
- ✅ View device details
- ✅ Global search
- ❌ Admin tab (hidden)
- ❌ Panel message monitor
- ❌ Payload debugger
- ❌ Device CRUD
- ❌ Export data

**Level 2: Analyst** (Read + Export)
- ✅ All Viewer permissions
- ✅ Panel message monitor (read-only)
- ✅ Export data
- ❌ Admin tab
- ❌ Payload debugger
- ❌ Device CRUD

**Level 3: Admin** (Full Access)
- ✅ All Analyst permissions
- ✅ Admin tab
- ✅ Payload debugger
- ✅ System health
- ✅ Database monitor
- ✅ Visitor logs
- ✅ Error logs
- ✅ Endpoint catalog
- ❌ Device CRUD (requires Super Admin)

**Level 4: Super Admin** (Complete Control)
- ✅ All Admin permissions
- ✅ Device CRUD
- ✅ User management (future)
- ✅ Role management (future)

---

## Feature Count Summary

**Total Features:** 47 distinct features
**Working:** 46 features ✅
**Disabled:** 1 feature 🔴 (Device CRUD)
**Deprecated:** 3 features ⚠️ (Old cache refresh variants)

**API Endpoints:** 40 endpoints
**UI Pages:** 5 pages (login, dashboard, panel monitor, payload debugger, device lifecycle)
**Database Tables:** 6 tables
**Background Jobs:** 1 (cache refresh via cron)

---

## Post-Refactor Validation Checklist

### Must Maintain 100% Feature Parity

**Authentication & Session:**
- [ ] Login works
- [ ] Logout works
- [ ] Session persists
- [ ] Secure cookies
- [ ] Cross-browser compatible

**Dashboard:**
- [ ] All 4 cards load
- [ ] Customer overview
- [ ] Connectors
- [ ] Devices (with pagination)
- [ ] Supply alerts

**Search:**
- [ ] Global search works
- [ ] Search by serial
- [ ] Search by IP
- [ ] Search by model
- [ ] Opens device modal

**Device Modal:**
- [ ] Device info loads
- [ ] Meter readings display
- [ ] Supply levels show
- [ ] Supply alerts list
- [ ] Panel message history

**Admin Tab:**
- [ ] System health card
- [ ] Database monitor card
- [ ] Visitor analytics card
- [ ] Error logs card
- [ ] Endpoint catalog card

**Panel Monitoring:**
- [ ] Panel message list
- [ ] Time window filter
- [ ] Display limit filter
- [ ] Serial search
- [ ] Payload viewer
- [ ] Auto-refresh

**Payload Debugger:**
- [ ] Debug log table
- [ ] Status filter
- [ ] Auto-refresh
- [ ] Details modal
- [ ] Statistics dashboard

**Background System:**
- [ ] Cache refresh runs
- [ ] Devices cached
- [ ] Drill-downs cached
- [ ] Panel messages counted
- [ ] Lock prevents concurrent runs

**API Integration:**
- [ ] /mps-api/query works
- [ ] /mps-api/health works
- [ ] OAuth tokens refresh
- [ ] Rate limiting handled
- [ ] Retries on errors

**Webhooks:**
- [ ] Panel message callback works
- [ ] Debug callback works
- [ ] Secret validation
- [ ] Database storage

---

## New Features (Post-Refactor)

### Added During Refactor
- [ ] **Access Control:** Role-based permissions (Viewer, Analyst, Admin, Super Admin)
- [ ] **Multi-Tenancy:** Dealer/customer filtering
- [ ] **Job Queue:** Background workers replace cron
- [ ] **API Versioning:** /api/v1, /api/v2
- [ ] **Repository Pattern:** Data layer abstraction
- [ ] **Service Container:** Dependency injection
- [ ] **Multi-Level Caching:** Redis + Database
- [ ] **Health Checks:** Per-component health endpoints
- [ ] **Metrics Dashboard:** Component performance monitoring
- [ ] **Migration System:** Database version control
- [ ] **Test Suite:** Unit + integration tests

---

**Status:** Complete Feature Catalog
**Last Updated:** 2025-11-07
**Purpose:** Baseline for refactor validation - NO FEATURES SHALL BE LOST

