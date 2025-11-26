# Diagnostics & Tooling

> Pulling from: `PAYLOAD_DEBUGGER_GUIDE.md`, `AUDIT_REPORT.md`, `docs/PAIN_POINTS.md`, `scripts/README.md`, `battle_test.html`, `scripts/panel-message-diagnostics.ps1`

## Built-In Dashboards

- **System Health (Admin Tab)**  
  Endpoint: `cms/api/system-health.php` via `getSystemHealth()`.  
  Summarises DB connectivity, API latency, cache stats, server metrics, and session status. Auto-refreshes every 60 s while the card is visible.

- **Panel Message Monitor**  
  Route: `/cms/command-center.php?tab=panel`.  
  Auto-refresh: 30 s. Supports hour/limit filters and exposes raw payload JSON in a modal.

- **Database Monitor**  
  Route: Admin → System Monitoring (card).  
  Backed by `cms/api/get-database-monitor.php`. Shows drill-down coverage %, per-table min/max timestamps, refresh lock status, and provides expandable sample tables (latest cache devices, drill-down payloads, panel messages, payload debugger requests). Includes quick actions for cache warm-up (`skipDrilldown=1`) and full refresh (`force=1`) with rate-limit feedback.

- **Payload Debugger**  
  Route: `/cms/payload-debugger.php`.  
  Auto-refresh: 5 s (toggleable). Animation-free UI with totals, success/error counts, per-request headers/bodies, and a “Unique Sources” roll-up (IP / forwarded-for / user-agent). Powered by `cms/api/get-payload-debug-logs.php`, now returning `unique_source`, `forwarded_for`, `completed_at`, and aggregated source statistics.

## CLI & Scripted Tools

- **Battle Test Harness** (`battle_test.html`, `battle_test_results.txt`)  
  Launch in a browser for end-to-end smoke checks (login, devices, deep dive, panel messages, payload debugger, exports). Documented in `AUDIT_REPORT.md`.

- **Panel Message Diagnostics** (`scripts/panel-message-diagnostics.ps1`)  
  ```powershell
  .\scripts\panel-message-diagnostics.ps1 -Limit 10
  ```
  Emits recent panel message summaries, counts, and sample payload details.

- **Payload Debugger Stress Test** (`test-payloads.ps1`)  
  Sends curated success/error requests to `panel-message-debug.php`. Expect totals to align with `PAYLOAD_DEBUGGER_GUIDE.md` cases.

- **API Discovery Suite** (`scripts/run_discovery.py`, `generate_endpoint_sample_catalog.py`, etc.)  
  Maintains `.canonical/EndpointCatalog.php`. Requires Python 3.7+ and a populated `.env`. Preferred for large-scale API validation.

## Logs & Where to Find Them

| Log | Location | Purpose |
| --- | -------- | ------- |
| CMS PHP errors | `cms/logs/php_errors.log` | Runtime exceptions, warnings. |
| Visitor log exports | `cms/api/get-visitor-logs.php` | Query via Admin → Visitor tracking. |
| Cache refresh runs | `cms/logs/cache-refresh-YYYY-MM-DD.log` | Start/end timestamps, progress counters, errors (now includes rate-limit messages). |
| Panel message summaries | `mps-api/logs/panel-message-YYYY-MM-DD.log` | Lightweight JSON lines with time, customer, serial, alert code. |
| Panel callback debug | `mpsm_panel_callback_debug` table | Full HTTP payloads, headers, status, unique source fingerprint. |
| Engine errors | `mps-api/logs/php_errors_YYYY-MM-DD.log` | OAuth failures, upstream errors. |
| Config issues | `mps-api/logs/config_error_YYYY-MM-DD.log` | `.env` parsing problems. |

Use `tail -f` equivalents (PowerShell: `Get-Content -Wait`) for live monitoring during deployments.

## Quick Command Library

```bash
# Login and store session cookie
curl -s -X POST "https://mpsm.resolutionsbydesign.us/cms/api/login.php" \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin"}' -c cookies.txt

# Fetch dashboard devices
curl -s "https://mpsm.resolutionsbydesign.us/cms/api/get-devices.php" \
  -b cookies.txt | jq '.meta'

# Get deep dive for a device
curl -s "https://mpsm.resolutionsbydesign.us/cms/api/get-device-deep-dive.php?serialNumber=TEST" \
  -b cookies.txt | jq '{panelHistory}'

# Fetch payload debugger stats
curl -s "https://mpsm.resolutionsbydesign.us/cms/api/get-payload-debug-logs.php?limit=20" \
  -b cookies.txt | jq '.stats'

# Run enhanced cache refresh (manual)
curl -s "https://mpsm.resolutionsbydesign.us/cms/api/refresh-cache-enhanced.php"

# Engine health diagnostics
curl -s "https://mpsm.resolutionsbydesign.us/mps-api/diagnostics" | jq '.status'
```

## Browser Tooling

- **Chrome Dev Overlay Extension** (`dev-overlay-extension/`)  
  Provides HUD overlays for network calls, console logs, DOM changes, storage writes, and navigation events. Install via `chrome://extensions` → “Load unpacked”. Useful for live debugging without instrumenting the app.

- **Network Debugging Tips (from `docs/PAIN_POINTS.md`)**  
  - Firefox may block session cookies without `SameSite=Lax`; already handled in `cms/config.php`.  
  - If JSON downloads look malformed, check for truncation; `python -c` is preferred over `python -m json.tool` when piping.

## When Something Breaks

1. **Reproduce:** Capture request URL, payload, and console logs.  
2. **Check Known Fixes:** See `context/verified-fixes.md` to ensure the issue was not previously resolved.  
3. **Look at Logs/Tables:** `mpsm_panel_callback_debug`, PHP error logs, cache refresh logs.  
4. **Run Targeted Script:** e.g. `panel-message-diagnostics.ps1` for panel issues, `test-payloads.ps1` for callback validation.  
5. **Compare With Battle Test:** If a scenario fails, inspect the referenced files in `AUDIT_REPORT.md`.

Keep this playbook updated whenever new tooling or diagnostics are added.

## Next Steps / TODOs

- Allow the enhanced cache refresh to complete a full run (without `skipDrilldown`) and confirm the Database Monitor card reports ≥95 % drill-down coverage; record the before/after stats in `BACKGROUND_REFRESH_SYSTEM.md`.
- Refactor `test-payloads.ps1` to remove non-ASCII characters so it can be executed without parse errors, then check the payload debugger to ensure the expected 2 success / 6 error mix is recorded.
- After the next live payload arrives from MPS Monitor, capture a screenshot of the debugger showing the populated `Completed` column for support documentation (`PAYLOAD_DEBUGGER_GUIDE.md`).

