# Data & Workflow Flows

> Verified against: `cms/api/*.php`, `cms/assets/app.js`, `cms/assets/panel-messages.js`, `cms/api/refresh-cache-enhanced.php`, `mps-api/callbacks/*.php`

## 1. Login ➝ Dashboard Load

1. **Login Form** (`cms/login.html`) posts JSON `{username, password}` to `cms/api/login.php`.
2. `login.php` extracts credentials from php://input, `$_POST`, or raw body, then checks `mpsm_users` via `loginUser()`.
3. On success: sets session, logs visit (`trackVisit('/api/login.php')`), returns `{success:true}`.
4. Browser loads `cms/index.php`, which:
   - Calls `initializeTables()` (creating tables on the first run).
   - Pulls user preferences via `getUserPreferences()`.
   - Emits the SPA shell with theme attribute set from preferences.
5. `assets/app.js` initialises state, reads card layout from `localStorage`, and calls `bootstrap()` to load customers and device summaries.

## 2. Device Deep Dive Modal

1. User selects a device (card, search result, supply alert).
2. Frontend calls `GET /cms/api/get-device-deep-dive.php` with `deviceId`, `serialNumber`, and optionally `customerCode`.
3. Endpoint issues sequential `mps-api/query` calls for:
   - `Device/List` (confirm existence).
   - `Counter/ListDetailed` (meter readings).
   - `SdsAction/GetDeviceActions` (health/actions).
   - `SupplyAlert/List` (alerts).
4. Pulls local panel history from `mpsm_panel_messages` (up to 100 rows, newest first).
5. Responds with aggregated JSON: `{device, counterDetails, deviceHealth, supplyAlerts, panelHistory, errors}`.
6. Modal renders metrics, charts, and panel history (future UI work will visualise the `panelHistory.messages` array).

## 3. Global Device Search

1. Typing into global search triggers debounced `fetchAllDevicesForSearch()` in `assets/app.js`.
2. First run fetches up to 50 pages from `get-devices.php?pageRows=200&pageNumber=N&allCustomers=true`.
3. Results cached for 60 seconds in `globalSearchCache`.
4. Subsequent searches filter the cached array client-side and show results instantly.

## 4. Background Cache Refresh

Refer to `BACKGROUND_REFRESH_SYSTEM.md` and `cms/api/refresh-cache-enhanced.php`.

1. Invocation (manual `curl` or scheduled task) hits `refresh-cache-enhanced.php`.
2. Script obtains lock, ensures cache tables (`ensureCacheTables()`), and begins pagination.
3. Each device entry is upserted into `mpsm_cache_devices`.
4. Per-device deep dives are cached into `mpsm_cache_device_drilldown` with flags (`has_alerts`, `has_supplies`).
5. Progress logged every 50 devices; rate limit enforced with a 50 ms `usleep`.
6. Panel message coverage counted via `cachePanelMessages()`.
7. Final stats returned as JSON and log file appended.

## 5. Panel Message Pipeline

**Production Path**

1. Vendor posts to `POST /mps-api/callbacks/panel-message.php` with JSON payload and secret.
2. Endpoint validates method, content type, and `callbackSecret`.
3. Persists payload into `mpsm_panel_messages`, writes summary log line, responds `{success:true, stored:true}`.

**Debug Path**

1. Vendor (or test script) posts to `POST /mps-api/callbacks/panel-message-debug.php`.
2. `createDebugLog()` inserts PROCESSING row into `mpsm_panel_callback_debug` with headers, method, IP.
3. Same validation + storage as production path.
4. `updateDebugLog()` records status, HTTP code, message, and raw body (truncated at 65 000 chars).
5. Response identical to production; UI now exposes full trace via payload debugger.

**Monitoring**

1. `cms/panel-message-monitor.php` polls `GET /cms/api/get-panel-messages.php`.
2. Query supports `limit` (1-500) and `hours` (1-168) filters.
3. `assets/panel-messages.js` renders table, updates “Last refresh” badge, and modal payload viewer.
4. Future work: add tab/iframe to surface payload debugger within the command center.

## 6. Payload Debugger UI

1. Browser loads `/cms/payload-debugger.php`; page enforces `requireAuth()`.
2. JavaScript fetches `GET /cms/api/get-payload-debug-logs.php?limit=n&status=...` every 5 seconds by default.
3. API ensures `mpsm_panel_callback_debug` exists, returns logs with parsed headers and bodies alongside aggregate stats.
4. UI supports filtering by status, toggling auto-refresh, expanding payloads, and inspecting headers.
5. Run `test-payloads.ps1` from repo root to populate sample SUCCESS/ERROR events (documented in `PAYLOAD_DEBUGGER_GUIDE.md`).

## 7. System Health & Diagnostics

1. Admin tab calls `GET /cms/api/system-health.php` ➝ `getSystemHealth()` (CMS) to gather DB, API, cache, session, and server metrics.
2. System health card displays response times, counts, and server stats (memory, disk, load, uptime).
3. For backend diagnostics, hit `/mps-api/health` or `/mps-api/diagnostics`. The latter reports configuration status, file permissions, and token readiness.
4. `battle_test.html` runs through a curated list of checks: login, device listing, deep dive, panel history, payload debugger, exports, etc. (see `AUDIT_REPORT.md` lines referencing the battle test).

## 8. Visitor Analytics

1. Every authenticated request calls `trackVisit($pageUrl)` in `functions.php`.
2. Visits stored in `mpsm_visitor_log` with user id, IP, user agent, and timestamp.
3. Admin tab pulls data via `cms/api/get-visitor-logs.php`, with pagination, filtering, and CSV export.

## 9. Deployment Loop

1. Code pushes to `main` trigger GitHub Actions deployment (workflow defined in `.github/workflows/deploy.yml`).
2. Numerous PowerShell deployment scripts (`deploy-*.ps1`) exist for manual FTP pushes; they set up credentials and copy changed files.
3. Production often caches assets aggressively; after deployment, hard refresh (`Ctrl+Shift+R`) or cache-busting query parameters may be needed for `app.js`.

## 10. Device Lifecycle CRUD

1. From the panel message monitor, the `Device Lifecycle` tab loads `device-lifecycle.php` inside an iframe when the feature flag is enabled.
2. The workspace fetches inventories via `cms/api/device-list.php`, which proxies `Device/List` through `mps-api/query` with pagination, search, and dealer scoping.
3. Creating devices submits to `cms/api/device-create.php`, wrapping the vendor `Device/Offline/Create` payload. Successful operations clear `all-devices-dealer-*` cache keys and append audit rows to `cms/logs/device-crud-YYYY-MM-DD.log`.
4. Updates flow through `cms/api/device-update.php` (calling `Device/Update`), while deletions use `cms/api/device-delete.php` (calling `Device/Delete`). Each call validates the session, enforces the feature flag, and surfaces upstream errors to the UI.
5. Every mutation response triggers a UI refresh and toast notification; operators can immediately confirm changes without waiting for the background cache job.

## 11. Error Handling & Logging

- CMS: errors returned via `jsonError()` (includes HTTP code). `get-error-logs.php` reads from `cms/logs/php_errors.log`.
- mps-api: `sendResponse()` attaches `error_code` and optionally stack traces (when `MPS_DEBUG=true`).
- Panel messages: debug table surfaces HTTP status + message; daily log file summarises each request.
- Background refresh: check `cms/logs/cache-refresh-YYYY-MM-DD.log` for run history and errors.
