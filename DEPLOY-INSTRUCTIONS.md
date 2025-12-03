# Deployment Instructions (Current)

Agents ship to the live site themselves. Default path is git push to `main` (triggers GitHub Actions FTP). If git is blocked, push via FTP/PowerShell. Do not wait for a separate user push.

## Default Path — Git Push to Main (triggers GitHub Actions)
1) Ensure working tree contains only intended changes; stage/commit as needed.  
2) `git push origin main` (or use repo-configured upstream).  
3) Monitor GitHub Actions: https://github.com/JezSlade/MPSM-Dashboard/actions until green.  
4) If the workflow fails, retry the push or fall back to FTP below.

## Fallback — Direct FTP Upload
- Use the repo PowerShell helpers (`deploy-all.ps1`, `deploy-critical-fix.ps1`) or manual FTP upload to the web root.  
- Upload only the changed files; leave `cms/config.php`, `.env*`, and server-managed cache/logs untouched.  
- After FTP, continue with the verification steps below.

## Retired/Do Not Use
- `deploy.php` HTTP trigger and `DEPLOY_SECRET` setup are deprecated; do not add secrets to `config.php` or rely on HTTP deploys.

## Post-Deploy Verification (Agent-run)
Run these immediately after each deploy (git or FTP):
- Cache warm: `curl "https://mpsm.resolutionsbydesign.us/cms/api/refresh-cache-enhanced.php"` (no auth required).  
- Cache status: `curl -s "https://mpsm.resolutionsbydesign.us/cms/api/cache-status-report.php" | head -30` — confirm device counts and timestamps advance.  
- Chunked refresher (only if a run is stuck): `curl "https://mpsm.resolutionsbydesign.us/cms/api/refresh-cache-chunked.php?action=status"` to check state; restart only if needed.  
- Spot-check live UI (hard refresh): `/cms/` dashboard cards, Command Center tabs, dealer dashboard pages.  
- Logs: review `cms/logs/cache-refresh-*.log` and browser console for errors.

## Expected Cache Results
- Device pages: ~52,800 devices (528 pages @ 100/page) when upstream returns full data.  
- Duration: 30–60 minutes for a full refresh, drill-down coverage ~5,000 devices when enabled.

/*
CHANGELOG
2025-12-03 Codex
- Replaced outdated deploy secret/HTTP flow with agent-led git/FTP deployment instructions.
- Added mandatory post-deploy verification using no-auth cache endpoints.
- Clarified expected cache results and deprecated HTTP deploy trigger.
*/
