# Cache System Audit – 2025-11-15

Scope: CMS cache refresh scripts, cron orchestration, database usage, and status tooling.

## Components Reviewed
- Legacy monolithic refresher `cms/api/refresh-cache-enhanced.php`
- Chunked pipeline (`cms/api/refresh-cache-chunked.php`, `cms/api/refresh-cache-runner.php`)
- Cron router + logs (`cms/cron-router.php`, `cms/logs/cron-router-*.log`)
- Cache reader endpoints (`cms/api/get-cached-devices.php`, `cms/api/cache-status-report.php`)
- Repository layer (`src/Repositories/DeviceRepository.php`, helpers in `cms/functions.php`)

## Key Findings
1. **Chunked pipeline never advances past page 1**  
   `callMPSAPI()` returns the raw vendor payload, but the chunked processor insists on `response['data']` and `response['pagination']['total_pages']` before it inserts (`cms/api/refresh-cache-chunked.php:205-214`). Every call therefore throws “Invalid API response” and the state file stays parked at `fetching_devices, current_page: 1`, matching the live status API output (`refresh-cache-chunked.php?action=status`). Errors are appended to `$state['errors']`, but the script still returns `{success:true}`, so cron thinks the step finished even though nothing was cached.

2. **SQL uses non-existent columns**  
   Both staging inserts target `device_serial`/`install_status` columns that do not exist in either staging table (`cms/api/refresh-cache-chunked.php:221-243` and `:292-305`). The real schema uses `serial_number` and does not track `device_type`/`install_status`. Each `execute()` therefore produces “Unknown column” errors for every device/drill-down row, which are silently logged to the state error array but never bubble back to cron.

3. **CLI runs still emit CGI headers**  
   Even after switching cron to `/usr/local/bin/php … refresh-cache-chunked.php process`, the script treats GreenGeeks’ cron environment as HTTP (`PHP_SAPI` reports `cgi-fcgi`). It prints `X-Powered-By`/`Content-type` headers ahead of the JSON payload, and the router records `"message":"CLI execution failed"` (`cms/logs/cron-router-2025-11-14.log`, cron emails 00:02 EST). We patched the detection logic in `refresh-cache-chunked.php`, but the server copy needs to be redeployed so CLI runs emit pure JSON.

4. **Legacy refresher still truncates live tables up-front**  
   `fetchAndCacheAllDevicesIncremental()` unconditionally truncates `mpsm_cache_devices` (and drilldowns unless `skipDrilldown=1`) before the first API call (`cms/api/refresh-cache-enhanced.php:446-459`). Any network or vendor failure after that point leaves both tables empty, which is why previous runs zeroed out the dashboard. There is no staging/cutover path and no transaction to roll back the truncation.

5. **DeviceRepository expects `expires_at`, but refresher never sets it**  
   `DeviceRepository::cacheDevice()` writes an `expires_at` column for TTL-based invalidation (`src/Repositories/DeviceRepository.php:82-125`), yet `cacheDeviceList()` in the refresher only inserts `serial_number, device_data, customer_code, is_uninstalled, cached_at` (`cms/api/refresh-cache-enhanced.php:807-847`). That means repository reads see `NULL` expirations and cannot evict stale rows as designed.

6. **Cache population endpoints still load entire fleet into memory**  
   `cms/api/get-cached-devices.php` fetches every row (`SELECT … ORDER BY serial_number`) and builds one huge PHP array before responding (lines 38‑88). With 5‑10 k devices that adds seconds of CPU and hundreds of MBs per request. There is no pagination or streaming, and the code warns about “consider adding pagination” but does not implement it.

7. **Diagnostic tooling confirms repeated “START” cycles but no progress**  
   `cache-status-report.php` shows multiple `=== CHUNKED REFRESH START ===` entries on 2025‑11‑14 plus dozens of “Fetching device list page 1” lines but zero page increments (`cms/api/cache-status-report.php` output sampled 23:54 EST). This matches findings #1–#2: every chunk run restarts state, fails on page 1, and exits without touching staging tables.

8. **Duplication between legacy and chunked scripts causes drift**  
   There are two independent implementations of pagination, retry logic, and rate limiting (`fetchAllDevices()` vs. chunked loops). Fixes (e.g., pagination bug, dealer filters) applied to `refresh-cache-enhanced.php` are not reflected in `refresh-cache-chunked.php` or `cms/api/populate-chunked.php`, so new pipelines inherit outdated bugs (lower page rows, missing customer filters).

## Recommendations
- Make `refresh-cache-chunked.php` understand the actual `callMPSAPI()` response (`extractDevicesFromResponse()` already exists in `cms/functions.php`). Remove the bogus `response['data']`/`['pagination']` checks and use canonical helpers.
- Fix the staging inserts to use `serial_number` and align columns with `cache_devices`/`cache_device_drilldown`. Flush errors to the CLI so cron can detect failures.
- Roll out the CLI-detection patch (or force cron to use `/usr/bin/php-cli`) so chunked runs emit header-free JSON and `cron-router.php` doesn’t mislabel successful chunks as “CLI execution failed.”
- Stop truncating live tables at the beginning of `refresh-cache-enhanced.php`. Populate staging tables first and only swap once the full run completes, or wrap the truncation and insert loop in a transaction so it can roll back on failure.
- Extend `cacheDeviceList()` to maintain `expires_at` so Repository TTL logic works, or remove the unused column from the schema to avoid confusion.
- Paginate `get-cached-devices.php` (or gate it behind dealer/customer filters) to avoid returning tens of thousands of records per request.
- Consolidate the caching logic so there is a single source of truth for device pagination, retry/backoff, and DB writes. The current duplication makes it impossible to keep fixes in sync.

<!--
CHANGELOG
2025-11-15 Codex
- Added a fresh cache-system audit outlining current cron/chunked failures, schema mismatches, and high-priority remediation steps.
-->
