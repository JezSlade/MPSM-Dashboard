# Deployment Instructions (Current)

Agents ship to the live site themselves only after explicit user approval. Current path is direct FTP using portable Python scripts. Do not use PowerShell-only deployment scripts.

GitHub Actions workflow files may exist, but direct FTP via the Python scripts remains the authoritative deployment path for operator-run deploys in this repository.

## Direct FTP Upload
- Set `MPSM_FTP_HOST`, `MPSM_FTP_USER`, `MPSM_FTP_PASSWORD`, and optionally `MPSM_FTP_ROOT` in `.runtime/ftp.env` or the process environment.
- Back up the live site first: `python3 scripts/ftp_backup.py`.
- Run local checks: `python3 scripts/run_checks.py`.
- Upload the repository and remove stale remote files while preserving server config/runtime paths: `python3 scripts/ftp_deploy.py --delete`.
- After FTP, continue with the verification steps below.

## Retired/Do Not Use
- `deploy.php` HTTP trigger and `DEPLOY_SECRET` setup are deprecated; do not add secrets to `config.php` or rely on HTTP deploys.

## Post-Deploy Verification (Agent-run)
Run these immediately after each FTP deploy:
- Portable smoke test: `python3 scripts/live_smoke.py`.
- Cache status: `curl -s "https://mpsm.resolutionsbydesign.us/cms/api/cache-status-report.php" | head -30` — confirm device counts and timestamps advance.  
- Chunked refresher: `curl "https://mpsm.resolutionsbydesign.us/cms/api/refresh-cache-chunked.php?action=status"` to check live/staging state. Use repeated `action=process` calls or cron to continue refresh; call `action=cutover` only when status is `ready_for_cutover`.  
- Spot-check live UI (hard refresh): `/cms/` dashboard cards, Command Center tabs, dealer dashboard pages.  
- Logs: review `cms/logs/cache-refresh-*.log` and browser console for errors.

## Expected Cache Results
- Current live cache report shows 3351 live devices and 1425 live drill-downs.
- Current staged refresh checkpoint on 2026-05-20: 3370 devices, 300 drill-downs, status `fetching_drilldowns`, 0 errors.
- Duration depends on vendor API latency; drill-down staging is intentionally chunked to avoid shared-host timeouts.

/*
CHANGELOG
2025-12-03 Codex
- Replaced outdated deploy secret/HTTP flow with agent-led git/FTP deployment instructions.
- Added mandatory post-deploy verification using no-auth cache endpoints.
- Clarified expected cache results and deprecated HTTP deploy trigger.
2026-05-20 Codex
- Replaced PowerShell FTP guidance with portable Python backup, deploy, and smoke-test scripts.
2026-05-20 Codex
- Removed GitHub Actions as the default path for this working tree, documented chunked cache refresh as current post-deploy path, and recorded current live/staging cache counts.
*/
