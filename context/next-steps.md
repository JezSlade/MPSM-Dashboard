# Project Next Steps / TODOs

Updated: 2026-05-20

## Data Refresh & Coverage

- Continue the current `cms/api/refresh-cache-chunked.php` staged run until `action=status` reports completion with no errors, then run `action=cutover`.
- Current checkpoint from the 2026-05-20 documentation pass: 3,351 live devices, 1,425 live drilldowns, 3,370 staged devices, 300 staged drilldowns, 0 staged errors.
- Do not use `refresh-cache-enhanced.php` as the full shared-host warmup path unless timeout behavior is retested; it timed out during the 2026-05-20 post-deploy warmup.
- Verify the live cPanel cron, if enabled, follows the chunked start/process/status/cutover flow and does not overlap long-running refreshes.
- Surface the cached timestamp inside the device modal once the cache is steady so analysts know how fresh each drill-down snapshot is.

## Payload Debugger & Callbacks

- Recreate the callback payload harness as a portable Python or shell helper if the eight-case success/error matrix needs to be rerun.
- Monitor `mpsm_panel_callback_debug` for live MPS Monitor traffic to validate `unique_source`, `forwarded_for`, and `completed_at` data; record any production IP ranges for future allow-listing.
- After the next vendor callback, grab a debugger screenshot highlighting the “Completed” column for support documentation (`PAYLOAD_DEBUGGER_GUIDE.md`).

## Admin & Monitoring UX

- Add a light-weight alert on the Admin Database Monitor card whenever drill-down coverage drops below 90 % so on-call staff can launch a warm-up immediately.
- Document the new sample tables (device cache, drill-down cache, panel messages, payload debugger) in the runbook so new engineers know how to interpret them.
- Keep an eye on `panel-message-monitor.php` iframe behaviour; if browsers reintroduce frame restrictions, move the debugger into an in-page tab instead of using an `<iframe>`.

## Maybe Later

- **Fragility Reduction Refactor** (detailed plan in `tender-tinkering-peacock.md` lines 245-638):
  - **Phase 1 (Safety Nets)**: Health check endpoint, schema validation, cache health dashboard, error boundaries - 6 hours, zero risk
  - **Phase 2 (Extract & Isolate)**: Split app.js into 8-10 modules, split functions.php into 6 domain files, add migration system - 2 weeks
  - **Phase 3 (Decouple)**: Migration system to stop DDL on every request, consolidate 3 cache scripts into 1 service, API response transformers - 2-3 weeks
  - **Phase 4 (Strengthen)**: Config consolidation, DI everywhere, error handling boundaries - ongoing
  - Goal: Reduce coupling, eliminate fragile implicit dependencies, make system changes safe and predictable
- Stand up the async worker fleet (Redis/Rabbit-backed queues for `cache.refresh.fast`, `cache.refresh.deep`, `api.prefetch`, `webhook.enrich`, `alerts.evaluate`, and `logs.rollup`) to offload heavy cache hydration, enrichment, and alert evaluation from request/response code paths while keeping ActionCache/MySQL fresh.
- **Incrementally layer role-based access control** (no ground-up refactor):
  - Add `role` column to `mpsm_users` (`admin`, `manager`, `viewer`)
  - Extend `loginUser()` to load role into session: `$_SESSION['role'] = $user['role']`
  - Add `requireRole($minRole)` helper alongside existing `requireAuth()`
  - Guard sensitive endpoints (`refresh-cache`, `visitor-logs`, `user-admin`) with `requireRole('admin')`
  - Frontend: inject `window.MPSM_USER_ROLE` from PHP, filter card registry by `minRole`, hide Admin tab for non-admins
  - (Phase 2 if needed): granular permissions table (`view_devices`, `export_data`, `manage_users`) with bitfield/JSON grants
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
