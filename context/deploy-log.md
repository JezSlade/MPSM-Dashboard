# Deploy Log

## 2025-11-18 20:30 UTC
- **Command:** `curl -T cms/CRON-SETUP.md ftp://ftp.resolutionsbydesign.us/cms/CRON-SETUP.md` (authenticated with existing FTP credentials)
- **Result:** Success (file overwritten on live FTP server, matching local changes)
- **Notes:** FTP upload performed per user request instead of GitHub push.

## 2025-11-18 21:05 UTC
- **Command:** `curl -T cms/api/refresh-cache-chunked.php ftp://ftp.resolutionsbydesign.us/cms/api/refresh-cache-chunked.php` (FTP credentials already stored)
- **Result:** Success (updated live chunked refresh script to the version that uses `serial_number`/`drilldown_data`)
- **Notes:** This eliminates the cron SQLSTATE 42S22 column-not-found failures.

## 2025-11-19 13:15 UTC
- **Command:** `curl -T cms/api/refresh-cache-chunked.php ftp://ftp.resolutionsbydesign.us/cms/api/refresh-cache-chunked.php`
- **Result:** Success (deployed the version that publishes `REFRESH_CACHE_CHUNKED_VERSION` on every response)
- **Notes:** Next cron email should include the `version` field, proving the updated script is running; keep an eye on errors for the OAuth timeout.

## 2025-11-19 14:00 UTC
- **Command:** `curl -T cms/api/refresh-cache-chunked.php ftp://ftp.resolutionsbydesign.us/cms/api/refresh-cache-chunked.php`
- **Result:** Success (pushed column-detection patch so the job works regardless of whether the table uses `serial_number` or `device_serial`)
- **Notes:** Live cron output now exposes `version` plus dynamic column handling; monitor the next emails for the absence of the `device_serial` error.

## 2025-11-19 14:10 UTC
- **Command:** `curl --ftp-create-dirs -T /tmp/refresh-cache-chunked.log ftp://ftp.resolutionsbydesign.us/logs/refresh-cache-chunked.log`
- **Result:** Success (created `/home/resolut7/logs/refresh-cache-chunked.log` and set permissions so cron output can stream there instead of emailing)
- **Notes:** The new log path will now capture every cron run (bypass email); update cron entry to `>> /home/resolut7/logs/refresh-cache-chunked.log 2>&1`.

## 2025-11-19 15:05 UTC
- **Command:** `curl -T cms/api/run-refresh-cache-chunked.php ftp://ftp.resolutionsbydesign.us/cms/api/run-refresh-cache-chunked.php`
- **Result:** Success (deployed HTTP helper that executes the CLI process when supplied with `secret=RUN_REFRESH_2025`)
- **Notes:** Use the new endpoint to trigger the chunked refresh from this workspace; responses include the shell command, exit code, and CLI output.

## 2025-11-21 16:40 UTC
- **Command:** `curl -T cms/api/refresh-cache-chunked.php ftp://ftp.resolutionsbydesign.us/cms/api/refresh-cache-chunked.php`
- **Result:** Success (queued all non-uninstalled devices for drill-down fetching so stage 2 actually runs).
- **Notes:** This change ensures the drill-down phase executes even when devices don’t classify themselves as `installed`; monitor `/home/resolut7/logs/refresh-cache-chunked.log` for the next drill-down chunk outputs.

## 2025-11-28 18:00 UTC
- **Command:** Uploaded via FTP  
  - `curl -T cms/api/get-device-deep-dive.php ftp://mpsm%40mpsm.resolutionsbydesign.us:Deploy123%21@ftp.resolutionsbydesign.us/cms/api/get-device-deep-dive.php`  
  - `curl -T cms/api/search-devices.php ftp://mpsm%40mpsm.resolutionsbydesign.us:Deploy123%21@ftp.resolutionsbydesign.us/cms/api/search-devices.php`  
  - `curl -T cms/assets/app.js ftp://mpsm%40mpsm.resolutionsbydesign.us:Deploy123%21@ftp.resolutionsbydesign.us/cms/assets/app.js`  
  - `curl -T context/session.md ftp://mpsm%40mpsm.resolutionsbydesign.us:Deploy123%21@ftp.resolutionsbydesign.us/context/session.md`
- **Result:** Success (deployed cached-first device search, expanded panel alert matching for drill-down, and modal alert display updates).
- **Notes:** Panel message matching now normalizes serial/device IDs from payloads and surfaces in-device modal; search uses cache-first with API fallback.

## 2025-11-28 19:05 UTC
- **Command:** Uploaded via FTP  
  - `curl -T cms/api/get-panel-messages.php ftp://mpsm%40mpsm.resolutionsbydesign.us:Deploy123%21@ftp.resolutionsbydesign.us/cms/api/get-panel-messages.php`  
  - `curl -T cms/api/get-device-deep-dive.php ftp://mpsm%40mpsm.resolutionsbydesign.us:Deploy123%21@ftp.resolutionsbydesign.us/cms/api/get-device-deep-dive.php`  
  - `curl -T cms/assets/panel-messages.js ftp://mpsm%40mpsm.resolutionsbydesign.us:Deploy123%21@ftp.resolutionsbydesign.us/cms/assets/panel-messages.js`  
  - `curl -T cms/assets/app.js ftp://mpsm%40mpsm.resolutionsbydesign.us:Deploy123%21@ftp.resolutionsbydesign.us/cms/assets/app.js`  
  - `curl -T context/session.md ftp://mpsm%40mpsm.resolutionsbydesign.us:Deploy123%21@ftp.resolutionsbydesign.us/context/session.md`
- **Result:** Success (deployed alert display-name mapping for panel/system alerts and ensured drill-down modal sections always render).
- **Notes:** Panel alerts now prefer payload descriptions and alert_definitions display names; code-only fallbacks are muted/secondary.

## 2025-11-28 19:40 UTC
- **Command:** Uploaded via FTP  
  - `curl -T cms/api/search-devices.php ftp://mpsm%40mpsm.resolutionsbydesign.us:Deploy123%21@ftp.resolutionsbydesign.us/cms/api/search-devices.php`  
  - `curl -T cms/api/command-center.php ftp://mpsm%40mpsm.resolutionsbydesign.us:Deploy123%21@ftp.resolutionsbydesign.us/cms/api/command-center.php`  
  - `curl -T cms/assets/hero-notifications.js ftp://mpsm%40mpsm.resolutionsbydesign.us:Deploy123%21@ftp.resolutionsbydesign.us/cms/assets/hero-notifications.js`
- **Result:** Success (hardened cache-first search fallback, enriched notifications with department/model/equipment_id, and updated hero cards to show human-readable alert names and device metadata).
- **Notes:** System Alerts cards now render display_name, model, department, and equipment barcode; search gracefully degrades if API is unreachable.

## 2025-11-28 19:55 UTC
- **Command:** `curl -T cms/assets/hero-notifications.js ftp://mpsm%40mpsm.resolutionsbydesign.us:Deploy123%21@ftp.resolutionsbydesign.us/cms/assets/hero-notifications.js`
- **Result:** Success (restored alerts rendering by falling back to the raw notification list when grouping yields zero, while keeping display name/device/department metadata).
- **Notes:** System Alerts should now appear even if alert_code/device_serial are missing in the feed.

## 2025-11-24 20:55 UTC
- **Command:**
  - `curl -T cms/assets/hero-notifications.js ftp://mpsm%40mpsm.resolutionsbydesign.us:Deploy123%21@ftp.resolutionsbydesign.us/cms/assets/hero-notifications.js`
  - `curl -T cms/api/populate-alert-definitions.php ftp://mpsm%40mpsm.resolutionsbydesign.us:Deploy123%21@ftp.resolutionsbydesign.us/cms/api/populate-alert-definitions.php`
- **Result:** Success (System Alerts cards no longer show serial numbers in subtitle; alert codes display as "Alert 808" format; added script to populate alert definitions)
- **Notes:**
  - hero-notifications.js now detects serial numbers (15+ chars) and excludes them from subtitle
  - Subtitle shows customer code or "Device Alert" fallback
  - populate-alert-definitions.php needs manual execution while logged in to insert human-readable descriptions
  - Commit: cc672ed
- **Remaining:** Run populate script, add customer filtering to Panel Message Monitor, handle 429 errors

/*
CHANGELOG
2025-11-18 Codex
- Logged FTP deployments of `cms/CRON-SETUP.md` and `cms/api/refresh-cache-chunked.php` to capture the cron instructions and cache schema fixes.
2025-11-19 Codex
- Added the deployment log entry for the versioned response patch to track this upload.
*/
2025-11-26 15:12:59 -05:00 Commit: Refine Command Center UI & API
- Files: cms/api/command-center.php, cms/assets/command-center.js, cms/assets/style.css, cms/command-center.php
- Notes: 1h tallies, alert-type aggregations, customer filter, modal fixes
2025-11-26 15:13:08 -05:00 Deploy: GitHub Actions auto-FTP (push to main)
- Command: git push origin main
- Workflow: .github/workflows/deploy.yml
- Monitor: https://github.com/JezSlade/MPSM-Dashboard/actions
2025-11-26 15:31:26 -05:00 Deploy: GitHub Actions auto-FTP (push to main)
- Command: git push origin main
- Workflow: .github/workflows/deploy.yml
- Monitor: https://github.com/JezSlade/MPSM-Dashboard/actions
2025-11-26 15:47:42 -05:00 Deploy: GitHub Actions auto-FTP (push to main)
- Command: git push origin main
- Workflow: .github/workflows/deploy.yml
- Monitor: https://github.com/JezSlade/MPSM-Dashboard/actions
2025-12-03 03:59:10 UTC
- Command: curl -T cms/api/get-dealer-summary.php ftp://mpsm%40mpsm.resolutionsbydesign.us:Deploy123%21@ftp.resolutionsbydesign.us/cms/api/get-dealer-summary.php
- Command: curl -T cms/api/get-dealer-summary-hybrid.php ftp://mpsm%40mpsm.resolutionsbydesign.us:Deploy123%21@ftp.resolutionsbydesign.us/cms/api/get-dealer-summary-hybrid.php
- Result: Success (redeclaration fix deployed)
- Notes: Removed local callMPSAPI duplicates; APIs now rely on shared callMPSQuery to avoid fatal errors when loading dealer dashboard.

2025-12-03 04:25:00 UTC
- Command: curl -T cms/dealer.php ftp://mpsm%40mpsm.resolutionsbydesign.us:Deploy123%21@ftp.resolutionsbydesign.us/cms/dealer.php
- Command: curl -T cms/api/get-dealer-summary.php ftp://mpsm%40mpsm.resolutionsbydesign.us:Deploy123%21@ftp.resolutionsbydesign.us/cms/api/get-dealer-summary.php
- Command: curl -T cms/api/get-dealer-summary-hybrid.php ftp://mpsm%40mpsm.resolutionsbydesign.us:Deploy123%21@ftp.resolutionsbydesign.us/cms/api/get-dealer-summary-hybrid.php
- Result: Success (dealer dashboard fixes deployed)
- Notes:
  * Added toast-container div to dealer.php for showToast() function
  * Fixed callMPSQuery parameters: added required DealerCode, PageNumber, PageRows, SortColumn
  * Fixed response parsing: API returns data array directly, not nested in Customers/MpsDashboardCustomer
  * Cache table exists but is empty (0 devices), so APIs fall back to live MPS API
  * Live API now returns actual customer/device data instead of zeros
