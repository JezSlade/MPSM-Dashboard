# mps-api Layer Guide

> Files referenced: `mps-api/index.php`, `mps-api/engine.php`, `mps-api/config.php`, `mps-api/cache/ActionCache.php`, `mps-api/callbacks/panel-message.php`, `mps-api/callbacks/panel-message-debug.php`, `.canonical/EndpointCatalog.php`

## Purpose

`/mps-api` is the hardened proxy between the CMS and the vendor API. It handles configuration, authentication, rate limiting, caching, diagnostics, and webhook intake without exposing credentials to the browser.

## Entry Points (`index.php`)

| Route | Method | Description |
| ----- | ------ | ----------- |
| `/` | GET | Diagnostics landing page with timestamps, PHP environment, configuration status. |
| `/health` | GET | Returns `{status: healthy|degraded|unhealthy}` based on engine availability. |
| `/diagnostics` | GET | Extended report with filesystem checks, config validation, OAuth readiness, request counters. |
| `/endpoints` | GET | Lists all supported actions derived from `EndpointCatalog`. |
| `/swagger.json` | GET | Machine-readable schema for ChatGPT Actions and tooling. |
| `/query` | POST | Primary action dispatcher: expects JSON `{action: string, params: object}`. |

Every request is wrapped with shared error handling; debug mode (`MPS_DEBUG=true`) surfaces stack traces.

## Configuration Loading (`config.php`)

- Looks for `.env` in priority order: `MPS_ENGINE_ENV_PATH`, project root `.env`, parent directories, then local copy.
- Enforces exclusive read locking during load (`flock`).
- Validates required keys: `MPS_BASE_URL`, OAuth credentials (or API key), timeout ranges, retry limits.
- Logs configuration errors to `mps-api/logs/config_error_YYYY-MM-DD.log`.
- Returns a normalised array consumed by `engine.php`.

### Required `.env` Keys (examples from repo root `.env`)

```
MPS_BASE_URL="https://api.abassetmanagement.com/api3/"
TOKEN_URL="https://api.abassetmanagement.com/api3/token"
CLIENT_ID="G0bYZyS9bjOjx6oRv-MQ6vGF3VkVTvZy5hzhVEOWQs8"
CLIENT_SECRET="wFFXo9TQvvuCGVBb0_MMNZkZP5YuTPJqe_eRRdHCPQo"
USERNAME="rbd.connect@resolutionsbydesign.com"
PASSWORD="connect.RBD24!"
SCOPE="rbd.connect@resolutionsbydesign.com MpsMonitorApiAll"
DEALER_CODE="NY06AGDWUQ"
DEALER_ID="SZ13qRwU5GtFLj0i_CbEgQ2"
```

## Engine (`engine.php`)

- Singleton `MPSMonitorEngine` caches configuration, action registry, and domain seeds.
- Handles OAuth password grant (stores token + expiry), request retries, throttling, and action dispatch.
- Integrates with `SwaggerActionRegistry` to map action names to HTTP verbs, paths, and payload templates.
- Uses `ActionCache` (filesystem cache) when configured to reduce vendor load.
- Provides helpers for diagnostics (`healthCheck()`), seed extraction (`DomainSeeder`), and payload templates (`payload_templates.php`).

## Callback Endpoints

### `callbacks/panel-message-common.php`

- Shared helper loaded by both webhook endpoints.
- Ensures table definitions exist (adding `unique_source`, `forwarded_for`, `completed_at`, and expanding the payload column when required).
- Provides `createPanelCallbackDebugLog()` / `updatePanelCallbackDebugLog()` so every callback is captured with consistent metadata inside `mpsm_panel_callback_debug`.

### `callbacks/panel-message.php`

- Production webhook storing messages in `mpsm_panel_messages`.
- Validates HTTP method, content type, shared secret (`mpsm-panel-message-v1`), and JSON body.
- Logs summary lines to `mps-api/logs/panel-message-YYYY-MM-DD.log`.
- Uses shared CMS functions for DB access (`require_once ../cms/config.php`, `../cms/functions.php`).
- Invokes the shared logger so even successful production callbacks appear inside the payload debugger timeline with their unique source fingerprint.

### `callbacks/panel-message-debug.php`

- Diagnostic variant with full request capture.
- Immediately creates a row in `mpsm_panel_callback_debug` (`createPanelCallbackDebugLog()`), capturing headers, IP, method, `unique_source`, and any proxy forwarding chain.
- Updates status (PROCESSING → SUCCESS/ERROR) and stores raw body + HTTP code regardless of outcome.
- Re-uses `ensurePanelMessageTable()` to persist valid messages side-by-side with production endpoint.
- Response contracts documented in `PAYLOAD_DEBUGGER_GUIDE.md`.

## Logging & Storage

- `mps-api/logs/` contains PHP error logs, callback summaries, config load errors, and cache worker traces.
- Cache storage default path: `mps-api/cache/storage/` (JSON files). `ActionCache::init()` configures TTLs and cleaning thresholds via `mps-api/cache/config.php`.
- `dashboard.html` and helper Python scripts (`verify_fixes.py`, `verify_canonical_integration.py`) exist for QA/regression testing.

## Endpoint Catalog

- Canonical list of 544 actions maintained in `.canonical/EndpointCatalog.php` and `.canonical/endpoint_catalog.json`.
- The discovery scripts under `/scripts` generate/update this catalog by probing the official Swagger (`.canonical/Swagger.json`).
- `/mps-api/endpoints` route reads directly from the catalog to report available operations.

## Security Considerations

- No browser access: only the CMS (authenticated via PHP sessions) should call `/query`.
- Shared constants with CMS are limited to DB credentials and dealer defaults; OAuth secrets remain server-side.
- Callbacks validate shared secrets and never trust headers or client-provided IPs without logging.
- File permissions and writability are checked inside diagnostics to satisfy shared hosting constraints.

## Known Workflows

- **API Dispatch:** CMS posts to `/query`; engine authenticates, calls vendor API, returns JSON with `success`, `data`, `meta`.
- **Cache Warmup:** CMS uses `/query` to populate its own database caches (enhanced refresh script).
- **Diagnostics:** Devs can hit `/diagnostics` (authenticated if desired) to confirm environment health before debugging field issues.

## Next Steps / TODOs

- Monitor `mpsm_panel_callback_debug` as real MPS Monitor traffic arrives to ensure `unique_source`, `forwarded_for`, and `completed_at` continue to populate; capture any production IP ranges for future allow-listing.
- Once the payload harness (`test-payloads.ps1`) is cleaned up for plain-ASCII output, re-run the full success/error matrix and document results in `PAYLOAD_DEBUGGER_GUIDE.md`.
- Keep an eye on `mps-api/logs/panel-message-YYYY-MM-DD.log` for error spikes; if repeated `Database error` messages appear, capture the stack trace and extend `panel-message-common.php` with additional defensive logging.
