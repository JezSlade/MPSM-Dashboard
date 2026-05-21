# Living Audit TODO (Updated 2026-05-20)

> **Guidance:** These entries are unverified suggestions collected from live forensics. Agents must read the entire file, treat each bullet as a symptom to diagnose deeper root causes, and only pick an item once systemic issues have been ruled out.

## Current Validation Notes

- PowerShell deployment/test scripts have been retired from the active repository. Use the portable Python scripts in `scripts/` for checks, FTP backup/deploy, and live smoke testing.
- Direct FTP is the current production deployment path. Historical GitHub Actions and HTTP deploy endpoint notes are not active unless explicitly re-established.
- `cms/api/refresh-cache-chunked.php` is the current production-safe cache refresh path. Items below that cite `refresh-cache-enhanced.php` are retained as legacy audit findings against that file, not as instructions for current deployment operations.
- The 2026-05-20 live checkpoint showed 3,351 live devices, 1,425 live drilldowns, and an active staged chunked run with 3,370 devices, 300 drilldowns, and 0 errors.

## How to Work This List
- Review everything below before coding; items are interrelated.
- If you find a deeper underlying defect, document it here before fixing.
- When closing an item, include the commit hash and brief resolution so future agents know it landed.

## Root-Cause Analysis (Evidence-Based)
1. **Cache Orchestration & Data Freshness Gaps**
   - `cms/api/refresh-cache-enhanced.php:439-444` truncates cache tables before any fetch, and the subsequent inserts (lines 531-535, 763-796) occur in 5k-device batches without transactions. When the MySQL connection drops (`SQLSTATE[HY000]: 2006` in `cms/logs/cache-refresh-*`), both tables remain empty, matching the zero counts reported by `cms/api/get-database-monitor.php:28-45`.
   - Schema and repository are misaligned: `DeviceRepository::cacheDevice()` writes to an `expires_at` column (src/Repositories/DeviceRepository.php:118-127), but `ensureCacheTables()` never creates it (cms/api/refresh-cache-enhanced.php:231-256). This causes silent SQL errors when caching devices/drilldowns, explaining why cache-backed endpoints still hit the vendor API.
   - Resolved: `cacheDeviceDrilldown()` was throwing because `refresh-cache-enhanced.php` never required `bootstrap.php`, so the `app()` helper used by the function was undefined and the drill-down loop died before persisting rows. Now requiring `dirname(__DIR__, 2) . '/bootstrap.php'` ensures `DeviceRepository` is available and the cache can be populated.
   - Duplicate fetch code (`fetchAllDevices()` at cms/api/refresh-cache-enhanced.php:263-421 versus `cms/api/get-cached-devices.php:70-180`) means pagination parameters differ (FilterDealerId vs. FilterDealerCodes), so device counts never reconcile and cron outputs contradict UI stats.
   - Payload debugger now reports 2,758 invalid JSON errors; the new `payload-sanitizer.php` normalizes multi-line strings, Unicode separators, and invalid UTF-8 before decoding and logs the sanitized snippet so we can study the remnants.

2. **CMS API/Controller Duplication**
   - `cms/api/get-devices.php` and `cms/api/search-devices.php` each open raw HTTP streams (lines 52-91 and 33-114) instead of using `callMpsAPI()`. This creates divergent timeout/error handling; for example, `get-devices.php` treats any JSON decode failure as “Invalid response” while `search-devices.php` logs successes as errors (line 105), leading to inconsistent behavior reported by operators.
   - SQL bugs stem from hand-written queries per endpoint: `get-panel-messages.php:28-33` binds `INTERVAL :hours HOUR`, which MySQL rejects, so filtering by hours silently returns all rows. Because no shared query builder exists, each API reproduces the same mistakes.
   - `cms/api/login.php` reads `php://input` multiple times (lines 17-40); once the stream is exhausted, JSON logins fall through to “Username and password required,” matching the live symptom observed in testing. A shared request parser would have prevented the issue.

3. **Front-End & Command-Center UX Debt**
   - `cms/assets/app.js` is a 4,020-line single file (confirmed via line count), so there is no route-level code splitting. As a result, Admin diagnostics, visitor logs, and cache warmers all fire immediately on login, producing the sluggish dashboards reported in Jez’s TODOs.
  - `cms/assets/panel-messages.js:144-177` fetches the entire panel-message feed again just to display one payload in the modal, duplicating network cost every click. Combined with the iframe-based payload debugger (`cms/panel-message-monitor.php:180-185`) that polls separately, the command center issues redundant requests, explaining the “wonky” monitor load times.
  - Resolved: `loadDashboard()` awaited both `CardManager.refreshAll()` and `updateOfflineCountFromCache()` before the UI became interactive, so the modal overlay stayed in place for minutes while the remote APIs finished; both tasks now run asynchronously after the header renders so the dashboard no longer freezes on login.

4. **mps-api Gateway & Webhook Engine**
   - `mps-api/index.php:346-375` enforces rate limiting by creating/deleting `ratelimit_*.log` files on every request. Under load the filesystem becomes the bottleneck, which matches production complaints about slow `/mps-api/query` even when the vendor API is healthy.
   - `mps-api/callbacks/panel-message.php` calls `processNotificationRules()` synchronously after inserting the payload (lines 92-98). Any notification failure triggers a 500 to the vendor and causes repeated retries, which explains why `mpsm_panel_messages` occasionally contains duplicate rows and why webhook latency spikes.
   - Diagnostics endpoints recompute file sizes, permissions, and `.env` contents on every call (lines 111-178). Since monitoring hits `/mps-api/health` frequently, the overhead is self-inflicted, leading to the 1–2 s health-check latency captured during manual tests.

5. **Operational Observability & Tooling**
   - Resolved for repository docs/scripts on 2026-05-20: active docs now point operators at the chunked refresh flow, and the PowerShell cache loop scripts were retired. Verify the live cPanel cron still follows the same chunked, non-overlapping process before relying on unattended refreshes.
   - No synthetic watchdog hits `/cms/api/system-health.php` or `/mps-api/health`, so regressions (e.g., cache counts stuck at 0) go unnoticed until someone opens the Admin tab. This gap explains why production ran for days with empty cache tables despite cron “success” logs.
   - Observability tables (`mpsm_panel_messages`, `mpsm_panel_callback_debug`, `mpsm_visitor_log`) lack retention jobs; as they grow, endpoints like `get-payload-debug-logs.php` and `get-visitor-logs.php` slow down (each runs full-table scans for stats: see lines 96-108 in visitor logs). The increasing latency is therefore due to missing lifecycle tooling rather than isolated query bugs.

---

## Immediate Failures (Legacy Enhanced Refresh Triage)

The following cache items were collected against `cms/api/refresh-cache-enhanced.php`. The current live refresh workflow uses `cms/api/refresh-cache-chunked.php`, which stages data before cutover. Keep these items as technical debt for the legacy script and for any future refresh implementation review.
- [ ] **(1) Cache truncates before refill succeeds** – `cms/api/refresh-cache-enhanced.php:439-444` wipes both cache tables before a successful run; switch to staging/transactional swaps so old data survives crashes.
- [ ] **(2) Batch writes overwhelm MySQL** – The crawler buffers 50 pages (~5k devices) before inserting (`cms/api/refresh-cache-enhanced.php:436-537`); stream smaller batches to prevent disconnects.
- [ ] **(3) Drilldown preload exhausts RAM** – `cms/api/refresh-cache-enhanced.php:102-157` loads every cached row into an array before processing; iterate via paged queries/streaming cursor.
- [ ] **(4) Fixed 250 ms cadence makes runs exceed cron window** – `usleep(250000)` per device (`cms/api/refresh-cache-enhanced.php:112-135`) serializes drilldowns; parallelize via QueueManager or reduce the per-device sleep dynamically.
- [ ] **(5) Inserts run one row at a time** – `cacheDeviceList()` (`cms/api/refresh-cache-enhanced.php:763-796`) executes per device. Use multi-row inserts inside a transaction.
- [ ] **(6) Script reports success even when caches are empty** – Stats warnings (`cms/api/refresh-cache-enhanced.php:169-190`) don’t fail the run; emit non-200 so monitoring alerts.
- [ ] **(7) Database monitor uses `NOT IN` scans** – `cms/api/get-database-monitor.php:48-67` will table-scan once caches fill; convert to indexed joins.
- [ ] **(8) Pagination still assumes 50-row pages** – `PageRows` is 50 and loop caps at 500 pages (`cms/api/refresh-cache-enhanced.php:455-543`); respect the vendor’s 100-row pages and upstream metadata.
- [ ] **(9) callMPSMAPI hides fatal transport errors** – Returning `null` (`cms/api/refresh-cache-enhanced.php:628-729`) causes false “complete” runs; throw so the job aborts and keeps prior data.
- [ ] **(10) DeviceRepository queries rely on JSON scans** – Filtering via `JSON_EXTRACT` (`src/Repositories/DeviceRepository.php:43-86`) becomes untenable once caches populate; persist indexed columns for dealer/customer/model/IP.

## Optimization & Robustness Opportunities
- [ ] **(11) Request 100 devices per page** – Update `PageRows` (`cms/api/refresh-cache-enhanced.php:455-458`) to match vendor behavior.
- [ ] **(12) `array_filter` drops intentional zeros** – `array_filter($params)` (`cms/api/refresh-cache-enhanced.php:468-470`) removes `SortOrder = 0`; supply a callback that only strips `null`.
- [ ] **(13) `array_merge` copies batches repeatedly** – Appending pages (`cms/api/refresh-cache-enhanced.php:520-523`) should use `$deviceBatch[]` to avoid O(n²) copying.
- [ ] **(14) Drilldown queue should stream, not preload** – Same section (`cms/api/refresh-cache-enhanced.php:102-109`); process via chunked queries.
- [ ] **(15) Stop after partial page** – Break the loop once `<100` devices arrive (`cms/api/refresh-cache-enhanced.php:539-542`) to save extra calls.
- [ ] **(16) Populate full modal payload in cache** – `fetchDeviceDrillDown()` (`cms/api/refresh-cache-enhanced.php:606-623`) only stores `Device/Get`; add counters/SDS/actions/supplies.
- [ ] **(17) Call vendor engine directly** – `callMPSMAPI()` posts to `/mps-api/query` (`cms/api/refresh-cache-enhanced.php:629-706`); reuse engine classes to bypass extra HTTP hops.
- [ ] **(18) Don’t treat empty response as success** – Validate vendor `TotalRows/HasNext` before assuming completion (`cms/api/refresh-cache-enhanced.php:312-341`).
- [ ] **(19) Panel message counts need incremental tracking** – `cachePanelMessages()` (`cms/api/refresh-cache-enhanced.php:804-813`) currently full-scans `mpsm_panel_messages`.
- [ ] **(20) Drilldown coverage flags mis-set** – `cacheDeviceDrillDownSimple()` (`cms/api/force-populate-all-drilldowns.php:214-235`) uses `supplyAlerts` for both alert/supply booleans.
- [ ] **(21) Dealer filter compares wrong field** – `DeviceRepository::findAll()` (`src/Repositories/DeviceRepository.php:43-47`) compares dealer IDs to `DealerCode`.
- [ ] **(22) Search JSON paths have wrong casing** – Queries hit `$.equipmentId`/`$.ipAddress` (`src/Repositories/DeviceRepository.php:61-72`) even though payload keys are capitalized.
- [ ] **(23) Schema lacks `expires_at`** – `DeviceRepository::cacheDevice()` (`src/Repositories/DeviceRepository.php:118-127`) writes to `expires_at`, but `ensureCacheTables()` (`cms/api/refresh-cache-enhanced.php:231-241`) never created it.
- [ ] **(24) Drilldown retrieval ignores expiry** – `getDrilldown()` (`src/Repositories/DeviceRepository.php:161-194`) should discard stale entries.
- [ ] **(25) List cache invalidation stubbed out** – `BaseRepository::invalidateListCache()` (`src/Repositories/BaseRepository.php:226-233`) needs real cache-key tracking.
- [ ] **(26) File-cache invalidation rescans disk** – `cacheClear()` (`cms/functions.php:604-608`) `glob`s entire cache on every invalidation; track keys centrally.
- [ ] **(27) Cache stats re-read every JSON file** – `getCacheStats()` (`cms/functions.php:615-652`) scales poorly; maintain aggregates during writes.
- [ ] **(28) Rate limiting via log files** – `mps-api/index.php:346-375` is I/O heavy; move to APCu/Redis counters.
- [ ] **(29) Swagger diagnostics stat missing files** – `mps-api/index.php:111-142` should track the actual casing used to prevent warnings.
- [ ] **(30) Remove unused `fetchAllDevices()`** – Legacy function (`cms/api/refresh-cache-enhanced.php:263-421`) duplicates crawler logic.
- [ ] **(31) Skip rewriting unchanged payloads** – Detect unchanged JSON before re-upserting (`cms/api/refresh-cache-enhanced.php:531-534`).
- [ ] **(32) Accept alternate identifiers for drilldowns** – `fetchDeviceDrillDown()` should fall back to `ExternalIdentifier`, etc.
- [ ] **(33) Release batch memory after flushing** – `cms/api/refresh-cache-enhanced.php:531-535`; `unset` or use generators.
- [ ] **(34) Reuse prepared statement** – Keep a single prepared handle inside `cacheDeviceList()` (`cms/api/refresh-cache-enhanced.php:763-796`).
- [ ] **(35) Make backoff adaptive** – Only sleep when rate limits actually fire (`cms/api/refresh-cache-enhanced.php:112-136`).
- [ ] **(36) Separate retry queue** – Rate-limited devices shouldn’t re-enter the main queue (`cms/api/refresh-cache-enhanced.php:150-152`).
- [ ] **(37) Wrap batch inserts in transactions** – Avoid per-row fsyncs (`cms/api/refresh-cache-enhanced.php:763-796`).
- [ ] **(38) Persist vendor timestamps** – Store upstream `LastUpdate`/`Install` when caching (`cms/api/refresh-cache-enhanced.php:775-795`).
- [ ] **(39) Fix duplicate supply/alert flags** – Same issue as #20 for the force-populate script.
- [ ] **(40) Delete dead legacy pagination comments/code** – Clean the unused block (`cms/api/refresh-cache-enhanced.php:263-421`) once incremental path is solid.

---

## Platform-Wide Backlog (Items 41-140)
- [ ] **(41) Login JSON bodies still fail intermittently** – `cms/api/login.php:17-40` reads `php://input` twice; capture once and reuse so JSON logins do not return “Username and password required.”
- [ ] **(42) Login error logging dumps user payloads** – `cms/api/login.php:47` writes `print_r($data)` (including usernames/password hints) into php_errors, creating noisy logs; log metadata only.
- [ ] **(43) Device list endpoint reimplements HTTP plumbing** – `cms/api/get-devices.php:52-91` opens its own `file_get_contents` stream instead of using the shared `callMpsApi*` helper, so retries/timeouts diverge from other endpoints.
- [ ] **(44) “All customers” search still pinned to default dealer** – `cms/api/get-devices.php:37-50` always injects `DEFAULT_DEALER_ID/CODE`, ignoring query parameters and preventing multi-dealer operations.
- [ ] **(45) Live device list never consults cached tables** – Despite `mpsm_cache_devices` existence, `cms/api/get-devices.php` always hits the vendor API; wire it to serve from cache when `useCache=1`.
- [ ] **(46) Allowable `pageRows` (1–5000) exceeds vendor max** – `cms/api/get-devices.php:17-32` tells callers they can request 5k rows even though the upstream caps at 100, leading to false expectations; document and loop server-side instead.
- [ ] **(47) Deep-dive endpoint duplicates query helper** – `cms/api/get-device-deep-dive.php:36-69` copies HTTP logic that already exists elsewhere; centralize so diagnostics/backoff are consistent.
- [ ] **(48) Drilldown cache queried twice per request** – `cms/api/get-device-deep-dive.php:127-170` and again near line 264 run the same SELECT; reuse the first result to cut database chatter.
- [ ] **(49) Deep-dive makes four sequential vendor calls** – Lines 302-370 fetch counters, actions, and alerts serially; batch them via parallel promises or cached drilldowns to keep modal latency <500 ms.
- [ ] **(50) Deep-dive silently drops upstream errors** – `callMpsApiQuery()` returns `null` on failure and the caller just leaves sections empty; bubble structured errors so the UI can surface “panel history unavailable” rather than blank cards.
- [ ] **(51) Panel-message query binds `INTERVAL :hours`** – `cms/api/get-panel-messages.php:28-33` uses a bound parameter inside the `INTERVAL` expression, which MySQL rejects; compute the cutoff timestamp in PHP instead.
- [ ] **(52) Always returning full callback payloads** – `cms/api/get-panel-messages.php:23-57` sends the JSON payload for every row even when the table view only needs metadata; add `includePayload=0`.
- [ ] **(53) No paginated access to panel messages** – Endpoint only offers `limit`/`hours` with no cursor, so analysts can’t page beyond the first 500 rows; add `offset` or `before_id`.
- [ ] **(54) Payload-debug SQL interpolates LIMIT** – `cms/api/get-payload-debug-logs.php:45-53` concatenates `LIMIT {$limit}`; switch to a bound integer consistently with other filters.
- [ ] **(55) Debug log responses include entire `raw_body`** – `cms/api/get-payload-debug-logs.php:98-114` streams complete payloads even when 65 KB; add truncation/download links to prevent UI freezes.
- [ ] **(56) Callback debugger lacks pagination state** – `cms/api/get-payload-debug-logs.php:45-146` only ever returns the most recent `limit` rows, forcing manual log scraping; expose cursors for deep history.
- [ ] **(57) Error-log endpoint re-reads huge files per request** – `cms/api/get-error-logs.php:58-77` scans the entire tail each time; cache a file pointer or stream through `tail -n`.
- [ ] **(58) Filtering happens after full load** – `cms/api/get-error-logs.php:82-107` filters the entire in-memory array; apply `filter`/`level` during the read loop to avoid double work on large logs.
- [ ] **(59) Dealer-wide cache stored only in files** – `cms/api/get-cached-devices.php:18-33` writes to the file-based cache, so a PHP restart discards the expensive crawl; persist into `mpsm_cache_devices`.
- [ ] **(60) Dealer cache lacks a lock or PID file** – Nothing prevents two users from triggering the same long-running crawl in `get-cached-devices.php`; reuse the enhanced refresh lock.
- [ ] **(61) Dealer crawl keeps array-merging huge pages** – `cms/api/get-cached-devices.php:113-120` uses `array_merge` in a loop, causing repeated copy costs; push onto an array or stream insert.
- [ ] **(62) HTTP helper swallows upstream diagnostics** – `callMpsApiDirect()` in `get-cached-devices.php:36-65` returns `null` without logging the HTTP code, making it impossible to triage crawl failures.
- [ ] **(63) Partial-page detection still assumes 50-row responses** – `cms/api/get-cached-devices.php:115-118` stops when `<50`, yet the upstream returns up to 100 rows; the last 50 rows per page are skipped.
- [ ] **(64) Installed+deleted sets are merged in RAM** – `cms/api/get-cached-devices.php:148-175` consolidates both lists before caching, consuming hundreds of MB; stream to disk/DB instead.
- [ ] **(65) Search API logs every success as an error** – `cms/api/search-devices.php:105` writes a success message to `error_log`, flooding production logs; remove or guard behind DEBUG.
- [ ] **(66) Search results cap at 100 with no pagination** – `cms/api/search-devices.php:34-47` always returns only the first 100 matches; introduce paging tokens so analysts can view everything.
- [ ] **(67) FilterText input isn’t escaped for literal `%/_` searches** – `cms/api/search-devices.php:33-47` passes raw query strings to the vendor; escape wildcard characters so users can search for strings containing `%`.
- [ ] **(68) Preference saver ignores `json_last_error`** – `cms/api/save-preferences.php:16-24` treats `json_decode(false)` as “no preferences,” causing silent failure; validate JSON before saving.
- [ ] **(69) Preferences aren’t schema-validated** – `saveUserPreferences()` accepts arbitrary keys, so broken structures crash the SPA; validate allowed keys before persisting.
- [ ] **(70) get-preferences assumes session ID** – `cms/api/get-preferences.php:11-15` uses `$_SESSION['user_id']` without checking existence; add guard that returns 401 if the session expired.
- [ ] **(71) Visitor log stats ignore active filters** – `cms/api/get-visitor-logs.php:96-108` always report unique user/IP counts across the entire table, not the filtered window, confusing operators; apply filters to the stats queries too.
- [ ] **(72) Visitor log `LIKE` filters treat `%` literally** – `cms/api/get-visitor-logs.php:38-61` inserts user-provided `%`/`_` characters directly, so analysts can’t search for literal wildcards; escape them before binding.
- [ ] **(73) Visitor stats recompute whole-table counts every request** – `cms/api/get-visitor-logs.php:96-108` runs three full scans even when the calling card refreshes every minute; materialize or cache stats.
- [ ] **(74) Visitor date filters ignore timezone** – `cms/api/get-visitor-logs.php:48-56` compares raw strings to UTC columns even though the UI expects Eastern; convert incoming dates to UTC before binding.
- [ ] **(75) Payload modal refetches entire dataset** – `cms/assets/panel-messages.js:144-175` re-calls `get-panel-messages.php` just to show one payload; add `api/get-panel-message.php?id=...` to avoid redundant fetches.
- [ ] **(76) Panel monitor keeps polling while tab hidden** – `panel-messages.js:184-189` always runs a 30 s interval; pause/resume based on `document.visibilityState` to reduce load.
- [ ] **(77) Monitor view duplicates CSS inline** – `cms/panel-message-monitor.php:16-154` embeds a large `<style>` block rather than reusing `assets/style.css`, so styling drifts; move monitor-specific styles into the shared bundle.
- [ ] **(78) Payload debugger lives in an iframe** – `panel-message-monitor.php:180-185` loads `/cms/payload-debugger.php` as an iframe, triggering a second session handshake and double API polling; convert to an in-page tab.
- [ ] **(79) Dashboard SPA lives in a 4 k-line file** – `cms/assets/app.js` bundles routing, data layer, and UI logic in one 4020-line script; break into modules to reduce regression risk.
- [ ] **(80) `app.js` reimplements fetch scaffolding dozens of times** – There’s no shared API client, so headers/error handling diverge per request; factor fetch logic into a single helper.
- [ ] **(81) Admin tab preloads heavy cards on every login** – `app.js` fires system-health, database-monitor, and visitor-log requests even for users who never open the Admin tab; delay until tab activation.
- [ ] **(82) Global state is mutated from multiple modules with no guard** – `app.js` exposes the mutable `state` object globally, so add a tiny store (or proxies) to catch accidental mutations.
- [ ] **(83) Device search results aren’t cached per dealer/customer** – `app.js` re-fetches identical searches if the user toggles between filters; memoize by `(dealer, customer, query)`.
- [ ] **(84) Date formatting logic repeated across cards** – Multiple sections in `app.js` rebuild `formatDate()` utilities; move to a shared module to keep localization consistent.
- [ ] **(85) Panel monitor lacks virtual scrolling** – Rendering 500 rows at once (`panel-messages.js:47-84`) causes layout thrash; introduce virtualization or pagination.
- [ ] **(86) Command center iframe height hard-coded** – `panel-message-monitor.php:147-153` pins debugger iframe to 70 vh; dynamically size it to avoid double scrollbars.
- [ ] **(87) Device lifecycle tab hidden but still loads assets** – `panel-message-monitor.php:174-179` still renders the button even when `FEATURE_DEVICE_CRUD` is off; hide server-side to avoid confusion.
- [ ] **(88) SPA lacks route-based code splitting** – All features ship in `app.js`, so even login-only flows download admin logic; introduce a bundler or dynamic imports.
- [ ] **(89) SPA fetches visitors/logs without cancellation** – Network calls triggered during tab switches aren’t aborted, leading to race conditions; attach `AbortController`.
- [ ] **(90) SPA uses `innerHTML` rendering for large tables** – Panel monitor builds rows with template strings (`panel-messages.js:56-83`), which re-parses HTML every refresh; use `DocumentFragment` diffing.
- [ ] **(91) SPA lacks unit tests** – `tests/test-examples.php` is a placeholder; add PHPUnit coverage for repositories, cache helpers, and queue classes.
- [ ] **(92) DeviceRepository search duplicates JSON decode** – `src/Repositories/DeviceRepository.php:292-314` decodes `device_data` even though the caller already parses it; return decoded arrays once.
- [ ] **(93) DeviceRepository cache invalidation stubbed** – `BaseRepository::invalidateListCache()` (lines 226-233) is empty; implement tagging so list caches refresh after writes.
- [ ] **(94) QueueManager has no worker** – `src/Queue` registers jobs but nothing consumes them; document or remove to avoid a false sense of background processing.
- [ ] **(95) Payload debugger uses SELECT * with raw TEXT** – `cms/api/get-payload-debug-logs.php:45-115` pulls full columns even when UI toggles “metadata only”; add column selection.
- [ ] **(96) Visitor log endpoint lacks `order` parameter** – Analysts can’t request chronological asc view; add sort options.
- [ ] **(97) Export endpoint reads entire file into memory** – `cms/api/run-export.php` base64-encodes large CSVs before returning; stream via chunked download.
- [ ] **(98) Cron endpoints return HTML on failure** – `cms/api/refresh-cache-cron.php` echoes raw text rather than JSON, so monitoring can’t parse status; align outputs.
- [ ] **(99) Command center rules engine executes inside callback thread** – `mps-api/callbacks/panel-message.php:91-99` runs `processNotificationRules()` synchronously, increasing webhook latency; enqueue work instead.
- [ ] **(100) Callback secret hard-coded** – `panel-message.php:57-60` embeds the shared secret string, forcing code change for rotation; load from `.env`.
- [ ] **(101) Callback debugger writes large headers row per insert** – `mps-api/callbacks/panel-message-common.php` stores full header JSON even for duplicate keys; normalize header storage to reduce DB bloat.
- [ ] **(102) Command-center engine lacks retry logging** – `callbacks/command-center-engine.php` quietly catches exceptions; add `error_log` with message IDs.
- [ ] **(103) mps-api rate limiter globs files every request** – `mps-api/index.php:346-375` deletes old `ratelimit_*.log` files for every call, burning IO; rotate asynchronously.
- [ ] **(104) MAX_REQUEST_SIZE hard-coded** – `mps-api/index.php:67` pegs max request size at 1 MB; expose env var so larger exports can run in sandboxes.
- [ ] **(105) Diagnostics always read `.env` fully** – `mps-api/index.php:127-178` loads and parses `.env` for every diagnostics call; cache results to reduce disk churn.
- [ ] **(106) Engine required twice** – `mps-api/index.php:24` and later near line 197 both `require_once engine.php`; drop the second include to avoid wasted work.
- [ ] **(107) `sendResponse` lacks JSON_THROW_ON_ERROR** – `mps-api/index.php:302-333` silently falls back to “Internal server error” when encoding fails; throw and log the broken payload.
- [ ] **(108) `getRequestBody()` treats any non-JSON POST as suspicious** – `mps-api/index.php:10-16` logs “Invalid content type” for legitimate form posts (e.g., login); restrict to `/query`.
- [ ] **(109) mps-api diagnostics expose file sizes on every request** – `getSystemDiagnostics()` recomputes file metadata each time; memoize so `/health` stays sub-100 ms.
- [ ] **(110) mps-api `MAX_REQUESTS_PER_MINUTE` static** – Rate limit constant (line 70) should be env-configurable per deployment tier.
- [ ] **(111) mps-api logs huge “security events” to disk** – `logSecurityEvent()` writes per violation but no rotation; use central logger/structured records.
- [x] **(112) Cron helper scripts duplicate curl invocations** – Resolved for the active repository on 2026-05-20 by retiring PowerShell helpers and documenting the chunked cache path. Verify live cPanel cron separately.
- [x] **(113) PowerShell scripts lack parameter validation** – Resolved for the active repository on 2026-05-20 by removing PowerShell from the supported workflow and replacing deploy/test helpers with portable Python scripts.
- [ ] **(114) No automated smoke tests for `/cms` endpoints** – `tests/api-tests.sh` still references legacy URLs; regenerate it to match the current API surface.
- [ ] **(115) `bootstrap.php` sets timezone before reading config overrides** – If `.env` sets `APP_TIMEZONE`, it’s ignored; load config first, then set timezone.
- [ ] **(116) `cacheDeviceDrilldown()` calculates `has_supplies` incorrectly** – `src/Repositories/DeviceRepository.php:200-237` only checks for supply arrays, ignoring flags in Device/Get; refine heuristics.
- [ ] **(117) Queue jobs never expire** – `mpsm_jobs` rows sit forever because no cleanup script exists; add `cleanup-jobs.php`.
- [ ] **(118) Payload debugger lacks index on `unique_source`** – Queries in `get-payload-debug-logs.php` filter by `unique_source` but table definition doesn’t index it; update schema.
- [ ] **(119) Panel message table missing composite index** – Filtering by `device_serial` and `received_at` is common; add a compound index to `mpsm_panel_messages`.
- [ ] **(120) Device cache tables missing TTL enforcement** – Nothing deletes stale rows from `mpsm_cache_device_drilldown`; add scheduled purge.
- [ ] **(121) Visitor log table grows without retention** – `mpsm_visitor_log` is never pruned; implement 90-day cleanup job.
- [ ] **(122) Panel callback debug table logs raw headers forever** – Add retention job or optional auto-prune in `panel-message-common.php`.
- [ ] **(123) Command center notifications processed synchronously** – See `command-center-engine.php`; move heavy rule evaluation into a queue to prevent callback lag.
- [x] **(124) Cron docs out of sync with code** – Resolved in the active context docs on 2026-05-20. Current docs describe the chunked cache status/process/cutover flow and call out live cPanel cron verification as a follow-up.
- [ ] **(125) Device CRUD endpoints bypass cache invalidation** – `cms/api/device-create.php`/`update.php` don’t clear `mpsm_cache_devices`; ensure caches flush on mutations.
- [ ] **(126) Device CRUD logging grows unbounded** – `logDeviceCrudAction()` writes to `cms/logs/device-crud-*.log` with no rotation; add size/time pruning.
- [ ] **(127) `get-drilldown-count.php` recalculates stats each poll** – Replace the three `COUNT(*)` queries with a cached materialized view so the Admin card doesn’t scan both tables every refresh.
- [ ] **(128) `get-drilldown-count.php` also uses bound INTERVAL math** – Calculate `NOW() - INTERVAL` thresholds in PHP to avoid MySQL placeholder errors.
- [ ] **(129) Panel-message endpoint lacks `processed` filter** – Add `processedOnly=true` support so the command center can focus on unreviewed alerts.
- [ ] **(130) `trackVisit('/panel-message-monitor')` only fires at page load** – Background AJAX refreshes aren’t logged, so visitor stats undercount; add API-level tracking instead of page-level.
- [ ] **(131) Panel payload modal can’t export JSON** – Provide a “Download JSON” button so analysts aren’t forced to copy text from the viewer.
- [ ] **(132) Global search input lacks debounce** – Each keystroke triggers API calls and log writes; wrap with a 300 ms debounce.
- [ ] **(133) Preferences stored in localStorage lack versioning** – When schema changes, stale entries break rendering; namespace/set version and purge old entries.
- [ ] **(134) Error toasts auto-hide too quickly** – Five concurrent API calls all produce 3 s toasts; extend or stack errors so analysts can read them.
- [ ] **(135) `/mps-api/diagnostics` exposes PHP internals publicly** – Restrict diagnostics to authenticated CMS sessions or behind a secret token so bots can’t scrape phpinfo-style data.
- [ ] **(136) `/mps-api/health` pings the vendor on every check** – Current implementation calls `AlertLimit/Dealer/Get` for each health probe, tying uptime alarms to upstream latency; cache the last success for a minute.
- [x] **(137) PowerShell cache scripts still document */5 cron** – Resolved for active repository operations on 2026-05-20 by retiring PowerShell cache scripts.
- [x] **(138) PowerShell scripts swallow curl exit codes** – Resolved for active repository operations on 2026-05-20 by retiring PowerShell cache scripts and using Python helpers that return non-zero on failures.
- [ ] **(139) Command-center rule definitions duplicated** – `create-live-rules.php` and `create-sample-rules.php` each hard-code JSON; extract shared config to avoid drift.
- [ ] **(140) No synthetic monitoring of `/cms/api/system-health.php`** – Add an external cron that hits the endpoint, validates JSON schema, and alerts when fields go missing.

---

## Extended Backlog (Items 141-240)
- [ ] **(141) Refresh doc still claims 5-minute cadence** – `cms/api/refresh-cache-enhanced.php:12-13` and operations docs reference */5 cron even though full runs require ~30 minutes; update to prevent overlapping jobs.
- [ ] **(142) Runtime limit overrides unchecked** – `set_time_limit(3600)` and `ini_set('memory_limit','1G')` (lines 15-16) ignore failures, so shared hosts silently keep old limits; detect and log when overrides fail.
- [ ] **(143) Cache logging ignores write failures** – `logMessage()` (lines 35-46) doesn’t verify `mkdir`/`file_put_contents` success, so cache telemetry disappears when logs directory isn’t writable.
- [ ] **(144) Lock-response lacks HTTP headers** – When another run is active (`cms/api/refresh-cache-enhanced.php:65-75`), the script `die()`s with raw JSON and no status; wrap in `jsonError` so monitoring can parse.
- [ ] **(145) Lock is written before DB connectivity** – Line 78 sets the lock file before calling `getDatabase()`, so connection errors leave a stale lock that blocks subsequent runs.
- [ ] **(146) Stats copied even after fatal fetches** – If `fetchAndCacheAllDevicesIncremental()` throws, `$deviceStats` is undefined yet `$stats['devices_cached']` remains 0, causing false “success” responses.
- [ ] **(147) Drilldown queue loads entire table** – Lines 103-110 fetch every `device_data` row before drilling down, causing memory exhaustion once cache tables hold tens of thousands of devices; stream via cursors.
- [ ] **(148) Fixed 250 ms delay throttles throughput** – Drilldown loop (lines 132-136) always sleeps 250 ms regardless of rate limits; switch to adaptive backoff so runs finish before cron overlap.
- [ ] **(149) Devices hitting 10 retries simply vanish** – Rate-limit handler (lines 139-152) drops any serial after 10 attempts, lowering coverage without alerting; track and retry next run.
- [ ] **(150) No structured run artifact** – Completion block (lines 189-200) only logs text; persist JSON metrics so ops can trend coverage.
- [ ] **(151) API output bypasses `jsonSuccess`** – Lines 204-211 `die(json_encode(...))`, omitting standard headers/security flags; align with other CMS APIs.
- [ ] **(152) Cache DDL missing `expires_at`** – `ensureCacheTables()` (lines 226-256) doesn’t create `expires_at`, yet `DeviceRepository::cacheDevice()` writes to that column; fix schema to prevent insert failures.
- [ ] **(153) Legacy `fetchAllDevices()` still truncates tables** – Dead code at lines 263-421 keeps the truncation logic accessible and confuses auditors; delete once incremental path is validated.
- [ ] **(154) Drilldown table cleared even when fetch fails** – Lines 439-444 truncate caches before verifying any rows were fetched; use staging tables or wrap operations in transactions.
- [ ] **(155) Request params allow cross-dealer fetches** – Installed-device params (lines 445-458) send `null` for both `FilterDealerId` and `FilterDealerCodes`, causing the crawl to grab every dealer tied to the credential unintentionally.
- [ ] **(156) `array_filter` strips numeric zeros** – Lines 468-470 remove `SortOrder = 0`, allowing the vendor to pick default ordering; specify a callback that only drops `null`.
- [ ] **(157) Vendor response discarded on failure** – Lines 483-495 treat any falsy response as “empty page” and never log the payload, making RCA impossible; log body and HTTP status.
- [ ] **(158) Batch merging copies huge arrays** – Lines 520-534 use `array_merge`, creating O(n²) memory churn; append items instead.
- [ ] **(159) Deleted devices lack customer metadata** – Lines 552-594 mark `IsUninstalled` but do not persist customer info, preventing churn analysis by client.
- [ ] **(160) Drilldown requests ignore `ExternalIdentifier`** – `fetchDeviceDrillDown()` (lines 605-623) fails to fall back to `ExternalIdentifier`/`AssetNumber`, so many devices never receive cached deep dives.
- [ ] **(161) Login JSON parser consumes stream twice** – `cms/api/login.php:17-40` calls `file_get_contents('php://input')` multiple times, exhausting the stream and causing intermittent “username required” failures.
- [ ] **(162) Login error logs expose payloads** – Line 47 logs `print_r($data)` (including password hints) to php_errors; redact sensitive fields.
- [ ] **(163) Logout endpoint accepts GET** – `cms/api/logout.php` doesn’t enforce POST-only semantics, letting simple link clicks log users out.
- [ ] **(164) Device list ignores supplied dealer IDs** – `cms/api/get-devices.php:37-45` always uses default dealer codes, preventing tier-2 debugging for other tenants.
- [ ] **(165) Device list duplicates HTTP helper** – Lines 52-91 open `file_get_contents` instead of `callMPSQuery()`, so retries/backoff differ from other endpoints.
- [ ] **(166) Deep-dive adds yet another ad-hoc HTTP helper** – `cms/api/get-device-deep-dive.php:36-69` implements `callMpsApiQuery()` locally, guaranteeing drift vs. shared logic.
- [ ] **(167) Deep-dive bypasses repositories** – Lines 127-170 handcraft SQL for cached drilldowns; use `DeviceRepository` to centralize TTL checks.
- [ ] **(168) Deep-dive re-fetches counters/actions even when cached** – Lines 305-367 ignore cached drilldown data, doubling vendor load.
- [ ] **(169) Panel-message query uses invalid INTERVAL binding** – `cms/api/get-panel-messages.php:28-33` binds `:hours` inside `INTERVAL`, so MySQL ignores the filter and scans entire table.
- [ ] **(170) Panel-message endpoint always transmits payloads** – Lines 52-57 include full JSON bodies for every row; add `includePayload` flag to reduce response size.
- [ ] **(171) Payload-debug logs interpolate LIMIT** – `cms/api/get-payload-debug-logs.php:45-53` concatenates `LIMIT {$limit}`; bind integers to leverage query caching.
- [ ] **(172) Payload-debug accepts arbitrary `limit` strings** – Lack of validation causes PDO warnings when non-numeric limits arrive.
- [ ] **(173) Error-log API reloads entire file every call** – `cms/api/get-error-logs.php:58-77` reads the whole log tail before filtering; stream with `SplFileObject::seek`.
- [ ] **(174) Error-log filtering occurs post-read** – Lines 82-108 filter in-memory arrays, doubling CPU; apply filters while streaming.
- [ ] **(175) Dealer cache helper duplicates HTTP logic** – `cms/api/get-cached-devices.php:36-65` defines its own request helper lacking retries/backoff.
- [ ] **(176) Dealer cache only populates file cache** – Lines 70-189 write to file-based cache, not MySQL tables, so Admin “cache coverage” never improves.
- [ ] **(177) Drilldown count endpoint recomputes counts each refresh** – `cms/api/get-drilldown-count.php:19-48` executes three `COUNT(*)` queries per poll; cache results for the dashboard.
- [ ] **(178) Visitor log filters allow wildcard injection** – `cms/api/get-visitor-logs.php:34-61` passes user-supplied `%/_` into `LIKE` without escaping.
- [ ] **(179) Export heuristics reinvent payload defaults** – `cms/api/run-export.php:20-137` guesses parameters instead of using the definitions already in `EndpointCatalog`.
- [ ] **(180) Export responses always base64 inside JSON** – Lines 201-230 return files encoded in JSON, forcing the browser to allocate large buffers; stream downloads instead.
- [ ] **(181) SPA bundle ships entire Admin stack to login page** – `cms/assets/app.js` is always loaded, even on `/cms/login.html`, increasing first paint time.
- [ ] **(182) Admin cards autoload at login** – The bundle prefetches system health, cache stats, and visitor logs regardless of tab usage, overloading the API.
- [ ] **(183) Global `state` object mutated everywhere** – No store abstraction exists, so multiple tabs can clobber each other’s data.
- [ ] **(184) Fetch calls lack AbortControllers** – Navigating away mid-request leaves dangling promises that still update DOM, causing race conditions.
- [ ] **(185) Preferences stored in localStorage without versioning** – Schema changes break rendering when old JSON remains; add versioned keys/migrations.
- [ ] **(186) Panel monitor rebuilds table via `innerHTML`** – Large data sets trigger layout thrash; use keyed rendering/virtualization.
- [ ] **(187) Payload modal refetches entire dataset** – `panel-messages.js:144-175` loads all rows again just to view one payload; add single-row endpoint.
- [ ] **(188) Monitor view includes inline CSS** – `cms/panel-message-monitor.php` duplicates styles already in `assets/style.css`, causing drift.
- [ ] **(189) Payload debugger embedded via iframe** – The iframe triggers extra session/auth flows and redundant polling; convert to an in-page component.
- [ ] **(190) Polling continues when tab hidden** – No `visibilitychange` handling, so panel monitor consumes bandwidth even when backgrounded.
- [ ] **(191) Device-lifecycle assets load even when feature flag off** – Hidden tab still loads JS/CSS, wasting resources.
- [ ] **(192) SPA lacks automated tests** – There are no Jest/mocha tests; regressions go unnoticed.
- [ ] **(193) Device CRUD scripts duplicate API helpers** – `device-crud.js` copies fetch logic from `app.js`, leading to inconsistent error handling.
- [ ] **(194) Global search lacks debounce** – Multiple API requests fire per keystroke and log spam shows up in error logs.
- [ ] **(195) Timestamp formatting ignores server timezone** – Hardcoded locale logic leads to mismatched times when ops adjust TZ.
- [ ] **(196) Cached search entries ignore dealer context** – Results are keyed only by query text, so switching customers returns the wrong rows.
- [ ] **(197) Error toasts auto-dismiss quickly** – 3-second toasts disappear before analysts can read them; extend or stack notifications.
- [ ] **(198) Device-lifecycle buttons visible without permission** – UI still renders buttons even when feature flag is off, confusing users.
- [ ] **(199) Command center visits undercounted** – `trackVisit('/panel-message-monitor')` fires only once; AJAX-heavy interactions never log.
- [ ] **(200) Login page still imports the entire SPA** – There is no lightweight login bundle, so even unauthenticated users download megabytes of JS.
- [ ] **(201) mps-api rate limiter relies on filesystem globbing** – `mps-api/index.php:346-375` creates/deletes log files every request, slowing queries.
- [ ] **(202) Diagnostics re-read `.env` each call** – `mps-api/index.php:111-178` parses config files on every request, wasting CPU.
- [ ] **(203) `sendResponse` suppresses JSON encoding errors** – Failures fall back to “internal server error” without logging the problematic payload.
- [ ] **(204) `getRequestBody()` logs “invalid content-type” for form posts** – The proxy flags legitimate requests as suspicious, bloating security logs.
- [ ] **(205) `/health` pings vendor on every call** – Health checks incur real vendor traffic, tying availability alarms to upstream slowness.
- [ ] **(206) `/diagnostics` accessible without auth** – Anyone can fetch PHP version, file paths, and config hints.
- [ ] **(207) OAuth tokens stored only in memory** – Each PHP process authenticates separately; token cache is not shared across requests.
- [ ] **(208) Request counters tracked per request** – Stats reset every request, so `/diagnostics` “request_count” is always 0.
- [ ] **(209) Webhooks process notifications synchronously** – `processNotificationRules()` runs inline, increasing webhook latency and retries.
- [ ] **(210) Callback logs never pruned** – `panel_message.php` writes to DB/File logs without retention; tables grow indefinitely.
- [ ] **(211) Callback secret hard-coded** – Secret string embedded in code; rotation requires deploys.
- [ ] **(212) Debug table lacks index on `unique_source`** – Yet filters rely on it; add index for faster queries.
- [ ] **(213) Notification engine swallows exceptions** – `command-center-engine.php` catches errors but doesn’t log them, hiding failures.
- [ ] **(214) ActionCache grows without bounds** – No purge job exists for `mps-api/cache/storage`.
- [ ] **(215) Rate limiter throttles all routes together** – File-based limiter doesn’t distinguish endpoints; heavy `/diagnostics` use throttles `/query`.
- [ ] **(216) `/health` doesn’t expose structured metrics** – Ops must parse raw JSON for latency; add explicit fields.
- [ ] **(217) Swagger loader warns every request when file missing** – `file_exists` check logs warnings if only lowercase file exists; cache path results.
- [ ] **(218) `MAX_REQUEST_SIZE` hard-coded** – 1 MB limit isn’t configurable, blocking larger exports/tests.
- [ ] **(219) Vendor error handling lacks exponential backoff** – Non-rate-limit 500s aren’t retried with increasing delays.
- [ ] **(220) Config failures logged but execution continues** – `mps-api/engine.php` logs missing env vars but proceeds, causing hidden errors later.
- [ ] **(221) Ops playbook references outdated cron schedule** – Documentation misleads on-call engineers.
- [ ] **(222) Playbook omits `force=1` lock-clear instructions** – Operators can’t recover from stale locks without code knowledge.
- [ ] **(223) Cache population PowerShell ignores curl exit code** – Script always returns success, so Task Scheduler never alerts on failure.
- [ ] **(224) PowerShell scripts hardcode production URLs** – No parameterization for staging/dev; engineers must edit files manually.
- [ ] **(225) Panel-diagnostics script stores DB creds inline** – No `.env` usage, leading to credential drift.
- [ ] **(226) API test script references removed endpoints** – `tests/api-tests.sh` still pings `get-dashboard-stats.php`, so suite doesn’t cover current endpoints.
- [ ] **(227) API tests don’t authenticate** – Many tests hit protected endpoints without cookies/token, so responses are 302/401 yet script marks them PASS.
- [ ] **(228) CI workflow undocumented in context wiki** – Contributors remain unaware of automatic deploy pipeline.
- [ ] **(229) Constitution promises per-endpoint logging but reality differs** – Docs aren’t synchronized with code.
- [ ] **(230) No CI guard ensures living-audit updates** – Contributors can change cache/API code without touching this file.
- [ ] **(231) Living audit numbering manual** – There’s no script preventing duplicate IDs.
- [ ] **(232) Logs/ directory lacks retention guidance** – No README describes rotation strategy, leading to inconsistent practices.
- [ ] **(233) Cache purge function unused** – `cacheClear()` exists but is never scheduled.
- [ ] **(234) Cache stats recomputed synchronously** – `getCacheStats()` scans every JSON file; no background aggregation job.
- [ ] **(235) Panel message helper bypasses repositories** – Functions instantiate repos directly per call, ignoring dependency injection described elsewhere.
- [ ] **(236) Bootstrap sets timezone before config load** – `.env` timezone overrides ignored.
- [ ] **(237) Session config never verifies save path** – Failures manifest later as fatal errors.
- [ ] **(238) CMS config hardcodes credentials** – Unlike mps-api, CMS doesn’t read `.env`; secrets must be edited in code.
- [ ] **(239) Endpoint catalog not validated** – No CI/job ensures `.canonical/EndpointCatalog.php` matches vendor API.
- [ ] **(240) No synthetic monitoring for health endpoints** – Ops rely on humans to notice regressions; add scheduled probes.

---

*Keep this document synchronized with every cache/API change so Claude and other agents always have the latest investigative trail.*
/*
CHANGELOG
2025-11-10 Codex
- Logged the bootstrap fix that now loads DI before `cacheDeviceDrilldown()`, which was the root cause of the empty `mpsm_cache_device_drilldown` table.
- Added the payload-sanitizer worklog so multi-line, BOM, and Unicode line separator payloads get normalized before decoding and the sanitized snippets are captured for RCA.
*/
