# Operations Playbook

> Grounded in: `README.md`, `docs/INDEX.md`, `docs/operations/DEPLOY-INSTRUCTIONS.md`, `context/current-state.md`, and current code. Verified 2026-05-20.

## Credentials & Access

- **CMS Admin:** Default `admin / admin` (auto-created). Change via Admin tab ➝ User Management.
- **Database:** `resolut7_mpsm` using credentials in `cms/config.php`.
- **MPS Monitor OAuth:** Defined in root `.env` (mirrored in `cms/config.php` and `mps-api/config.php` loader). Dealer code `NY06AGDWUQ`, dealer ID `SZ13qRwU5GtFLj0i_CbEgQ2`.
- **FTP/Deployment:** Portable scripts use `ftp.resolutionsbydesign.us`; credentials belong in environment variables or ignored `.runtime/ftp.env`, never in committed docs or scripts.

## Mandatory Patch Loop
All agents follow this loop until the live site is healthy: **RCA ➝ plan patch ➝ refine patch plan ➝ optimize plan against regression ➝ patch ➝ deploy ➝ analyze live site ➝ repeat**. No patch stops before deploy + live verification.

## Daily Checks

1. **Login Smoke Test**
   ```bash
   curl -s -X POST "https://mpsm.resolutionsbydesign.us/cms/api/login.php" \
     -H "Content-Type: application/json" \
     -d '{"username":"admin","password":"admin"}' \
     -c cookies.txt
   ```
2. **Dashboard Load:** Visit `/cms/`, ensure cards populate and no `CardManager` errors in console (hard refresh if needed).
3. **Command Center:** Open `/cms/panel-message-monitor.php`; confirm table refreshes and latest message timestamps update.
4. **Payload Debugger:** Hit `/cms/payload-debugger.php`; ensure stats increment if test payloads are sent.
5. **System Health:** Check Admin ➝ System Monitoring or `curl https://mpsm.resolutionsbydesign.us/mps-api/health`.

## Background Cache Refresh

- **Current Path:**
  Use `cms/api/refresh-cache-chunked.php`. The full `refresh-cache-enhanced.php` path timed out during 2026-05-20 live post-deploy warmup and should not be used for full shared-host refreshes without retesting timeout limits.
  ```bash
  curl "https://mpsm.resolutionsbydesign.us/cms/api/refresh-cache-chunked.php?action=status"
  curl "https://mpsm.resolutionsbydesign.us/cms/api/refresh-cache-chunked.php?action=process"
  curl "https://mpsm.resolutionsbydesign.us/cms/api/refresh-cache-chunked.php?action=cutover"
  ```
  Only call `cutover` when status is `ready_for_cutover`.

- **Verify Results:**
  ```sql
  SELECT COUNT(*) FROM mpsm_cache_devices;
  SELECT COUNT(*) FROM mpsm_cache_device_drilldown;
  SELECT MAX(cached_at) FROM mpsm_cache_devices;
  ```

- **Log Monitoring:** After each cron execution, pull `/home/resolut7/logs/refresh-cache-chunked.log` (FTP/curl) and confirm the appended JSON includes `"version": "2025-11-19a"`, `state`, and `errors`. If the file stays empty, rerun the helper `cms/api/run-refresh-cache-chunked.php?secret=RUN_REFRESH_2025` to regenerate output.

- **Current Refresh Checkpoint:** As of 2026-05-20 16:16 America/New_York, the staged refresh is `fetching_drilldowns` with 3370 staged devices, 300 staged drill-down rows, and 0 errors. Live cache remains on previous tables until guarded cutover.

- **Troubleshooting:**
  - Confirm dealer code in `cms/config.php` matches `.env`.
  - Inspect `cms/logs/cache-refresh-YYYY-MM-DD.log`.
  - **CRITICAL**: If only getting ~200 devices, check pagination fix was applied (commit 878e7a4f)
  - Send direct API probe through `mps-api/query` to ensure upstream is returning data.

- **Automation:** Verify cPanel cron state before changing it. Current code supports repeated `process` calls through HTTP or CLI; the preferred CLI shape is:
  ```
  * * * * * /usr/local/bin/php /home/resolut7/public_html/mpsm.resolutionsbydesign.us/cms/api/refresh-cache-chunked.php process >> /home/resolut7/logs/refresh-cache-chunked.log 2>&1
  ```
  The log stores the versioned JSON for verification.

- **Drill-down monitoring:** After resetting the device phase (use `?action=start` or the helper), expect the next cron ticks to write new receipts showing `state.status` progressing into `fetching_drilldowns`. Tail `/home/resolut7/logs/refresh-cache-chunked.log` to see it, and if the log stays completed you can rerun `run-refresh-cache-chunked.php?secret=RUN_REFRESH_2025` to confirm the helper reads the current queue and dumps the same JSON. Logging ensures no more emails and documents every chunk.

- **Emergency trigger:** If you need to rerun the CLI process manually, call `https://mpsm.resolutionsbydesign.us/cms/api/run-refresh-cache-chunked.php?secret=RUN_REFRESH_2025`; it executes the same command as cron and returns the output/exit code so you can confirm the versioned script is live.

## Panel Message Diagnostics

- **Send Test Payload:** Historical PowerShell payload scripts were removed from active tests. Recreate this as a portable Python or shell helper if callback stress tests are needed.

- **Check Recent Messages:**
  ```sql
  SELECT id, received_at, device_serial, maintenance_alert_code
  FROM mpsm_panel_messages
  ORDER BY id DESC
  LIMIT 10;
  ```

- **Debug Table Cleanup:**
  ```sql
  DELETE FROM mpsm_panel_callback_debug
   WHERE timestamp < NOW() - INTERVAL 30 DAY;
  ```

## Deployment Workflow

### Primary Path — Direct FTP
1. Run `python3 scripts/run_checks.py`.
2. Run `python3 scripts/ftp_backup.py`.
3. Run `python3 scripts/ftp_deploy.py --delete`.
4. Run `python3 scripts/live_smoke.py`.
5. Record backup path, deploy result, and live smoke output.

This working tree has no git remote and no active `.github/workflows/deploy.yml`, so do not rely on GitHub Actions unless a future repo state restores it.

### Post-Deployment Actions (Mandatory, agent-run)
1. Confirm deploy completion (FTP transfer finished with 0 errors).  
2. Verify cache via no-auth endpoints:  
   - `curl -s "https://mpsm.resolutionsbydesign.us/cms/api/cache-status-report.php" | head -30`  
   - `curl "https://mpsm.resolutionsbydesign.us/cms/api/refresh-cache-chunked.php?action=status"`  
3. Live UI smoke: dashboard cards, Command Center tabs, dealer dashboard; hard refresh.  
4. Logs: `cms/logs/cache-refresh-*.log`, browser console, and any curl error output.  
5. Record results in the session/test logs when applicable.

**Manual Steps (Only if Programmatic Access Unavailable):**
- Database index application (requires phpMyAdmin/MySQL access)
- File renaming on server (if FTP automation unavailable)
- Cron job scheduling (if cPanel API unavailable)

### Post-Deployment Verification Checklist
1. Live site loads: https://mpsm.resolutionsbydesign.us/cms/ (no JS errors).  
2. Cache operational: `cache-status-report.php` shows advancing timestamps and non-zero device counts.  
3. Core features: login, dashboard cards, device modal, Command Center tabs, dealer dashboard load.  
4. No errors: clean logs and console.  
5. Performance: dashboard under 3s, modals under 500ms when cache is warm.

## Incident Response

1. **Identify**: Capture error message, HTTP status, request payload.
2. **Logs**: Check `cms/logs/php_errors.log`, `mps-api/logs/php_errors_YYYY-MM-DD.log`, panel message logs, and debug table.
3. **Rollback**: If deployment introduced regression, redeploy previous known-good bundle or revert via FTP script.
4. **Document**: Update `verified-fixes.md` (this folder) and relevant doc files with resolution.

## Environment-Specific Notes

- Shared hosting occasionally strips request bodies; `login.php` workarounds are already in place.
- Asset caching is aggressive; instruct users to do `Ctrl+Shift+R` or open in incognito after JS deployments.
- When debugging locally, use `.env.example` files as templates and populate with dealer credentials.
- For ChatGPT Actions integration, `/mps-api/swagger.json` and `/mps-api/endpoints` must remain accessible and accurate.

## Pending Action Items (from `IMMEDIATE_ACTION_ITEMS.md`)

1. **Enhanced Refresh Monitoring:** Allow the current full run to finish, then review `cms/logs/cache-refresh-*.log` to confirm drill-down caching completes without rate-limit exhaustion.
2. **Scheduling Strategy:** Decide on cadence for `skipDrilldown=1` quick warm-ups vs. full refreshes (with retries) and configure cron/Task Scheduler accordingly.
3. **Payload Debugger QA:** Periodically review the Unique Sources roll-up to ensure only trusted origins are reaching the callback endpoints.

  Keep this section in sync with the live action item document whenever new high-priority tasks arise.

/*
CHANGELOG
2025-12-03 Codex
- Added mandatory RCA→plan→refine→optimize→patch→deploy→analyze loop for all agents.
- Updated deployment workflow to require agent-driven git/FTP pushes and no-auth cache verification.
- Clarified post-deploy checks and preserved safety around config/.env during FTP.
*/
