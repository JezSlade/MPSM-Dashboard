# CMS Layer Guide

> Files referenced: `cms/config.php`, `cms/functions.php`, `cms/index.php`, `cms/assets/`, `cms/api/`, `cms/panel-message-monitor.php`, `cms/payload-debugger.php`

## Directory Layout

```
cms/
â”œâ”€ config.php                // runtime constants + session config
â”œâ”€ functions.php             // database + API helpers + caching
â”œâ”€ index.php                 // authenticated dashboard shell
â”œâ”€ login.html                // public login page
â”œâ”€ panel-message-monitor.php // command center (panel stream)
â”œâ”€ payload-debugger.php      // standalone payload debugger UI
â”œâ”€ assets/
â”‚  â”œâ”€ app.js                 // main application bundle (~146â€¯KB)
â”‚  â”œâ”€ style.css              // dashboard styles
â”‚  â”œâ”€ panel-messages.js      // command center JS
â”‚  â”œâ”€ error-logs.js          // log viewer module
â”‚  â””â”€ js/
â”‚     â”œâ”€ card-manager.js     // card orchestration (CardManager.setContext)
â”‚     â”œâ”€ card-registry.js    // registry of all dashboard cards
â”‚     â””â”€ table-utils.js      // reusable table rendering helpers
â””â”€ api/                      // JSON endpoints
```

## Configuration & Sessions

- `config.php` defines database credentials, dealer defaults (`DEFAULT_DEALER_CODE = 'NY06AGDWUQ'`) and MPS API credentials.
- Session cookies default to 1 hour, `httponly`, `SameSite=Lax`, and `secure` when HTTPS is detected (`config.php` lines 52-83).
- `initializeTables()` in `functions.php` creates `mpsm_users`, `mpsm_user_preferences`, and `mpsm_visitor_log`, and seeds the default `admin/admin` user.

## Core Utilities (`functions.php`)

- `getDatabase()` â€” PDO singleton with exception mode enabled.
- `getMPSToken()` / `callMPSAPI()` â€” vendor OAuth helper (CMS still uses direct OAuth for legacy endpoints).
- `requireAuth()` / `loginUser()` / `trackVisit()` â€” session enforcement and analytics.
- `getSystemHealth()` â€” comprehensive system diagnostics consumed by the Admin tab.
- `cacheGet()`, `cacheStore()`, `cacheClear()`, `getCacheStats()` â€” file-based cache located under `cms/api/cache/`.
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
| Cache & Background | `refresh-cache.php`, `refresh-cache-v2.php`, `refresh-cache-enhanced.php`, `get-cached-devices.php`, `clear-cache.php`, `cache-engine.js` | Enhanced version writes to MySQL tables `mpsm_cache_devices` and `mpsm_cache_device_drilldown`. |
| Panel Messages | `get-panel-messages.php`, `get-device-panel-history.php`, `get-payload-debug-logs.php` | Backed by `mpsm_panel_messages` and `mpsm_panel_callback_debug`. |
| Admin Tools | `get-error-logs.php`, `get-visitor-logs.php`, `get-endpoint-catalog.php`, `run-export.php`, `system-health.php`, `get-database-monitor.php`, `check-customers.php` | Powers the Admin area cards: system health, database monitor, visitor tracking, logs, and catalog tooling. |
| Legacy Support | `get-customers.php`, `get-devices.php` variations, `get-supply-alerts.php`, `get-deleted-devices.php` | Still rely on live API responses until cache is fully wired. |

All endpoints call `requireAuth()` and respond with `{success: bool, ...}` JSON for consistency with frontend helpers.

## Command Center Surfaces

- **Panel Message Monitor** (`panel-message-monitor.php` + `assets/panel-messages.js`): filters by time window, displays message metadata, opens payload modal.
- **Payload Debugger** (`payload-debugger.php` + inline JS): auto-refresh UI that consumes `api/get-payload-debug-logs.php`, renders stats, payloads, headers, and a unique-source roll-up. Embedded as a tab inside `panel-message-monitor.php` for same-screen diagnostics.

## Background Cache Flow

cms/api/refresh-cache-enhanced.php orchestrates the background cache:

1. Locks to prevent concurrent runs (?force=1 overrides; ?skipDrilldown=1 runs a fast pass).
2. `fetchAllDevices()` pages through Device/List and Device/Deleted/ListByDealer using the same dealer parameters as get-cached-devices.php, ensuring full coverage.
3. `cacheDeviceList()` upserts payloads into `mpsm_cache_devices`.
4. Unless skipped, each device is hydrated with Device/Get (preferring device IDs when available) and retried with exponential back-off on rate-limit responses before being written to `mpsm_cache_device_drilldown`.
5. `cachePanelMessages()` tallies distinct devices with stored panel history.
6. Each run logs duration, API call counts, and drill-down coverage to `cms/logs/cache-refresh-YYYY-MM-DD.log`; the `get-database-monitor.php` endpoint exposes these metrics to the admin UI.

## Next Steps / TODOs

- Schedule a full `refresh-cache-enhanced.php?force=1` run (no `skipDrilldown`) during a quiet window, then confirm the Database Monitor shows ≥95 % drill-down coverage and capture the before/after counts.
- Add a lightweight alert (badge/toast) when coverage drops below 90 % so operators can trigger the warm-up without digging through logs.
- Surface the cached timestamp inside the device modal once the cache stabilises, helping analysts judge data freshness.

## Authentication Failure Handling

- On API errors the frontend surfaces the JSON error message and the log viewer captures the exception.
- login failures log detail via `error_log()` with anonymised credential info (`cms/api/login.php` lines 36-51).
- Cross-origin requests from the CMS honour CORS whitelisting in `setSecurityHeaders()` (only same-origin + localhost).

