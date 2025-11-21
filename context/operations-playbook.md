# Operations Playbook

> Grounded in: `docs/ONBOARDING.md`, `IMMEDIATE_ACTION_ITEMS.md`, `BACKGROUND_REFRESH_SYSTEM.md`, `AUDIT_REPORT.md`, deployment scripts under `/`, and current code.

## Credentials & Access

- **CMS Admin:** Default `admin / admin` (auto-created). Change via Admin tab ➝ User Management.
- **Database:** `resolut7_mpsm` using credentials in `cms/config.php`.
- **MPS Monitor OAuth:** Defined in root `.env` (mirrored in `cms/config.php` and `mps-api/config.php` loader). Dealer code `NY06AGDWUQ`, dealer ID `SZ13qRwU5GtFLj0i_CbEgQ2`.
- **FTP/Deployment:** Scripts reference `ftp.resolutionsbydesign.us` with credentials stored inside `deploy-*.ps1`.

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

- **Manual Run:**
  /cms/api/refresh-cache-chunked.php now reports `REFRESH_CACHE_CHUNKED_VERSION`/`device_serial_column` when processing, so any cron output should include `"version": "2025-11-19a"` and `"device_serial_column": "serial_number"` as proof the latest script is running.
  **Cron log:** Direct every-minute output into `/home/resolut7/logs/refresh-cache-chunked.log` (`>> /home/resolut7/logs/refresh-cache-chunked.log 2>&1`) so agents can fetch the file instead of reading email reports.
  ```bash
  # Full run (includes drill-down caching, may take several minutes)
  curl "https://mpsm.resolutionsbydesign.us/cms/api/refresh-cache-enhanced.php"

  # Fast device-list warmup (skip drill-down)
  curl "https://mpsm.resolutionsbydesign.us/cms/api/refresh-cache-enhanced.php?skipDrilldown=1"

  # Force a re-run if a previous job left the lock behind
  curl "https://mpsm.resolutionsbydesign.us/cms/api/refresh-cache-enhanced.php?force=1"
  ```
  Expect JSON with `devices_cached`, `devices_with_drilldown`, `errors`, `duration`, `page_samples`, and `drilldown_skipped`. The refresher now retries `Device/Get` calls with exponential back-off when the vendor API reports rate limits.

- **Verify Results:**
  ```sql
  SELECT COUNT(*) FROM mpsm_cache_devices;
  SELECT COUNT(*) FROM mpsm_cache_device_drilldown;
  SELECT MAX(cached_at) FROM mpsm_cache_devices;
  ```

- **Log Monitoring:** After each cron execution, pull `/home/resolut7/logs/refresh-cache-chunked.log` (FTP/curl) and confirm the appended JSON includes `"version": "2025-11-19a"`, `state`, and `errors`. If the file stays empty, rerun the helper `cms/api/run-refresh-cache-chunked.php?secret=RUN_REFRESH_2025` to regenerate output.

- **Expected Device Count:**
  - As of 2025-11-08: **5000+ devices** across all customers/dealers
  - If count is significantly lower, see CRITICAL_FIX_DEVICE_PAGINATION.md
  - Coverage should be 95%+ (devices with drill-down / total devices)

- **Troubleshooting:**
  - Confirm dealer code in `cms/config.php` matches `.env`.
  - Inspect `cms/logs/cache-refresh-YYYY-MM-DD.log`.
  - **CRITICAL**: If only getting ~200 devices, check pagination fix was applied (commit 878e7a4f)
  - Send direct API probe through `mps-api/query` to ensure upstream is returning data.

- **Automation:** These cron jobs are live in cPanel and must remain in place unless explicitly changed:
  ```
  # UPDATED 2025-11-09: Changed from */5 to 0 * * * * (hourly) to prevent infinite loop
  # Previous issue: 5-minute cron was truncating cache before 30-minute refresh could complete
  0 * * * * /usr/bin/timeout 240 /usr/bin/curl -s "https://mpsm.resolutionsbydesign.us/cms/api/refresh-cache-enhanced.php?skipDrilldown=1" >/dev/null 2>&1
  0 0 * * * /usr/bin/timeout 1800 /usr/bin/curl -s "https://mpsm.resolutionsbydesign.us/cms/api/refresh-cache-enhanced.php?force=1" >/dev/null 2>&1
  0,30 * * * * /usr/bin/curl -s "https://mpsm.resolutionsbydesign.us/mps-api/health" >> /home/youruser/logs/mps-api-health.log
  0 0 * * * /usr/bin/curl -s "https://mpsm.resolutionsbydesign.us/cms/api/get-database-monitor.php" >> /home/youruser/logs/database-monitor.log
  0 0 * * 0 /usr/bin/php /home/youruser/public_html/cms/api/cleanup-payload-debug.php >/dev/null 2>&1
  ```
  The CMS relies on these schedules for cache freshness, health logging, and payload debugger retention; adjust only with owner approval.

  **Note:** First cron changed from every 5 minutes to hourly (2025-11-09) to allow full 30,000+ device cache population to complete without interference. Once cache stability is verified, may consider restoring more frequent updates.

- **Chunked refresher detail:** Update the cPanel cron entry to redirect output:
  ```
  * * * * * /usr/local/bin/php /home/resolut7/public_html/mpsm.resolutionsbydesign.us/cms/api/refresh-cache-chunked.php process >> /home/resolut7/logs/refresh-cache-chunked.log 2>&1
  ```
  The log stores the versioned JSON for verification.

- **Emergency trigger:** If you need to rerun the CLI process manually, call `https://mpsm.resolutionsbydesign.us/cms/api/run-refresh-cache-chunked.php?secret=RUN_REFRESH_2025`; it executes the same command as cron and returns the output/exit code so you can confirm the versioned script is live.

## Panel Message Diagnostics

- **Send Test Payload (PowerShell):**
  ```powershell
  .\test-payloads.ps1
  ```
  Populates both success and error cases for debugger validation (`PAYLOAD_DEBUGGER_GUIDE.md`).

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

### Automatic Deployment (Preferred)

**GitHub Actions automatically deploys on push to main:**

1. Make changes locally
2. Test locally (run relevant smoke checks)
3. Commit with descriptive message:
   ```bash
   git add .
   git commit -m "Your descriptive message"
   ```
4. Push to main:
   ```bash
   git push origin main
   ```
5. **Automatic deployment begins** (GitHub Actions workflow: `.github/workflows/deploy.yml`)
   - Takes 2-5 minutes
   - Deploys via FTP to: `ftp.resolutionsbydesign.us`
   - Excludes: logs, tests, .git, documentation, scripts
   - Includes: .env files (OAuth credentials)

**Monitor deployment:**
- Go to: https://github.com/JezSlade/MPSM-Dashboard/actions
- View latest workflow run
- Check for green checkmark (success) or red X (failure)

### Manual Deployment (Alternative)

For urgent hotfixes or when GitHub Actions unavailable:

```powershell
# Use one of the deployment scripts:
.\deploy-critical-fix.ps1
.\deploy-performance-refactor.ps1
# etc.
```

Scripts upload selected files via FTP and can invalidate caches if necessary.

### Post-Deployment Actions (REQUIRED - AUTOMATED)

**CRITICAL: When AI agent pushes code, ALL post-deployment actions MUST be completed programmatically:**

1. **Monitor GitHub Actions deployment** (wait for completion, check status)
   ```bash
   # Poll GitHub Actions API or check workflow status
   # Wait for green checkmark before proceeding
   ```

2. **Trigger cache population** (programmatic)
   ```bash
   curl -X GET "https://mpsm.resolutionsbydesign.us/cms/api/refresh-cache-enhanced.php"
   # Wait for response, verify success
   ```

3. **Verify live site functionality** (automated testing)
   ```bash
   # Run test_live_site.ps1 or equivalent curl-based tests
   # Test: homepage, cache endpoint, panel monitor, payload debugger, API engine
   ```

4. **Check error logs** (programmatic)
   ```bash
   # Query live error log endpoints if available
   # Or use cPanel API to check logs
   ```

5. **Validate performance** (automated benchmarking)
   ```bash
   # Test response times for key endpoints
   # Verify < 3s dashboard load, < 500ms modals, < 100ms cache hits
   ```

**AI Agent Behavior Requirements:**
- ✅ MUST complete all post-deployment actions programmatically
- ✅ MUST verify deployment success before completing task
- ✅ MUST run automated tests to confirm functionality
- ✅ MUST NOT just provide instructions - execute all steps
- ✅ MUST report actual test results and status
- ❌ DO NOT delegate manual steps to user unless technically impossible

**Manual Steps (Only if Programmatic Access Unavailable):**
- Database index application (requires phpMyAdmin/MySQL access)
- File renaming on server (if FTP automation unavailable)
- Cron job scheduling (if cPanel API unavailable)

### Post-Deployment Verification Checklist

After automated actions complete, verify:
1. **Live site loads:** https://mpsm.resolutionsbydesign.us/cms/
2. **Cache operational:** get-cached-devices.php returns data
3. **Core features working:** Login, dashboard, search, device modal
4. **No errors:** Clean logs, no console errors
5. **Performance targets met:** Dashboard < 3s, modals < 500ms

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
