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

- **Troubleshooting:**
  - Confirm dealer code in `cms/config.php` matches `.env`.
  - Inspect `cms/logs/cache-refresh-YYYY-MM-DD.log`.
  - Send direct API probe through `mps-api/query` to ensure upstream is returning data.

- **Automation:** Schedule every 5 minutes. On Linux cron:
  ```
  */5 * * * * curl -s "https://mpsm.resolutionsbydesign.us/cms/api/refresh-cache-enhanced.php" > /dev/null
  ```
  On Windows Task Scheduler, invoke PowerShell with `Invoke-WebRequest`.

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
6. After deploy, hard refresh browser (`Ctrl+Shift+R`) to clear cache

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

### Post-Deployment Checklist

1. **Verify live site loads:** https://mpsm.resolutionsbydesign.us/cms/
2. **Hard refresh browser:** `Ctrl+Shift+R`
3. **Check console:** No JavaScript errors (F12 → Console)
4. **Test critical features:**
   - Login works
   - Dashboard loads
   - Device search works
   - Device modal opens
5. **Monitor logs:**
   - PHP errors: `cms/logs/php_errors.log`
   - Cache refresh: `cms/logs/cache-refresh-*.log`
6. **Verify cache:** Run `refresh-cache-enhanced.php` if needed

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
