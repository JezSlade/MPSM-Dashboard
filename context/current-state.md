# Current State

Verified: 2026-05-20 16:16 America/New_York.

This file is the current source of truth for the repo cleanup, portable deployment tooling, and live deployment state. Older context files remain useful history, but this file supersedes old PowerShell, GitHub Actions, and enhanced-cache-only instructions unless current code proves otherwise.

## Repository Layout

- Root contains only project entrypoints and top-level metadata: `.env.example`, `.gitignore`, `.htaccess`, `README.md`, `Swagger.json`, `agents.md`, `bootstrap.php`, `deploy.php`, `favicon.ico`, `robots.txt`, `version.js`, and `worker.php`.
- Current source directories are `cms/`, `mps-api/`, `src/`, `config/`, `scripts/`, `tools/`, `tests/`, `database/`, `docs/`, `context/`, `dev-overlay-extension/`, `.canonical/`, and `output/`.
- Runtime/local-only directories are ignored: `.runtime/`, `backups/`, `reference/`, `.archive/`, `.well-known/`, cache, logs, locks, and local config/secrets.
- Tests live under `tests/`; docs live under `docs/` and `context/`; SQL lives under `database/`; diagnostics and maintenance helpers live under `tools/`.

## Deployment

- Current deploy path is direct FTP through portable Python scripts, not PowerShell.
- This working tree has no configured git remote and no `.github/workflows/deploy.yml`; do not describe GitHub Actions as active unless a future repo state adds them back.
- FTP credentials are not committed. The helpers read `MPSM_FTP_HOST`, `MPSM_FTP_ROOT`, `MPSM_FTP_USER`, and `MPSM_FTP_PASSWORD` from the process environment or ignored `.runtime/ftp.env`.
- Use:
  - `python3 scripts/run_checks.py`
  - `python3 scripts/ftp_backup.py`
  - `python3 scripts/ftp_deploy.py --delete`
  - `python3 scripts/live_smoke.py`
- FTP deploy preserves server-managed `.env`, `cms/config.php`, cache, logs, locks, and `mps-api/cache/storage/`.

## Last Live Backup And Deploy

- Backup path: `backups/live-site-20260520-160537`
- Backup result: 3114 files, 0 errors.
- FTP deploy result: 441 files uploaded, 0 errors.
- Removed live unsafe temporary endpoint: `/cms/api/tmp-secret-bc2f7.php` now returns 404.
- Live smoke passes:
  - `/cms/login.html` 200
  - `/cms/` redirects to login and returns 200
  - `/cms/api/cache-status-report.php` 200 text report
  - `/cms/api/v1/health` 200 JSON
  - `/robots.txt` 200
- `/mps-api/health` returns 200 and reports upstream API connection successful.

## Portable Runtime

- PHP is available through host execution from this environment with `flatpak-spawn --host php`.
- MySQL PHP extensions were downloaded into ignored `.runtime/php-ext/` and are auto-loaded by `scripts/run_checks.py` when present.
- PowerShell scripts were removed from active `scripts/` and `tests/`; use Python and shell equivalents.

## Cache Refresh

- The non-chunked `refresh-cache-enhanced.php` path is not suitable for shared-host full refreshes; it hit a live connection timeout during post-deploy warmup.
- The chunked path is current:
  - Status endpoint: `/cms/api/refresh-cache-chunked.php?action=status`
  - Process endpoint: `/cms/api/refresh-cache-chunked.php?action=process`
  - Cutover endpoint: `/cms/api/refresh-cache-chunked.php?action=cutover`
- `refresh-cache-chunked.php` uses staging tables and only swaps into live cache on guarded cutover.
- Current checkpoint: status `fetching_drilldowns`, page `34/34`, devices staged `3370`, drilldowns staged `300`, errors `0`, last activity `2026-05-20 16:16:04`.
- Live cache remains on the previous live tables until chunked refresh reaches `ready_for_cutover` and cutover succeeds.

## Code Fixes From Cleanup/Deploy

- `bootstrap.php` autoloads un-namespaced classes from current `src/` subdirectories.
- `src/Router.php` normalizes `/cms/api/v1/*` requests so the v1 health route works on the deployed path.
- `scripts/live_smoke.py` treats `cache-status-report.php` as a text endpoint.
- `scripts/ftp_common.py` excludes temporary secret endpoints and preserves server config/runtime paths.
- `scripts/run_checks.py` scans for known secrets and flags `tmp-secret-*` PHP files.
- `cms/api/refresh-cache-chunked.php` uses smaller drill-down chunks to fit shared-host HTTP timeouts.

## Known Follow-Ups

- Replace remaining hardcoded helper/bypass secrets in PHP endpoints with environment-backed configuration.
- Resolve or remove the `EngineInterface` registration for missing `src/Engine/MPSEngine.php`.
- Let the chunked refresh finish and perform guarded cutover, then record final live cache counts in `context/test-log.md`.
