# Project Next Steps / TODOs

Updated: 2025-11-06 13:50 UTC

## Data Refresh & Coverage

- Allow a full `cms/api/refresh-cache-enhanced.php?force=1` run to complete (without `skipDrilldown`) and confirm the Database Monitor card reports ≥95 % drill-down coverage; capture the before/after metrics in `BACKGROUND_REFRESH_SYSTEM.md`.
- Watch `cms/logs/cache-refresh-YYYY-MM-DD.log` for sustained rate-limit messages; if retries still hit the cap, consider increasing the base back-off above 0.75 s or adding a staggered queue.
- Make sure `refresh-cache-chunked.php` cron emails show `"version": "2025-11-19a"` and `errors: []` before returning to the drill-down phase; if the OAuth token timeout survives, add retries/backoff and silence the repeated completed-state output as the follow-up fix.
- Monitor `/home/resolut7/logs/refresh-cache-chunked.log` instead of emails; each entry should include the current `version`, `state`, and `errors`. If nothing is written, hit `run-refresh-cache-chunked.php?secret=RUN_REFRESH_2025` to regenerate the latest run output before investigating further.
- Confirm drill-down progress after restarting the job. Once you reinitialize via `?action=start`, check that the log/helper shows `state.status = "fetching_drilldowns"` and `drilldowns_cached` begins rising; if needed rerun the helper or wait a minute before rerunning to ensure the queue processes and that log entries capture both device and drill-down phases.
- Surface the cached timestamp inside the device modal once the cache is steady so analysts know how fresh each drill-down snapshot is.

## Payload Debugger & Callbacks

- Clean up `test-payloads.ps1` (remove non-ASCII quotes) so the eight-case harness runs without parse errors; rerun and verify the debugger reflects the expected mix (2 success / 6 error).
- Monitor `mpsm_panel_callback_debug` for live MPS Monitor traffic to validate `unique_source`, `forwarded_for`, and `completed_at` data; record any production IP ranges for future allow-listing.
- After the next vendor callback, grab a debugger screenshot highlighting the “Completed” column for support documentation (`PAYLOAD_DEBUGGER_GUIDE.md`).

## Admin & Monitoring UX

- Add a light-weight alert on the Admin Database Monitor card whenever drill-down coverage drops below 90 % so on-call staff can launch a warm-up immediately.
- Document the new sample tables (device cache, drill-down cache, panel messages, payload debugger) in the runbook so new engineers know how to interpret them.
- Keep an eye on `panel-message-monitor.php` iframe behaviour; if browsers reintroduce frame restrictions, move the debugger into an in-page tab instead of using an `<iframe>`.

## Maybe Later

- Stand up the async worker fleet (Redis/Rabbit-backed queues for `cache.refresh.fast`, `cache.refresh.deep`, `api.prefetch`, `webhook.enrich`, `alerts.evaluate`, and `logs.rollup`) to offload heavy cache hydration, enrichment, and alert evaluation from request/response code paths while keeping ActionCache/MySQL fresh.
- Introduce an OOP permission core built around `PermissionsServiceInterface`, `RoleRepositoryInterface`, and `UserRepositoryInterface`, keeping each class <30 lines while enforcing SOLID boundaries between value objects (`Permission`, `Role`, `UserPermissions`) and persistence.
- Layer a reusable `PolicyMiddleware`/controller adapter that decorates every endpoint with `withPermission(...)` guards, so enforcing `device.manage`, `system.admin`, etc. becomes declarative and centrally audited.
- Ship an Access Control admin panel (UI + API) that lets `system.admin` users create roles, wire inheritance, and assign users, while the client reads `/api/me/permissions` and gates buttons/tabs through a tiny `PermissionsClient`.
- Deliver schema migrations + smoke tests for the role/permission tables, a post-migration admin-rotation script, and minimal PHPUnit coverage for the `DatabasePermissionsService` resolution logic.
- Externalize CMS configuration secrets into environment variables (mirroring the `mps-api` loader), fail fast when values are missing, and rotate the currently committed credentials.
- Split `cms/functions.php` into focused modules (`auth.php`, `database.php`, `cache.php`, `mps_api.php`, etc.) to reduce coupling and improve testability.
- Enforce `SESSION_TIMEOUT` inside `requireAuth()` (idle session invalidation, CSRF hardening) and rotate session IDs more aggressively on privilege changes.
- Replace the web-accessible JSON cache with storage outside the document root or a PHP proxy, making caching optional/feature-flagged for production.
- Modularize the front-end (break up the 4k-line `app.js` and 500-line `device-crud.js` into ES modules with a lightweight bundler) to ease incremental refactors and testing.
- Align CMS API calls with the local `mps-api` engine by pulling the query base URL from config so developers can run everything offline.
- Convert `initializeTables()` into a migration/install script instead of executing DDL on every request, and remove the auto-created `admin/admin` user once setup completes.
- Prune archival/dead files (old docs, `.NEW` duplicates, unused assets) and update the documentation set so it matches the current architecture and standards.
