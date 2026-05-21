# CMS Layer Guide

> Files referenced: `cms/config.php`, `cms/functions.php`, `cms/index.php`, `cms/assets/`, `cms/api/`, `cms/command-center.php`, `cms/payload-debugger.php`

## Directory Layout

`
cms/
|- config.php                // runtime constants + session config
|- functions.php             // database + API helpers + caching
|- index.php                 // authenticated dashboard shell
|- login.html                // public login page
|- command-center.php // command center (panel stream)
|- device-lifecycle.php      // device CRUD workspace (feature flagged)
|- payload-debugger.php      // standalone payload debugger UI
|- assets/
|  |- app.js                 // main application bundle (~146 KB)
|  |- style.css              // dashboard styles
|  |- panel-messages.js      // command center JS
|  |- error-logs.js          // log viewer module
|  |- device-crud.js         // device lifecycle interactions
|  - device-crud.css        // device lifecycle layout
- api/                      // JSON endpoints
`

## Configuration & Sessions

- `config.php` defines database credentials, dealer defaults (`DEFAULT_DEALER_CODE = 'NY06AGDWUQ'`) and MPS API credentials.
- Session cookies default to 1 hour, `httponly`, `SameSite=Lax`, and `secure` when HTTPS is detected (`config.php` lines 52-83).
- `initializeTables()` in `functions.php` creates `mpsm_users`, `mpsm_user_preferences`, and `mpsm_visitor_log`, and seeds the default `admin/admin` user.

## Core Utilities (`functions.php`)

- `getDatabase()` — PDO singleton with exception mode enabled.
- `getMPSToken()` / `callMPSAPI()` — vendor OAuth helper (CMS still uses direct OAuth for legacy endpoints).
- `requireAuth()` / `loginUser()` / `trackVisit()` — session enforcement and analytics.
- `getSystemHealth()` — comprehensive system diagnostics consumed by the Admin tab.
- `cacheGet()`, `cacheStore()`, `cacheClear()`, `getCacheStats()` — file-based cache located under `cms/api/cache/`.
- All JSON responses funnel through `jsonSuccess()` / `jsonError()` to normalise headers and formatting.

## Frontend Shell (`index.php`)

- Requires auth, initializes tables, loads user preferences, then renders the SPA shell with `assets/app.js`.
- Tabs: **Dashboard** (cards + device modals) and **Admin** (system, logs, Catalog, user management).
- Header includes quick links: global search, panel monitor icon (`<i class="fas fa-satellite-dish">`), theme toggle, refresh, logout.

## JavaScript Highlights (`assets/app.js`)

- Maintains global `state` with dealer/customer context, device caches, alert summaries, and card order.
- Uses `CardManager.setContext()` guarded by `typeof CardManager !== 'undefined'` checks (fix shipped in `cms/assets/app.js` lines ~1256, 1432, 1742, 1769).
- Implements global device search with 1-minute cache TTL (ADR-0005).
- Device deep dive modal calls `api/get-device-deep-dive.php` to aggregate counters, supply alerts, SDS actions, and panel history; the endpoint now hydrates from `mpsm_cache_devices` / `mpsm_cache_device_drilldown` when the upstream `Device/Get` response is missing, ensuring cached fallback renders instantly.
- Admin tab surfaces system health (`api/system-health.php`), error logs (`api/get-error-logs.php`), visitor logs, endpoint catalog, and user settings.
- Admin cards are pre-loaded after login (system health, database monitor, visitor logs) so the System Monitoring section renders immediately; auto-refresh keeps them current.

## API Endpoint Groups (`cms/api/`)

| Category | Endpoints | Notes |
| -------- | --------- | ----- |
| Authentication | `login.php`, `logout.php` | `login.php` reads JSON body but falls back to `$_POST` for hosts that strip php://input (commit `2220dcd`). |
| Devices & Search | `get-devices.php`, `search-devices.php`, `get-device-deep-dive.php`, `get-device-panel-history.php` | All proxy through `mps-api/query`. Deep dive merges up to five upstream endpoints and the local panel message store. |
| Cache & Background | `refresh-cache.php`, `refresh-cache-v2.php`, `refresh-cache-enhanced.php`, `refresh-cache-chunked.php`, `get-cached-devices.php`, `clear-cache.php`, `cache-engine.js` | `refresh-cache-chunked.php` is the current live-safe path: it writes to staging tables and cuts over only after a successful run. |
| Panel Messages | `get-panel-messages.php`, `get-device-panel-history.php`, `get-payload-debug-logs.php` | Backed by `mpsm_panel_messages` and `mpsm_panel_callback_debug`. |
| Admin Tools | `get-error-logs.php`, `get-visitor-logs.php`, `get-endpoint-catalog.php`, `run-export.php`, `system-health.php`, `get-database-monitor.php`, `check-customers.php` | Powers the Admin area cards: system health, database monitor, visitor tracking, logs, and catalog tooling. |
| Legacy Support | `get-customers.php`, `get-devices.php` variations, `get-supply-alerts.php`, `get-deleted-devices.php` | Still rely on live API responses until cache is fully wired. |

All endpoints call `requireAuth()` and respond with `{success: bool, ...}` JSON for consistency with frontend helpers.

## Command Center Surfaces

- **Panel Message Monitor** (`command-center.php` + `assets/panel-messages.js`): filters by time window, displays message metadata, opens payload modal.
- **Payload Debugger** (`payload-debugger.php` + inline JS): auto-refresh UI that consumes `api/get-payload-debug-logs.php`, renders stats, payloads, headers, and a unique-source roll-up. Embedded as a tab inside `command-center.php` for same-screen diagnostics.
- **Device Lifecycle** (`device-lifecycle.php` + `assets/device-crud.js`): feature-flagged CRUD workspace reachable from the monitor. Proxies create/update/delete actions through `mps-api/query`, clears cached device inventories on mutation, and logs every change to `cms/logs/device-crud-YYYY-MM-DD.log` for auditing.

## Background Cache Flow

`cms/api/refresh-cache-chunked.php` is the current production cache path:

1. `action=start` creates staging tables and initializes a checkpoint.
2. `action=process` advances the job in bounded chunks so shared-host timeouts do not wipe the live cache.
3. Device list rows are staged before drilldowns are hydrated with `Device/Get`.
4. `action=status` reports the current phase, staged device count, staged drilldown count, checkpoint indexes, errors, and last activity.
5. `action=cutover` swaps staging rows into the live cache tables only after the staged run completes.
6. `cache-status-report.php` continues to report the live cache counts used by the Admin UI.

Last verified during the 2026-05-20 documentation pass: the live cache held 3,351 devices and 1,425 drilldowns, while the active staged chunked run held 3,370 devices, 300 drilldowns, and 0 errors at `2026-05-20 16:16:04`.

## Next Steps / TODOs

- Continue the staged `refresh-cache-chunked.php` run to completion and run `action=cutover` only after `status` reports completion with no errors.
- Add a lightweight alert (badge/toast) when coverage drops below 90% so operators can trigger the warm-up without digging through logs.
- Surface the cached timestamp inside the device modal once the cache stabilises, helping analysts judge data freshness.

## Authentication Failure Handling

- On API errors the frontend surfaces the JSON error message and the log viewer captures the exception.
- login failures log detail via `error_log()` with anonymised credential info (`cms/api/login.php` lines 36-51).
- Cross-origin requests from the CMS honour CORS whitelisting in `setSecurityHeaders()` (only same-origin + localhost).
