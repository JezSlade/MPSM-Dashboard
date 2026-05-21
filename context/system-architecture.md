# System Architecture

> Source of record: `docs/adr/0001-cms-api-separation.md`, `cms/functions.php`, `mps-api/index.php`, `cms/api/refresh-cache-chunked.php`, `context/current-state.md`.

## Layered Design

```
Browser (Vanilla JS)
   ⇅ HTTPS fetch
CMS PHP (`/cms`)
   ⇅ HTTPS POST
mps-api Engine (`/mps-api`)
   ⇅ HTTPS (OAuth Password Grant)
MPS Monitor Cloud API
```

### Browser Layer

- Served from `cms/index.php` and `cms/login.html`.
- Main application bundle lives in `cms/assets/app.js`; card rendering helpers reside in `cms/assets/js/card-manager.js`, `card-registry.js`, and `table-utils.js`.
- Makes authenticated `fetch()` calls to PHP endpoints under `cms/api/` (`get-devices.php`, `get-device-deep-dive.php`, `get-panel-messages.php`, etc.).
- Stores user preferences and card layout through `savePreference()` (see `cms/assets/app.js` around the `persistCardLayout()` helper).

### CMS Layer (`/cms`)

- **Configuration:** `cms/config.php` centralises DB connection, session security, default dealer/customer codes, and API constants.
- **Utilities:** `cms/functions.php` provides PDO access (`getDatabase()`), OAuth token handling (`getMPSToken()`), caching helpers (`cacheStore()`), JSON helpers (`jsonSuccess()` / `jsonError()`), and table bootstrap (`initializeTables()`).
- **Endpoints:** `cms/api/*.php` handle UI-specific logic, including caching, background refresh, and panel history aggregation. Every endpoint starts by `requireAuth()`, enforcing session login.
- **Command Center:** `cms/panel-message-monitor.php` wraps the panel monitor UI; `cms/payload-debugger.php` is the standalone payload debugger awaiting integration.
- **Background Jobs:** `cms/api/refresh-cache-chunked.php` stages device and drill-down cache updates and performs guarded cutover into `mpsm_cache_devices` and `mpsm_cache_device_drilldown`.

### mps-api Layer (`/mps-api`)

- **Router:** `mps-api/index.php` dispatches `/query`, `/health`, `/diagnostics`, `/endpoints`, and `/swagger.json`.
- **Engine:** `mps-api/engine.php` loads configuration (`mps-api/config.php`), manages OAuth tokens, rate limiting, retries, and action dispatch via `SwaggerActionRegistry`.
- **Caching:** `mps-api/cache/ActionCache.php` provides filesystem caching for upstream responses; defaults configured in `mps-api/cache/config.php`.
- **Callbacks:** `mps-api/callbacks/panel-message.php` (production) and `panel-message-debug.php` (full logging) persist payloads into the CMS database via shared config and functions.
- **Canonical Data:** `.canonical/EndpointCatalog.php` and `endpoint_catalog.json` contain the discovered 544 MPS Monitor endpoints.

### Upstream Vendor API

- Hosted at `https://api.abassetmanagement.com/api3/`.
- Accessed exclusively by `mps-api` with credentials from the root `.env` (mirrored inside `cms/config.php` for historical reasons).
- Password grant flow handled by `getMPSToken()` in `cms/functions.php` when CMS proxies requests directly, and by `MPSMonitorEngine::authenticate()` inside `mps-api/engine.php` for general use.

## Request Flows

### Standard Device Fetch

1. Browser calls `GET /cms/api/get-devices.php`.
2. PHP endpoint validates session (`requireAuth()`), composes `Device/List` payload.
3. PHP performs `POST https://mpsm.resolutionsbydesign.us/mps-api/query` with JSON `{action:"Device/List", params:{...}}`.
4. `mps-api/index.php` delegates to `MPSMonitorEngine::dispatchAction()`, which fetches/refreshes OAuth tokens and contacts the vendor API.
5. Response travels back through `mps-api` ➝ `cms/api/get-devices.php`, which normalises `TotalCount` metadata and returns JSON to the browser.

### Background Cache Refresh

1. Cron or manual `GET /cms/api/refresh-cache-chunked.php?action=start` creates staging tables.
2. Repeated `action=process` calls fetch `Device/List` pages into `mpsm_cache_devices_staging`.
3. The same process stage then fetches `Device/Get` drill-downs into `mpsm_cache_device_drilldown_staging`.
4. `action=status` reports current state and live/staging counts.
5. `action=cutover` swaps staging into live tables only after the staged tables are populated and verified.

### Panel Message Lifecycle

1. MPS Monitor emits webhook to `POST /mps-api/callbacks/panel-message.php` (or `panel-message-debug.php` when diagnosing).
2. Callback validates shared secret (`mpsm-panel-message-v1`), auto-creates `mpsm_panel_messages`, stores payload JSON, and logs summary to `mps-api/logs/panel-message-YYYY-MM-DD.log`.
3. CMS exposes data via `GET /cms/api/get-panel-messages.php` and via the `panelHistory` block in `get-device-deep-dive.php`.
4. `cms/panel-message-monitor.php` + `cms/assets/panel-messages.js` poll every 30 seconds to surface new messages.
5. Debug variant additionally writes to `mpsm_panel_callback_debug` for full request trace; UI available at `/cms/payload-debugger.php`.

## Authentication & Sessions

- Login form (`cms/login.html`) posts JSON credentials to `cms/api/login.php`.
- Endpoint attempts multiple input sources (php://input, `$_POST`, raw body) to work around hosting limits (see commit `2220dcd`).
- Successful login sets `$_SESSION['logged_in']`, `$_SESSION['user_id']`, plus metadata; cookies use secure/SameSite Lax settings (`cms/config.php` lines 52-83).
- `requireAuth()` enforces presence of `$_SESSION['logged_in']` for every API and page load except the login page.

## Error Reporting

- CMS API functions call `jsonError()` to emit structured JSON with HTTP status codes and set security headers (`cms/functions.php`).
- mps-api uses `sendResponse()` wrappers in `index.php`, returning `success=false` with `error_code` for upstream failures.
- Panel callback errors logged both in database (`mpsm_panel_callback_debug`) and filesystem logs.
